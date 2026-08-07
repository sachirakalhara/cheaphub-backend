<?php

namespace App\Repositories\Payment;

use App\Models\Cart\Cart;
use App\Models\Cart\CartItem;
use App\Models\Coupon\Coupon;
use App\Models\Payment\OrderItems;
use App\Repositories\Payment\Interface\HeleketPaymentRepositoryInterface;
use Illuminate\Http\Response;
use App\Models\Payment\Order;
use App\Models\Payment\Wallet;
use App\Models\Product\Bulk\BulkProduct;
use App\Models\Product\Bulk\RemovedBulkProductSerial;
use App\Models\Subscription\Package;
use App\Models\Subscription\Subscription;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\Product\Contribution\RemovedContributionProductSerial;
use App\Models\User\User;
use App\Notifications\OrderCreated;
use App\Services\StockNotificationService;

class HeleketPaymentRepository implements HeleketPaymentRepositoryInterface
{
    /**
     * Statuses Heleket reports for a fully paid invoice.
     */
    private const SUCCESS_STATUSES = ['paid', 'paid_over'];

    /**
     * Statuses Heleket reports for a terminally failed invoice.
     */
    private const FAILURE_STATUSES = ['fail', 'wrong_amount', 'cancel', 'system_fail'];

    /**
     * Create a Heleket crypto invoice for the authenticated user's cart and
     * return the hosted payment page URL.
     */
    public function createInvoice($data)
    {
        $user = Auth::user();
        $amount = $data['amount'] ?? 0;
        $discount = 0;
        $isWallet = $data['is_wallet'] ?? false;

        if ($amount <= 0) {
            return response()->json(['message' => 'Invalid amount'], Response::HTTP_BAD_REQUEST);
        }

        $cart = Cart::with('cartItems')->where('user_id', $user->id)->first();

        // A product purchase MUST have a non-empty cart. Without this guard a
        // null cart (e.g. a duplicate/retried submit after the first call has
        // already cleared the cart) silently creates a PAID order with zero
        // items and no delivery. Reject before any order is created.
        if (!$isWallet) {
            if (!$cart || !$cart->cartItems || $cart->cartItems->isEmpty()) {
                return response()->json(['message' => 'Cart is empty'], Response::HTTP_BAD_REQUEST);
            }
        }

        if ($cart && !$isWallet) {
            if (!$cart->cartItems || $cart->cartItems->isEmpty()) {
                return response()->json(['message' => 'Cart is empty'], Response::HTTP_BAD_REQUEST);
            }

            $cartItemPackages = CartItem::where('cart_id', $cart->id)
                ->whereNotNull('package_id')
                ->get();

            $cartItemBulkProducts = CartItem::where('cart_id', $cart->id)
                ->whereNotNull('bulk_product_id')
                ->get();

            foreach ($cartItemPackages as $cartItemPackage) {
                $package = Package::find($cartItemPackage->package_id);

                if (!$package) {
                    return response()->json(['message' => 'Package not found'], Response::HTTP_NOT_FOUND);
                }

                if (optional($package->subscription->contributionProduct)->visibility === 'onHold') {
                    return response()->json(['message' => 'This product is currently unavailable'], Response::HTTP_BAD_REQUEST);
                }

                if ($package->subscription->is_manually_out_of_stock) {
                    return response()->json(['message' => 'This product is currently out of stock'], Response::HTTP_BAD_REQUEST);
                }

                if (($package->subscription->delivery_type ?? 'serial_based') === 'service_based') {
                    if ($package->subscription->service_qty < $cartItemPackage->quantity) {
                        return response()->json(['message' => 'Not enough stock for the package'], Response::HTTP_BAD_REQUEST);
                    }
                } else {
                    if ($package->subscription->available_serial_count < $cartItemPackage->quantity) {
                        return response()->json(['message' => 'Not enough stock for the package'], Response::HTTP_BAD_REQUEST);
                    }
                }
            }

            foreach ($cartItemBulkProducts as $cartItemBulkProduct) {
                $bulkProduct = BulkProduct::find($cartItemBulkProduct->bulk_product_id);

                if (!$bulkProduct) {
                    return response()->json(['message' => 'Bulk product not found'], Response::HTTP_NOT_FOUND);
                }

                if ($bulkProduct->visibility === 'onHold') {
                    return response()->json(['message' => 'This product is currently unavailable'], Response::HTTP_BAD_REQUEST);
                }

                if ($bulkProduct->is_manually_out_of_stock) {
                    return response()->json(['message' => 'This product is currently out of stock'], Response::HTTP_BAD_REQUEST);
                }

                if ($bulkProduct->bulk_type == 'serial_based') {
                    if ($bulkProduct->serial_count < $cartItemBulkProduct->quantity) {
                        return response()->json(['message' => 'Not enough stock for the bulk product'], Response::HTTP_BAD_REQUEST);
                    }

                    if ($bulkProduct->minimum_quantity > $cartItemBulkProduct->quantity) {
                        return response()->json(['message' => 'Minimum quantity not met for the bulk product'], Response::HTTP_BAD_REQUEST);
                    }

                    if ($bulkProduct->maximum_quantity < $cartItemBulkProduct->quantity) {
                        return response()->json(['message' => 'Maximum quantity exceeded for the bulk product'], Response::HTTP_BAD_REQUEST);
                    }
                }
            }

            if ($cart->coupon_code) {
                $coupon = Coupon::where('coupon_code', $cart->coupon_code)->first();

                if (!$coupon) {
                    return response()->json(['message' => 'Invalid coupon code'], Response::HTTP_BAD_REQUEST);
                }

                if ($coupon->isExpired()) {
                    return response()->json(['message' => 'Coupon has expired'], Response::HTTP_BAD_REQUEST);
                }

                // The cart validated this when the code was applied, but a
                // scheduled campaign can end (cron flips is_active) while the
                // code still sits in a long-lived cart. Re-check at payment.
                if (!$coupon->is_active) {
                    return response()->json(['message' => 'This coupon is not currently active'], Response::HTTP_BAD_REQUEST);
                }

                $packagesTotalPrice = $cart->cartItems->whereNotNull('package_id')->sum(function ($item) {
                    $package = Package::find($item->package_id);
                    return $package ? $package->price * $item->quantity : 0;
                });

                $bulkProductsTotalPrice = $cart->cartItems->whereNotNull('bulk_product_id')->sum(function ($item) {
                    $bulkProduct = BulkProduct::find($item->bulk_product_id);
                    return $bulkProduct ? $bulkProduct->price * $item->quantity : 0;
                });

                $totalPrice = $packagesTotalPrice + $bulkProductsTotalPrice;

                if (in_array($coupon->product_type, ['subscription', 'both'])) {
                    $discount += $packagesTotalPrice * $coupon->discount_percentage / 100;
                }

                if (in_array($coupon->product_type, ['bulk', 'both'])) {
                    $discount += $bulkProductsTotalPrice * $coupon->discount_percentage / 100;
                }

                $discount = min($discount, $coupon->max_discount_amount);

                if ($discount > $totalPrice) {
                    return response()->json(['message' => 'Discount exceeds total price'], Response::HTTP_BAD_REQUEST);
                }

                $amount = $totalPrice - $discount;
            }
        }

        DB::beginTransaction();
        try {
            $cart = Cart::with('cartItems')
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->first();

            if (!$isWallet && (!$cart || !$cart->cartItems || $cart->cartItems->isEmpty())) {
                DB::rollBack();
                return response()->json(['message' => 'Cart is empty'], Response::HTTP_BAD_REQUEST);
            }

            $order = Order::create([
                'amount' => $amount,
                'discount' => $discount,
                'currency' => config('heleket.currency', 'USD'),
                'description' => $data['description'] ?? '',
                'payment_status' => 'pending',
                'is_wallet' => $isWallet,
                'user_id' => $user->id,
                'order_id' => 'order_' . now()->format('YmdHis') . '_' . Str::random(6),
                'payment_method' => 'crypto',
            ]);

            if (!$isWallet) {
                if ($cart) {
                    foreach ($cart->cartItems as $cartItem) {
                        OrderItems::create([
                            'order_id' => $order->id,
                            'bulk_product_id' => $cartItem->bulk_product_id,
                            'package_id' => $cartItem->package_id,
                            'quantity' => $cartItem->quantity,
                        ]);
                    }
                    $cart->cartItems()->delete();
                    $cart->delete();
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Heleket order creation error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create order.',
                'error' => $e->getMessage(),
            ], 500);
        }

        $invoiceData = array_filter([
            'amount' => (string) $amount,
            'currency' => config('heleket.currency', 'USD'),
            'order_id' => $order->order_id,
            'url_return' => config('heleket.url_return') ?: null,
            'url_success' => config('heleket.url_success') ?: null,
            'url_callback' => config('heleket.url_callback') ?: null,
            'lifetime' => (int) config('heleket.lifetime', 3600),
            'accuracy_payment_percent' => min(5, max(0, (float) config('heleket.accuracy_payment_percent', 2))),
            'is_payment_multiple' => false,
        ], function ($value) {
            return $value !== null;
        });

        try {
            $sign = $this->generateSign($invoiceData);

            $response = Http::withHeaders([
                'merchant' => config('heleket.merchant_uuid'),
                'sign' => $sign,
                'Content-Type' => 'application/json',
            ])->post(rtrim(config('heleket.base_url'), '/') . '/payment', $invoiceData);

            $result = $response->json();

            if ($response->successful() && isset($result['result']['url'])) {
                $order->update([
                    'payment_status' => 'pending',
                    'transaction_id' => $result['result']['uuid'] ?? null,
                ]);

                return response()->json([
                    'status' => 'success',
                    'redirect_url' => $result['result']['url'],
                    'transaction_id' => $result['result']['uuid'] ?? null,
                    'order_id' => $order->order_id,
                ]);
            }

            $order->update(['payment_status' => 'failed']);
            Log::error('Heleket invoice creation failed', ['response' => $result]);

            return response()->json([
                'status' => 'error',
                'message' => 'Payment initiation failed.',
                'details' => $result,
            ], 400);
        } catch (\Exception $e) {
            $order->update(['payment_status' => 'failed']);
            Log::error('Heleket invoice creation error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while processing the payment.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Handle the server-to-server webhook Heleket sends on every status change.
     * Security is enforced entirely through signature verification because the
     * endpoint is public.
     */
    public function handleWebhook($request)
    {
        $payload = $request->all();

        // Verify the signature before trusting any field in the payload.
        if (!$this->verifySign($payload)) {
            Log::warning('Heleket webhook signature verification failed', ['payload' => $payload]);
            return response()->json(['status' => 'error', 'message' => 'Invalid signature'], Response::HTTP_FORBIDDEN);
        }

        $orderId = $payload['order_id'] ?? null;
        $status = $payload['status'] ?? null;

        if (!$orderId || !$status) {
            return response()->json(['status' => 'error', 'message' => 'Missing required fields'], Response::HTTP_BAD_REQUEST);
        }

        $order = Order::where('order_id', $orderId)->first();

        if (!$order) {
            Log::warning('Heleket webhook for unknown order', ['order_id' => $orderId]);
            return response()->json(['status' => 'error', 'message' => 'Order not found'], Response::HTTP_NOT_FOUND);
        }

        // Idempotency: ignore webhooks for an order we already fulfilled.
        if ($order->payment_status === 'paid') {
            return response()->json(['status' => 'success', 'message' => 'Already processed']);
        }

        if (in_array($status, self::FAILURE_STATUSES, true)) {
            $order->update(['payment_status' => 'failed']);
            Log::info('Heleket payment failed', ['order_id' => $orderId, 'status' => $status]);
            return response()->json(['status' => 'success', 'message' => 'Payment marked as failed']);
        }

        if (!in_array($status, self::SUCCESS_STATUSES, true)) {
            // Intermediate status (process, check, confirm_check, locked) — ack and wait.
            return response()->json(['status' => 'success', 'message' => 'Status acknowledged']);
        }

        $amountPaid = $payload['payment_amount'] ?? $payload['amount'] ?? $order->amount;

        if ($order->is_wallet) {
            $wallet = Wallet::where('user_id', $order->user_id)->first();

            if (!$wallet) {
                return response()->json(['status' => 'error', 'message' => 'Wallet not found'], Response::HTTP_NOT_FOUND);
            }

            $wallet->increment('balance', $order->amount);

            $order->update(['payment_status' => 'paid', 'amount_paid' => $amountPaid]);

            $user = User::find($order->user_id);
            if ($user) {
                $user->notify(new OrderCreated($order));
            }

            return response()->json(['status' => 'success', 'order_id' => $order->id]);
        }

        DB::beginTransaction();
        try {
            $orderItems = OrderItems::where('order_id', $order->id)->get();

            foreach ($orderItems as $orderItem) {
                if ($orderItem->bulk_product_id) {
                    $bulkProduct = BulkProduct::find($orderItem->bulk_product_id);

                    if ($bulkProduct && $bulkProduct->bulk_type == 'serial_based') {
                        $allSerials = array_values(array_filter(explode("\n", $bulkProduct->serial), 'trim'));

                        if (count($allSerials) < $orderItem->quantity) {
                            throw new \Exception('Not enough stock for the bulk product');
                        }

                        $oldCount = $bulkProduct->serial_count;
                        $removedSerials = array_splice($allSerials, 0, $orderItem->quantity);

                        $bulkProduct->serial = implode("\n", $allSerials);
                        $bulkProduct->serial_count = count($allSerials);
                        $bulkProduct->save();

                        StockNotificationService::checkAndNotify(
                            $bulkProduct->name, 'Bulk Product', $bulkProduct->serial_count, $oldCount, $bulkProduct->id
                        );

                        foreach ($removedSerials as $serial) {
                            RemovedBulkProductSerial::create([
                                'bulk_product_id' => $orderItem->bulk_product_id,
                                'order_item_id' => $orderItem->id,
                                'serial' => $serial,
                            ]);
                        }
                    }
                }
            }

            foreach ($orderItems as $orderItem) {
                if ($orderItem->package_id) {
                    $package = Package::find($orderItem->package_id);

                    if ($package) {
                        $subscription = Subscription::find($package->subscription_id);

                        if ($subscription && ($subscription->delivery_type ?? 'serial_based') === 'service_based') {
                            // Service-based: deduct manual quantity only, no serials.
                            if ($orderItem->quantity > $subscription->service_qty) {
                                throw new \Exception('Not enough stock for the subscription');
                            }

                            $oldServiceQty = $subscription->service_qty;
                            $subscription->decrement('service_qty', $orderItem->quantity);

                            StockNotificationService::checkAndNotify(
                                $subscription->name, 'Subscription', $subscription->service_qty, $oldServiceQty, $subscription->id
                            );
                        } elseif ($subscription) {
                            if ($orderItem->quantity > $subscription->available_serial_count) {
                                throw new \Exception('Not enough stock for the subscription');
                            }

                            $oldSubCount = $subscription->available_serial_count;
                            $allSerials = array_values(array_filter(explode("\n", $subscription->serial), 'trim'));

                            if (!empty($allSerials)) {
                                $removedSerials = array_splice($allSerials, 0, $orderItem->quantity);

                                $subscription->serial = implode("\n", $allSerials);
                                $subscription->available_serial_count = max(0, $subscription->available_serial_count - $orderItem->quantity);
                                $subscription->save();

                                StockNotificationService::checkAndNotify(
                                    $subscription->name, 'Subscription', $subscription->available_serial_count, $oldSubCount, $subscription->id
                                );

                                foreach ($removedSerials as $serial) {
                                    RemovedContributionProductSerial::create([
                                        'package_id' => $package->id,
                                        'order_item_id' => $orderItem->id,
                                        'serial' => $serial,
                                    ]);
                                }
                            }
                        }
                    }
                }
            }

            $order->update([
                'payment_status' => 'paid',
                'amount_paid' => $amountPaid,
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $order->update(['payment_status' => 'failed']);
            Log::error('Heleket webhook processing error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }

        // Product purchases earn wallet cashback. Wallet top-up orders never
        // reach this point (their branch returns earlier), so no guard needed.
        \App\Services\WalletCashbackService::creditForOrder($order);

        $user = User::find($order->user_id);
        if ($user) {
            $user->notify(new OrderCreated($order));
        }

        return response()->json([
            'status' => 'success',
            'order_id' => $order->id,
            'amount' => $order->amount,
            'amount_paid' => $amountPaid,
        ]);
    }

    /**
     * Generate the Heleket request signature:
     * md5( base64_encode( json_encode($data) ) . $api_key )
     */
    private function generateSign(array $data): string
    {
        $encoded = base64_encode(json_encode($data, JSON_UNESCAPED_UNICODE));
        return md5($encoded . config('heleket.api_key'));
    }

    /**
     * Verify an incoming webhook signature. The `sign` field is removed from
     * the payload, the remainder is re-signed, and the two are compared in a
     * timing-safe manner.
     */
    private function verifySign(array $payload): bool
    {
        $sign = $payload['sign'] ?? null;

        if (!$sign) {
            return false;
        }

        unset($payload['sign']);

        $expected = $this->generateSign($payload);

        return hash_equals($expected, (string) $sign);
    }
}

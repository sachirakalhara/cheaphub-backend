<?php

namespace App\Repositories\Payment;

use App\Helpers\Helper;
use App\Models\Cart\Cart;
use App\Models\Cart\CartItem;
use App\Models\Coupon\Coupon;
use App\Models\Payment\OrderItems;
use App\Repositories\Payment\Interface\MarxPaymentRepositoryInterface;
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
use Illuminate\Support\Facades\Mail;
use App\Models\Product\Contribution\RemovedContributionProductSerial;
use App\Models\User\User;
use App\Notifications\OrderCreated;
use App\Services\StockNotificationService;

class MarxPaymentRepository implements MarxPaymentRepositoryInterface
{
    
    public function makePaymentV4($data)
    {
        $user = Auth::user();
        $amount = $data['amount'] ?? 0;
        $discount = 0;
        $gateway_fee = 0;

        if ($amount <= 0) {
            return response()->json(['message' => 'Invalid amount'], Response::HTTP_BAD_REQUEST);
        }

        $cart = Cart::with('cartItems')->where('user_id', $user->id)->first();

        // A product purchase MUST have a non-empty cart. Without this guard a
        // null cart (e.g. a duplicate/retried submit after the first call has
        // already cleared the cart) silently creates a PAID order with zero
        // items and no delivery. Reject before any order is created.
        if (!($data['is_wallet'] ?? false)) {
            if (!$cart || !$cart->cartItems || $cart->cartItems->isEmpty()) {
                return response()->json(['message' => 'Cart is empty'], Response::HTTP_BAD_REQUEST);
            }
        }

        if ($cart && !$data['is_wallet']) {
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
                $gateway_fee = $package->subscription->gateway_fee ?? 0;

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
                $gateway_fee = $bulkProduct->gateway_fee ?? 0;
                if (!$bulkProduct) {
                    return response()->json(['message' => 'Bulk product not found'], Response::HTTP_NOT_FOUND);
                }

                if ($bulkProduct->visibility === 'onHold') {
                    return response()->json(['message' => 'This product is currently unavailable'], Response::HTTP_BAD_REQUEST);
                }

                if ($bulkProduct->is_manually_out_of_stock) {
                    return response()->json(['message' => 'This product is currently out of stock'], Response::HTTP_BAD_REQUEST);
                }

                if($bulkProduct->bulk_type == 'serial_based') {

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

        // Snapshot of the cart, captured before the order consumes it. If Marx
        // initiation fails below, the cart has already been deleted — without
        // restoring it the customer is stranded on "Cart is empty" on every retry.
        $cartSnapshot = null;

        DB::beginTransaction();
        try {
            // Lock the cart row so a concurrent request from the same user blocks
            // here until this transaction finishes. After commit the second request
            // will find no cart and get "Cart is empty".
            $cart = Cart::with('cartItems')
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->first();

            if (!($data['is_wallet'] ?? false) && (!$cart || !$cart->cartItems || $cart->cartItems->isEmpty())) {
                DB::rollBack();
                return response()->json(['message' => 'Cart is empty'], Response::HTTP_BAD_REQUEST);
            }

            $order = Order::create([
                'amount' => $amount,
                'discount' => $discount,
                'currency' => $data['currency'] ?? 'LKR',
                'description' => $data['description'] ?? '',
                'payment_status' => 'pending',
                'is_wallet' => $data['is_wallet'] ?? false,
                'user_id' => $user->id,
                'order_id' => 'order_' . now()->format('YmdHis'),
                'payment_method' => 'credit_card',
            ]);

            if (!$data['is_wallet'] || $data['is_wallet'] === 0) {
                if ($cart) {
                    $cartSnapshot = [
                        'coupon_code' => $cart->coupon_code,
                        'items' => $cart->cartItems->map(function ($ci) {
                            return [
                                'bulk_product_id' => $ci->bulk_product_id,
                                'package_id' => $ci->package_id,
                                'quantity' => $ci->quantity,
                            ];
                        })->toArray(),
                    ];

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
            Log::error('Order creation error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create order.',
                'error' => $e->getMessage(),
            ], 500);
        }

        $gateway_fee = $amount * $gateway_fee / 100;
        // Marx accepts at most 2 decimal places. The gateway-fee multiplication
        // can produce 3+ decimals (e.g. 20.97 * 1.045 = 21.91365), which Marx
        // rejects at initiation — the intermittent "payment initiation failed"
        // with nothing landing on the Marx dashboard. Round to 2dp.
        $amount = round($amount + $gateway_fee, 2);
        // Marx requires a Sri Lankan mobile in the form +94XXXXXXXXX. International
        // customers' numbers (e.g. a UK "07821173288") fail Marx's regex and the
        // whole payment is rejected. Use the customer's number when it already
        // matches, normalise a local SL "0XXXXXXXXX" to +94, otherwise fall back
        // to a compliant placeholder (this field is only Marx's record — card OTP
        // goes to the card issuer's number, not here).
        $customerMobile = trim($data['tel'] ?? '');
        if (preg_match('/^\+94\d{9}$/', $customerMobile)) {
            // already valid — keep as-is
        } elseif (preg_match('/^0\d{9}$/', $customerMobile)) {
            $customerMobile = '+94' . substr($customerMobile, 1);
        } else {
            $customerMobile = '+94763737145';
        }

        $marxArgs = [
            'merchantRID' => $order->order_id,
            'amount' => floatval($amount),
            // Environment-aware so dev payments return to dev and prod to prod.
            // Was hardcoded to prod, which made every dev card payment fail
            // reconciliation (Marx redirected to prod, whose DB lacked the order).
            'returnUrl' => rtrim(config('app.client_url'), '/') . '/marxpay',
            'validTimeLimit' => 30,
            'customerMail' => $data['email'] ?? '',
            'customerMobile' => $customerMobile,
            'mode' => "WEB",
            'currency' => $data['currency'],
            'orderSummary' => $data['description'] ?? '',
            'customerReference' => $user->id . " " . ($data['email'] ?? ''),
            "paymentMethod"=> $data['paymentMethod']
        ];

        try {
            $local_user_secret = config('marx.merchant_key');
            $marx_sandbox_url = config('marx.api_url');
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'merchant-api-key' => $local_user_secret,
            ])->post($marx_sandbox_url, $marxArgs);

            $result = $response->json();

            if ($response->successful() && isset($result['data']['payUrl']) && $result['status'] === 0 && $result['message'] === 'SUCCESS') {

                $order->update([
                    'payment_status' => 'pending',
                    'transaction_id' => $result['data']['trId']
                ]);

                return response()->json([
                    'status' => 'success',
                    'redirect_url' => $result['data']['payUrl'],
                    'transaction_id' => $result['data']['trId'],
                    'merchantRID' => $result['data']['merchantRID']
                ]);
            }

            $order->update(['payment_status' => 'failed']);

            // Capture exactly why Marx refused the initiation. Without this the
            // rejection reason (unsupported currency, bad paymentMethod, amount
            // format, merchant config, etc.) is lost and the order just shows
            // "failed" with nothing on the Marx dashboard. Secret key lives in
            // the request headers, not in $marxArgs, so this logs no credentials.
            Log::warning('Marx payment initiation rejected', [
                'order_id'      => $order->order_id,
                'http_status'   => $response->status(),
                'currency'      => $marxArgs['currency'] ?? null,
                'paymentMethod' => $marxArgs['paymentMethod'] ?? null,
                'amount'        => $marxArgs['amount'] ?? null,
                'marx_response' => $result,
            ]);

            // Give the customer their cart back so they can retry.
            if ($cartSnapshot) {
                $this->restoreCart($user->id, $cartSnapshot);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Payment initiation failed.',
                'details' => $result,
            ], 400);
        } catch (\Exception $e) {
            Log::error('Payment initiation error for order ' . ($order->order_id ?? 'unknown') . ': ' . $e->getMessage());

            // Give the customer their cart back so they can retry.
            if ($cartSnapshot) {
                $this->restoreCart($user->id, $cartSnapshot);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while processing the payment.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Recreate the user's cart from a snapshot taken before the order consumed it.
     * Called when Marx initiation fails so the customer can retry instead of being
     * stranded on "Cart is empty". No-op if the user has already started a new cart.
     */
    private function restoreCart($userId, array $snapshot)
    {
        if (Cart::where('user_id', $userId)->exists()) {
            return;
        }

        $cart = Cart::create([
            'user_id'     => $userId,
            'coupon_code' => $snapshot['coupon_code'] ?? null,
        ]);

        foreach ($snapshot['items'] as $item) {
            CartItem::create([
                'cart_id'         => $cart->id,
                'bulk_product_id' => $item['bulk_product_id'],
                'package_id'      => $item['package_id'],
                'quantity'        => $item['quantity'],
            ]);
        }
    }

    public function paymentCallbackV4($data)
    {
        try {
            $mur = $data['mur'] ?? null;
            $tr = $data['tr'] ?? null;

            if (!$mur || !$tr) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Missing required parameters.',
                ], Response::HTTP_BAD_REQUEST);
            }
            $production_url = config('marx.api_url');
            $local_user_secret = config('marx.merchant_key');

            $check_url = "{$production_url}/{$tr}";
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'merchant-api-key' => $local_user_secret,
            ])->put($check_url, [
                'merchantRID' => $mur,
            ]);

            if (!$response->successful()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to communicate with payment gateway.',
                    'details' => $response->json(),
                ], $response->status());
            }

            $result = $response->json();

            if (isset($result['data']['summaryResult']) && $result['data']['summaryResult'] === "SUCCESS") {
                $gatewayResponse = $result['data']['gatewayResponse'] ?? [];
                $amountPaid = $gatewayResponse['order']['amount'] ?? 0;

                $order = Order::where('transaction_id', $result['data']['trId'] ?? null)->first();
                if (!$order) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Order not found.',
                    ], Response::HTTP_NOT_FOUND);
                }

                if ($order->is_wallet) {
                    $wallet = Wallet::where('user_id', $order->user_id)->first();

                    if (!$wallet) {
                        return response()->json([
                            'status' => 'error',
                            'message' => 'Wallet not found.',
                        ], Response::HTTP_NOT_FOUND);
                    }

                    $wallet->increment('balance', $order->amount);
                }

                DB::beginTransaction();
                try {
                    if (!$order->is_wallet || $order->is_wallet === 0) {
                        $orderItems = OrderItems::where('order_id', $order->id)->get();

                        foreach ($orderItems as $orderItem) {
                            if ($orderItem->bulk_product_id) {
                                $bulkProduct = BulkProduct::find($orderItem->bulk_product_id);

                                if ($bulkProduct) {

                                    if($bulkProduct->bulk_type == 'serial_based') {

                                        // Parse the serials into an array
                                        $allSerials = array_values(array_filter(explode("\n", $bulkProduct->serial), 'trim'));

                                        // Check stock
                                        if (count($allSerials) < $orderItem->quantity) {
                                            throw new \Exception('Not enough stock for the bulk product');
                                        }

                                        // Remove the needed number of serials
                                        $removedSerials = array_splice($allSerials, 0, $orderItem->quantity);

                                        // Update bulk product
                                        $oldCount = $bulkProduct->serial_count;
                                        $bulkProduct->serial = implode("\n", $allSerials);
                                        $bulkProduct->serial_count = count($allSerials);
                                        $bulkProduct->save();

                                        StockNotificationService::checkAndNotify(
                                            $bulkProduct->name, 'Bulk Product', $bulkProduct->serial_count, $oldCount, $bulkProduct->id
                                        );

                                        // Save each removed serial individually
                                        foreach ($removedSerials as $serial) {
                                            RemovedBulkProductSerial::create([
                                                'bulk_product_id' => $orderItem->bulk_product_id,
                                                'order_item_id'   => $orderItem->id,
                                                'serial'          => $serial,
                                            ]);
                                        }
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
                                            // Remove the first $orderItem->quantity serials
                                            $removedSerials = array_splice($allSerials, 0, $orderItem->quantity);

                                            // Update subscription
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
                    }

                    $order->update([
                        'payment_status' => 'paid',
                        'amount_paid' => $amountPaid,
                    ]);

                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    $order->update(['payment_status' => 'failed']);
                    Log::error('Payment callback processing error: ' . $e->getMessage());
                    return response()->json([
                        'status' => 'error',
                        'message' => $e->getMessage(),
                    ], Response::HTTP_BAD_REQUEST);
                }

                // Product purchases earn wallet cashback; wallet TOP-UPs
                // (is_wallet=true in this flow) never do.
                if (!$order->is_wallet) {
                    \App\Services\WalletCashbackService::creditForOrder($order);
                }

                $user = User::find($order->user_id);
                $user->notify(new OrderCreated($order));

                return response()->json([
                    'status' => 'success',
                    'summaryResult' => 'SUCCESS',
                    'order_id' => $order->id,
                    'amount' => $order->amount,
                    'amount_paid' => $amountPaid
                ]);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Payment failed or invalid response.',
                'details' => $result,
            ], Response::HTTP_BAD_REQUEST);

        } catch (\Exception $e) {
            Log::error('Payment callback error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while processing the payment callback.',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
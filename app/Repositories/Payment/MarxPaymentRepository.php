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
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Models\Product\Contribution\RemovedContributionProductSerial;
use App\Models\User\User;
use App\Notifications\OrderCreated;

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
                if($package->subscription->service_type == 'serial_based') {
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

                if ($coupon->expiry_date < now()) {
                    return response()->json(['message' => 'Coupon has expired'], Response::HTTP_BAD_REQUEST);
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
            $cart = Cart::where('user_id',$user->id)->first();
            if ($cart) {
                CartItem::where('cart_id', $cart->id)->get()->each(function ($cartItem) use ($order) {
                    OrderItems::create([
                        'order_id' => $order->id,
                        'bulk_product_id' => $cartItem->bulk_product_id,
                        'package_id' => $cartItem->package_id,
                        'quantity' => $cartItem->quantity,
                    ]);
                });

                $cart->cartItems()->delete();
                $cart->delete();
            }
        }
        $gateway_fee = $amount * $gateway_fee / 100;
        $amount = $amount + $gateway_fee;
        $marxArgs = [
            'merchantRID' => $order->order_id,
            'amount' => floatval($amount),
            'returnUrl' => "https://cheaphub.io/marxpay",
            'validTimeLimit' => 30,
            'customerMail' => $data['email'] ?? '',
            'customerMobile' => $data['tel'] ?? '',
            'mode' => "WEB",
            'currency' => $data['currency'],
            'orderSummary' => $data['description'] ?? '',
            'customerReference' => $user->id . " " . ($data['email'] ?? ''),
            "paymentMethod"=> $data['paymentMethod']

        ];


        try {
           
            $local_user_secret = 'OTYwZTVkYmEtMGFiZi00OGQ0LTk5ZDctNGM1YWY2NjhkNWUwXzkxMjY=';
            $marx_sandbox_url = 'https://payment.v4.api.marx.lk/api/v4/ipg/orders';
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'merchant-api-key' => $local_user_secret,

            ])->post($marx_sandbox_url , $marxArgs);

            $result = $response->json();

            Log::info('Payment initiation response: ', $result);
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
            return response()->json([
                'status' => 'error',
                'message' => 'Payment initiation failed.',
                'details' => $result,
            ], 400);
        } catch (\Exception $e) {
            Log::error('Payment initiation error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while processing the payment.',
                'error' => $e->getMessage(),
            ], 500);
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
            $production_url = 'https://payment.v4.api.marx.lk/api/v4/ipg/orders';
            $local_user_secret = 'OTYwZTVkYmEtMGFiZi00OGQ0LTk5ZDctNGM1YWY2NjhkNWUwXzkxMjY=';

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
                                        $order->update([
                                            'payment_status' => 'failed',
                                        ]);
                                        return response()->json(['message' => 'Not enough stock for the bulk product'], Response::HTTP_BAD_REQUEST);
                                    }

                                    // Remove the needed number of serials
                                    $removedSerials = array_splice($allSerials, 0, $orderItem->quantity);

                                    // Update bulk product
                                    $bulkProduct->serial = implode("\n", $allSerials);
                                    $bulkProduct->serial_count = count($allSerials);
                                    $bulkProduct->save();

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

                                if ($subscription) {
                                    if ($orderItem->quantity > $subscription->available_serial_count) {
                                        $order->update([
                                            'payment_status' => 'failed',
                                        ]);
                                        return response()->json(['message' => 'Not enough stock for the bulk product'], Response::HTTP_BAD_REQUEST);
                                    }

                                    if($subscription->service_type == 'serial_based') {
                                        $allSerials = array_values(array_filter(explode("\n", $subscription->serial), 'trim'));

                                        if (!empty($allSerials)) {
                                            // Remove the first $orderItem->quantity serials
                                            $removedSerials = array_splice($allSerials, 0, $orderItem->quantity);

                                            // Update subscription
                                            $subscription->serial = implode("\n", $allSerials);
                                            $subscription->available_serial_count = max(0, $subscription->available_serial_count - $orderItem->quantity);
                                            $subscription->save();
                
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
                }

                $order->update([
                    'payment_status' => 'paid',
                    'amount_paid' => $amountPaid,
                ]);

                

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
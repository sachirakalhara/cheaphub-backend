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
use App\Models\Subscription\Package;
use App\Models\Subscription\Subscription;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\MailQueue;
use App\Models\Product\Contribution\ProductReplacement;
use App\Models\User\User;
use App\Notifications\OrderCreated;
use NunoMaduro\Collision\Adapters\Phpunit\Subscribers\Subscriber;

class MarxPaymentRepository implements MarxPaymentRepositoryInterface
{
    
    public function makePaymentV4($data)
    {
        $user = Auth::user();
        $amount = $data['amount'] ?? 0;
        $discount = 0;

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
                if (!$package) {
                    return response()->json(['message' => 'Package not found'], Response::HTTP_NOT_FOUND);
                }

                if ($package->subscription->available_serial_count < $cartItemPackage->quantity) {
                    return response()->json(['message' => 'Not enough stock for the package'], Response::HTTP_BAD_REQUEST);
                }
            }

            foreach ($cartItemBulkProducts as $cartItemBulkProduct) {
                $bulkProduct = BulkProduct::find($cartItemBulkProduct->bulk_product_id);
                if (!$bulkProduct) {
                    return response()->json(['message' => 'Bulk product not found'], Response::HTTP_NOT_FOUND);
                }

                if ($bulkProduct->serial_count < $cartItemBulkProduct->quantity) {
                    return response()->json(['message' => 'Not enough stock for the bulk product'], Response::HTTP_BAD_REQUEST);
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

                    $wallet->increment('balance', $amountPaid);
                }

                $order->update([
                    'payment_status' => 'paid',
                    'amount_paid' => $amountPaid,
                ]);

                if (!$order->is_wallet || $order->is_wallet === 0) {
                    $orderItems = OrderItems::where('order_id', $order->id)->get();
                    foreach ($orderItems as $orderItem) {
                        if ($orderItem->bulk_product_id) {
                            $bulkProduct = BulkProduct::find($orderItem->bulk_product_id);
                            if ($bulkProduct) {
                                $bulkProduct->serial_count -=  $orderItem->quantity;
                                $bulkProduct->save();
                            }
                        }

                        if ($orderItem->package_id) {
                            $package = Package::find($orderItem->package_id);
                            if ($package) {
                                $subscription = Subscription::find($package->subscription_id);
                                if ($subscription) {
                                    $subscription->available_serial_count -= $orderItem->quantity;
                                    $subscription->save();

                                    $productReplacement = ProductReplacement::where('user_id',$order->user_id)->where('package_id',$package->id)->first();
                                    if($productReplacement){
                                        $productReplacement->avalable_replace_count = $subscription->available_serial_count;
                                        $subscription->save();
                                    }

                                }
                            }
                        }
                    }
                }

                $user = User::find($order->user_id);
                $user->notify(new OrderCreated($order)); 

                return response()->json([
                    'status' => 'success',
                    'summaryResult' => 'SUCCESS',
                    'order_id' => $order->id,
                    'amount_paid' => $amountPaid,
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
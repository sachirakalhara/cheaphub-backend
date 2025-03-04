<?php

namespace App\Repositories\Payment;

use App\Helpers\Helper;
use App\Models\Cart\Cart;
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
use NunoMaduro\Collision\Adapters\Phpunit\Subscribers\Subscriber;

class MarxPaymentRepository implements MarxPaymentRepositoryInterface
{
    
    public function makePayment($data)
    {
        $user = Auth::user();

        if (isset($data['cart_id']) && $data['is_wallet'] == false) {
            $cart = Cart::find($data['cart_id']);
            if (!$cart) {
                return response()->json(['message' => 'Cart not found'], Response::HTTP_NOT_FOUND);
            }

            if ($cart->bulk_product_id) {
                $bulkProduct = BulkProduct::find($cart->bulk_product_id);
                if (!$bulkProduct || $bulkProduct->serial_count < $cart->quantity) {
                    return response()->json(['message' => 'Not enough stock for the bulk product'], Response::HTTP_BAD_REQUEST);
                }
            }

            if ($cart->package_id) {
                $package = Package::find($cart->package_id);
                
                if (!$package || $package->subscription->available_serial_count < $cart->quantity) {
                    return response()->json(['message' => 'Not enough stock for the serial'], Response::HTTP_BAD_REQUEST);
                }
            }
        }

        $order = Order::create([
            'amount' => $data['amount'],
            'currency' => $data['currency'],
            'description' => $data['description'] ?? '',
            'payment_status' => 'pending',
            'is_wallet' => $data['is_wallet'] ?? false,
            'user_id' => $user->id,
            'order_id' => 'order_' . now()->format('YmdHis'),
        ]);

        if (($data['is_wallet'] == false || $data['is_wallet'] == 'false')) {
            $cart = Cart::find($data['cart_id']);
            if ($cart) {
            OrderItems::create([
                'order_id' => $order->id,
                'bulk_product_id' => $cart->bulk_product_id,
                'package_id' => $cart->package_id,
                'quantity' => $cart->quantity,
            ]);
            $cart->delete();
            }
        }

        // Fetch credentials from .env
        $currencyConfig = [
            'LKR' => [
                'user_secret' => env('MARXPAY_LKR_USER_SECRET'),
                'url' => env('MARXPAY_LKR_URL'),
            ],
            'USD' => [
                'user_secret' => env('MARXPAY_USD_USER_SECRET'),
                'url' => env('MARXPAY_USD_URL'),
            ],
        ];

        if (!isset($currencyConfig[$data['currency']])) {
            $order->update(['payment_status' => 'failed']);

            return response()->json([
                'status' => 'error',
                'message' => 'Unsupported currency.',
            ], 400);
        }

        // Prepare API payload
        $marxArgs = [
            'merchantRID' => $order->order_id,
            'amount' => floatval($data['amount']),
            'returnUrl' => route('marxpay.callback'),
            'validTimeLimit' => 30,
            'customerMail' => $data['email'],
            'customerMobile' => $data['tel'],
            'mode' => "WEB",
            'currency' => $data['currency'],
            'orderSummary' => $data['description'],
            'customerReference' => $user->id . " " . $data['email'],
        ];

        try {
            $response = Http::withHeaders([
                'user_secret' => $currencyConfig[$data['currency']]['user_secret'],
                'Content-Type' => 'application/json',
            ])->post($currencyConfig[$data['currency']]['url'], $marxArgs);

            $result = $response->json();
            if ($response->successful() && isset($result['data']['payUrl'])) {
                $order->update([
                    'payment_status' => 'pending',
                    'transaction_id' => $result['data']['trId']
                ]);
                return response()->json([
                    'status' => 'success',
                    'redirect_url' => $result['data']['payUrl'],
                    'transaction_id' => $result['data']['trId']
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
    
    public function paymentCallback($data)
    {

        try {
            $mur = $data['mur'];
            $tr = $data['tr'];

            // Fetch credentials from .env
            $currencyConfig = [
                'LKR' => [
                    'user_secret' => env('MARXPAY_LKR_USER_SECRET'),
                    'url' => env('MARXPAY_LKR_URL') . "/{$tr}",
                ],
                'USD' => [
                    'user_secret' => env('MARXPAY_USD_USER_SECRET'),
                    'url' => env('MARXPAY_USD_URL') . "/{$tr}",
                ],
            ];

            // Prepare API request
            $marxArgs = ['merchantRID' => $mur];
            $response = Http::withHeaders([
                'user_secret' => $currencyConfig['LKR']['user_secret'],
                'Content-Type' => 'application/json',
            ])->put($currencyConfig['LKR']['url'], $marxArgs);

            $result = $response->json();
            if (isset($result['data']['summaryResult']) && $result['data']['summaryResult'] === "SUCCESS") {
                $gatewayResponse = $result['data']['gatewayResponse'];
                $amountPaid = $gatewayResponse['order']['amount'];

                $order = Order::where('transaction_id', $result['data']['trId'])->first();
                if ($order) {
                    if($order->is_wallet == true){
                        $wallet = Wallet::where('user_id', $order->user_id)->first();

                        if (!$wallet) {
                            return Helper::error('Wallet not found', Response::HTTP_NOT_FOUND);
                        }
                
                        $wallet->increment('balance', $amountPaid);
                    }
                    $order->update([
                        'payment_status' => 'paid',
                        'amount_paid' => $amountPaid,
                    ]);


                    $orderItem = OrderItems::where('order_id', $order->id)->first();
                    if ($orderItem->bulk_product_id) {
                        $bulkProduct = BulkProduct::find($orderItem->bulk_product_id);
                        if ($bulkProduct) {
                            $bulkProduct->decrement('serial_count', $orderItem->quantity);
                        }
                    }

                    if ($orderItem->package_id) {
                        $package = Package::find($orderItem->package_id);
                        if ($package) {
                            Subscription::find($package->subscription_id)->decrement('available_serial_count', $orderItem->quantity);
                        }
                    }
                }

                return response()->json([
                    'status' => 'success',
                    'summaryResult' => 'SUCCESS',
                    'order_id' => $order->id,
                    'amount_paid' => $amountPaid,
                ]);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Payment failed or invalid response',
            ], 500);

        } catch (\Exception $e) {
            Log::error('Payment callback error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while processing the payment callback.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


}
<?php

namespace App\Repositories\Payment;

use App\Helpers\Helper;
use App\Repositories\Payment\Interface\MarxPaymentRepositoryInterface;
use Illuminate\Http\Response;
use App\Models\Payment\Order;
use App\Models\Payment\Wallet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
class MarxPaymentRepository implements MarxPaymentRepositoryInterface
{
    
    public function makePayment($data)
    {
        $user = Auth::user();

        // Create new order with pending status
        $order = Order::create([
            'amount' => $data['amount'],
            'currency' => $data['currency'],
            'description' => $data['description'] ?? '',
            'payment_status' => 'pending',
            'is_wallet' => $data['is_wallet'] ?? false,
            'user_id' => $user->id
        ]);

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
            'merchantRID' => $order->id,
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
            Log::info('MarxPay Response:', $result);

            if ($response->successful() && isset($result['data']['payUrl'])) {
                $order->update(['payment_status' => 'paid']);
                return response()->json([
                    'status' => 'success',
                    'redirect_url' => $result['data']['payUrl'],
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
            ], 500);
        }
    }
    
    public function paymentCallback($data)
    {
        Log::info('Received payment callback request');
        Log::info($data);

        try {
            $mur = $data['mur'];
            $tr = $data['tr'];
            $currency = $data['currency'];

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

            if (!isset($currencyConfig[$currency])) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid currency type',
                ], 400);
            }

            // Prepare API request
            $marxArgs = ['merchantRID' => $mur];

            $response = Http::withHeaders([
                'user_secret' => $currencyConfig[$currency]['user_secret'],
                'Content-Type' => 'application/json',
            ])->put($currencyConfig[$currency]['url'], $marxArgs);

            $result = $response->json();
            Log::info('Payment callback response', ['response' => $result]);

            if (isset($result['data']['summaryResult']) && $result['data']['summaryResult'] === "SUCCESS") {
                $gatewayResponse = $result['data']['gatewayResponse'];
                $orderId = $gatewayResponse['order']['id'];
                $amountPaid = $gatewayResponse['order']['totalCapturedAmount'];

                $order = Order::find($orderId);
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
                        'transaction_id' => $tr,
                        'amount_paid' => $amountPaid,
                    ]);
                }

                return response()->json([
                    'status' => 'success',
                    'summaryResult' => 'SUCCESS',
                    'order_id' => $orderId,
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
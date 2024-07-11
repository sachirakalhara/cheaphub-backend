<?php

namespace App\Repositories\Payment;

use App\Helpers\Helper;
use App\Repositories\Payment\Interface\MarxPaymentRepositoryInterface;
use Illuminate\Http\Response;
use App\Models\Payment\Order;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
class MarxPaymentRepository implements MarxPaymentRepositoryInterface
{
    
    public function makePayment($request)
    {

         // Create a new order
         $order = Order::create([
            'amount' => $request->input('amount'),
            'currency' => $request->input('currency'),
            'description' => $request->input('description'),
            'payment_status' => 'pending',
        ]);

        // Create the payload
        $payload = [
            'amount' => $request->input('amount'),
            'currency' => $request->input('currency'),
            'description' => $request->input('description'),
            'redirect_url' => route('marxpay.callback'), // Callback route
            'customer_email' => $request->input('email'), // Example additional field
            'order_id' => $order->id, // Send the order ID with the payment request
            // Include other necessary data as required by MarxPay
        ];

        try {
            // Send the request to MarxPay API
            $response = Http::post('https://api.marxpay.com/payments', $payload);

            if ($response->successful()) {
                return response()->json([
                    'status' => 'success',
                    'payment_url' => $response->json()['payment_url'], // Assuming the API returns a payment URL
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Payment initiation failed.',
                    'details' => $response->json(),
                ], 400);
            }
        } catch (\Exception $e) {
            Log::error('Payment initiation error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while initiating the payment.',
            ], 500);
        }
    }

    public function paymentCallback($request)
    {
        // Log the callback request for debugging
        Log::info('Payment callback received: ', $request->all());

        // Retrieve the necessary data from the callback request
        $transactionId = $request->input('transaction_id');
        $status = $request->input('status');
        $amount = $request->input('amount');
        $currency = $request->input('currency');
        $orderId = $request->input('order_id'); // Assuming you sent an order ID with the payment request

        try {
            // Find the order by ID
            $order = Order::findOrFail($orderId);

            // Verify the payment status and update the order record
            if ($status === 'success') {
                $order->update([
                    'payment_status' => 'paid',
                    'transaction_id' => $transactionId,
                    'amount_paid' => $amount,
                    'currency' => $currency,
                ]);

                // Notify the user or perform additional actions as needed
            } else {
                $order->update([
                    'payment_status' => 'failed',
                ]);

                // Handle the payment failure, notify the user, etc.
            }

            return response()->json(['status' => 'ok']);
        } catch (\Exception $e) {
            Log::error('Payment callback handling error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'An error occurred while processing the payment callback.'], 500);
        }
    }
}
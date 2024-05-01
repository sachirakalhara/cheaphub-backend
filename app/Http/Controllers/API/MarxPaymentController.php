<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\MarxPayment;
use Illuminate\Http\Request;

class MarxPaymentController extends Controller
{
    public function processPayment(Request $request)
    {
        // Initialize Marx IPG with credentials
        $ipg = new IPG(
            config('marx_ipg.api_key'),
            config('marx_ipg.api_secret'),
            config('marx_ipg.api_url')
        );

        // Process payment
        $response = $ipg->processPayment($request->all());

        // Handle response
        if ($response->success) {
            // Payment successful
            return redirect()->back()->with('success', 'Payment successful');
        } else {
            // Payment failed
            return redirect()->back()->with('error', 'Payment failed: ' . $response->message);
        }
    }
}

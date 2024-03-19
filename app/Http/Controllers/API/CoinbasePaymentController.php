<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use GuzzleHttp\Client;

class CoinbasePaymentController extends Controller
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => env('APP_URL'),
            'headers' => [
                'X-CC-Api-Key' => config('coinbase.api_key'),
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);
    }

    public function createPayment(Request $request)
    {
        $customer_id = $request->input('customer_id');
        $amount = $request->input('amount');
var_dump( $customer_id);
        $response = $this->client->post('charges', [
            'json' => [
                'name' => 'Sample charge',
                'description' => 'Sample charge description',
                'local_price' => [
                    'amount' => '0.01',
                    'currency' => 'USD',
                ],
                'pricing_type' => 'fixed_price',
                'metadata' => [
                    'customer_id' => $customer_id,
                ],
            ],
        ]);

        $data = json_decode($response->getBody()->getContents(), true);

        return response()->json($data);
    }

    public function paymentCallback(Request $request)
    {
        return $request;
    }
}

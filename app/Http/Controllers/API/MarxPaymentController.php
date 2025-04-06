<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Repositories\Payment\Interface\MarxPaymentRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MarxPaymentController extends Controller
{
    private $marxPaymentRepository;

    public function __construct(MarxPaymentRepositoryInterface $marxPaymentRepository)
    {
        $this->marxPaymentRepository = $marxPaymentRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function makePayment(Request $request)
    {
        // Validate required fields
        $request->validate([
            'email' => 'required|email',
            'tel' => 'required|string',
            'amount' => 'required|numeric',
            'currency' => 'required|string',
            'description' => 'nullable|string',
            'is_wallet' => 'nullable|boolean',
        ]);

        return $this->marxPaymentRepository->makePayment($request);
    }    

    public function paymentCallback(Request $request)
    {
        $request->validate([
            'currency' => 'required|string'
        ]);
        return $this->marxPaymentRepository->paymentCallback($request);
    }
}
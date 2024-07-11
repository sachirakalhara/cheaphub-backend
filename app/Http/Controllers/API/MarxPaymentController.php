<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Repositories\Payment\Interface\MarxPaymentRepositoryInterface;
use Illuminate\Http\Request;

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
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'required|string|size:3', // e.g., USD
            'description' => 'required|string|max:255',
            'email' => 'required',
        ]);

        return $this->marxPaymentRepository->makePayment($request);
    }

    public function paymentCallback(Request $request)
    {
        return $this->marxPaymentRepository->paymentCallback($request);
    }
}
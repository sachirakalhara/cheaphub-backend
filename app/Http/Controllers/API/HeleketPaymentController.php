<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Repositories\Payment\Interface\HeleketPaymentRepositoryInterface;
use Illuminate\Http\Request;

class HeleketPaymentController extends Controller
{
    private $heleketPaymentRepository;

    public function __construct(HeleketPaymentRepositoryInterface $heleketPaymentRepository)
    {
        $this->heleketPaymentRepository = $heleketPaymentRepository;
    }

    /**
     * Create a crypto invoice and return the hosted payment page URL.
     */
    public function createInvoice(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string',
            'is_wallet' => 'nullable|boolean',
        ]);

        return $this->heleketPaymentRepository->createInvoice($request);
    }

    /**
     * Handle the server-to-server webhook Heleket sends on status changes.
     * No request validation here — the repository verifies the signature.
     */
    public function handleWebhook(Request $request)
    {
        return $this->heleketPaymentRepository->handleWebhook($request);
    }
}

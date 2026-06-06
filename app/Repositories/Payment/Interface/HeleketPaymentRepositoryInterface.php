<?php

namespace App\Repositories\Payment\Interface;

interface HeleketPaymentRepositoryInterface
{
    public function createInvoice($request);
    public function handleWebhook($request);
}

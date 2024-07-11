<?php

namespace App\Repositories\Payment\Interface;

interface MarxPaymentRepositoryInterface
{
    public function makePayment($request);
    public function paymentCallback($request);
}

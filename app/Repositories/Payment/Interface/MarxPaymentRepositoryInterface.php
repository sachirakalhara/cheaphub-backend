<?php

namespace App\Repositories\Payment\Interface;

interface MarxPaymentRepositoryInterface
{
    public function makePaymentV4($request);
    public function paymentCallbackV4($request);
    
}

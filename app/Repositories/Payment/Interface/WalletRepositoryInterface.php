<?php

namespace App\Repositories\Payment\Interface;

interface WalletRepositoryInterface
{
    public function show();
    public function processWalletPaymentForProduct($data);
    
}

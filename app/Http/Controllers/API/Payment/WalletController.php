<?php

namespace App\Http\Controllers\API\Payment;

use App\Http\Controllers\Controller;
use App\Models\Payment\Wallet;
use App\Repositories\Payment\Interface\WalletRepositoryInterface;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    private $walletRepository;

    public function __construct(WalletRepositoryInterface $walletRepository)
    {
        $this->walletRepository = $walletRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function show()
    {
        return $this->walletRepository->show();
    }
    
}

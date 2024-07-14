<?php

namespace App\Repositories\Payment;

use App\Helpers\Helper;
use App\Http\Resources\Payment\WalletResource;
use Illuminate\Http\Response;
use App\Repositories\Payment\Interface\WalletRepositoryInterface;
use Illuminate\Support\Facades\Auth;
class WalletRepository implements WalletRepositoryInterface
{
    
    public function show()
    {
        $user = Auth::user();
        $wallet = $user->virtualWallet;
        if ($wallet) {
            return new WalletResource($wallet);
        } else {
            return Helper::success(Response::$statusTexts[Response::HTTP_NO_CONTENT], Response::HTTP_NO_CONTENT);
        }
    }
}
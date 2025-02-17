<?php

namespace App\Repositories\Payment;

use App\Helpers\Helper;
use App\Http\Resources\Payment\WalletResource;
use App\Models\Payment\Wallet;
use Illuminate\Http\Response;
use App\Repositories\Payment\Interface\WalletRepositoryInterface;
use Illuminate\Support\Facades\Auth;

class WalletRepository implements WalletRepositoryInterface
{
    public function show()
    {
        $user = Auth::user();
        $wallet = Wallet::where('user_id', $user->id)->first();

        if (!$wallet) {
            return Helper::error('Wallet not found', Response::HTTP_NOT_FOUND);
        }

        return new WalletResource($wallet);
    }
}

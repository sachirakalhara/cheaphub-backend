<?php

namespace App\Http\Resources\User;

use App\Models\Payment\Order;
use App\Models\Payment\Wallet;
use App\Models\Ticket\Ticket;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class UserInfoResource extends JsonResource
{
    public static $wrap = 'user';

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $wallet = Wallet::where('user_id', $this->id)->get();
        $wallet_balance = $wallet->sum('balance');
    
        $orders = Order::with('tickets')->where('user_id', $this->id)->get();
        $ticketsCount = $orders->flatMap->tickets->count();
    
        return [
            'id' => $this->id,
            'display_name' => $this->display_name,
            'fname' => $this->fname,
            'lname' => $this->lname,
            'email' => $this->email,
            'wallet_balance' => $wallet_balance,
            'order_count' => $orders->count(),
            'ticket_count' => $ticketsCount,
            'user_created' => $this->created_at,
        ];
    }
}

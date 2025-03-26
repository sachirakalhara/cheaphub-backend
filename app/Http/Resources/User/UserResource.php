<?php

namespace App\Http\Resources\User;

use App\Models\Payment\Order;
use App\Models\Payment\Wallet;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class UserResource extends JsonResource
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
        $disk = Storage::disk('s3');
        $profile_photo = $this->profile_photo ? $disk->url($this->profile_photo) : null;
        $user_spend = Order::where('user_id', $this->id)->where('is_wallet', false)->where('payment_status', 'paid')->sum('amount_paid');
        $total_spend = Order::where('is_wallet', false)->where('payment_status', 'paid')->sum('amount_paid');

        return [
            'id'=>$this->id,
            'display_name'=> $this->display_name,
            'fname'=>$this->fname,
            'lname'=>$this->lname,
            'contact_no'=>$this->contact_no,
            'email'=> $this->email,
            'contact_number'=> $this->contact_no,
            'profile_photo' => $profile_photo,
            'user_level'=>$this->userLevel->scope,
            'wallet' => $this->wallet ? $this->wallet->balance : '0.00',
            'user_spend' => $user_spend,
            // 'total_spend' => $total_spend,
            // 'total_customer' => $this->get()->count(),
            'order_history' => $this->order
        ];
    }
}

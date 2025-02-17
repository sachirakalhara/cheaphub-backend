<?php

namespace App\Http\Resources\Payment;

use Illuminate\Http\Resources\Json\JsonResource;

class WalletResource extends JsonResource
{
    public static $wrap = 'wallet';

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'=>$this->id,
            'user'=>$this->user,
            'currency'=> $this->currency,
            'balance'=>$this->balance

        ];
    }

}

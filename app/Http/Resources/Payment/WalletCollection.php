<?php

namespace App\Http\Resources\Payment;

use Illuminate\Http\Resources\Json\ResourceCollection;

class WalletCollection extends ResourceCollection
{
    public static $wrap = 'wallet_list';

    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'wallet_list'=> WalletResource::collection($this->collection)
        ];
    }


}

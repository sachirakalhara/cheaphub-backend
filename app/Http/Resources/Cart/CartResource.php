<?php

namespace App\Http\Resources\Cart;
use Illuminate\Support\Facades\Storage;

use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    public static $wrap = 'cart';

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
            'user'=> $this->user,
            'coupon_code'=> $this->coupon_code,
            'cartItems'=> $this->cartItems->map(function ($cartItem) {
                return [
                    'id' => $cartItem->id,
                    'bulk_product' => $cartItem->bulkProduct,
                    'package' => $cartItem->package,
                    'quantity' => $cartItem->quantity,
                ];
            }),
        ];
    }

}

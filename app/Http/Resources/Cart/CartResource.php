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
            'cartItems'=> collect($this->cartItems)->map(function ($item) {
                if (is_object($item)) {
                    return [
                        'id' => $item->id,
                        'bulk_product' => $item->bulkProduct,
                        'package' => $item->package,
                        'quantity' => $item->quantity,
                        'price' => $item->bulkProduct ? $item->bulkProduct->price : ($item->package ? $item->package->price : 0),
                        'total' => $item->quantity * ($item->bulkProduct ? $item->bulkProduct->price : ($item->package ? $item->package->price : 0)),
                    ];
                }
                return null;
            })->filter(),
        ];
    }

}

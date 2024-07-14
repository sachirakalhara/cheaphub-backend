<?php

namespace App\Http\Resources\Cart;

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
            'quantity'=> $this->quantity,
            'bulkProduct'=> $this->bulkProduct,
            'contributionProduct'=> $this->contributionProduct
        ];
    }

}

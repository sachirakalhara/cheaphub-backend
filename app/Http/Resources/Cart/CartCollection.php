<?php

namespace App\Http\Resources\Cart;

use Illuminate\Http\Resources\Json\ResourceCollection;

class CartCollection extends ResourceCollection
{
    public static $wrap = 'cart_list';

    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'cart_list'=> CartResource::collection($this->collection)
        ];
    }


}

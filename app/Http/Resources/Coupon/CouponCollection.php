<?php

namespace App\Http\Resources\Coupon;

use Illuminate\Http\Resources\Json\ResourceCollection;

class CouponCollection extends ResourceCollection
{
    public static $wrap = 'coupon_list';

    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'coupon_list'=> CouponResource::collection($this->collection)
        ];
    }


}

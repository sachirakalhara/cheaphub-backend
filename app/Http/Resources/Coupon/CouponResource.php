<?php

namespace App\Http\Resources\Coupon;

use Illuminate\Http\Resources\Json\JsonResource;

class CouponResource extends JsonResource
{
    public static $wrap = 'coupon';

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
            'product_type'=>$this->product_type,
            'discount_percentage'=>$this->discount_percentage,
            'max_discount_amount'=>$this->max_discount_amount,
            'expiry_date'=>$this->expiry_date,
            'coupon_code'=>$this->coupon_code

        ];
    }

}

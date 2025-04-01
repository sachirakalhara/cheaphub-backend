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
        if ($this->package) {
            $this->package->image = $this->package->image ? Storage::disk('s3')->url($this->package->image) : null;
        }
        if ($this->bulkProduct) {
            $this->bulkProduct->image = $this->bulkProduct->image ? Storage::disk('s3')->url($this->bulkProduct->image) : null;
        }
        return [
            'id'=>$this->id,
            'user'=> $this->user,
            'quantity'=> $this->quantity,
            'bulkProduct'=> $this->bulkProduct,
            'package'=> $this->package,
            'coupon_code'=> $this->coupon_code
        ];
    }

}

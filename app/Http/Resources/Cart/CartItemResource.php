<?php

namespace App\Http\Resources\Cart;
use Illuminate\Support\Facades\Storage;

use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    public static $wrap = 'cart_item';

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $contributionProduct_image = null;
        if ($this->bulkProduct) {
            $this->bulkProduct->image = $this->bulkProduct->image ? Storage::disk('s3')->url($this->bulkProduct->image) : null;
        }
        if ($this->package) {
            $contributionProduct_image = $this->package->subscription->contributionProduct ? Storage::disk('s3')->url($this->package->subscription->contributionProduct->image) : null;
        }
        return [
            'id'=>$this->id,
            'quantity'=> $this->quantity,
            'bulkProduct'=> $this->bulkProduct,
            'package'=> $this->package,
            'contributionProduct_image' => $contributionProduct_image
        ];
    }

}

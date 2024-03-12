<?php

namespace App\Http\Resources\Product;

use App\Helpers\Helper;
use App\Http\Resources\Category\CategoryResource;
use App\Http\Resources\Serial\SerialResource;
use App\Models\Serial\Serial;
use App\Models\Subscription\Subscription;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public static $wrap = 'product';

    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'tag_id' => $this->tag_id,
            'description' => $this->description,
            'price' => $this->price,
            'gateway_fee' => $this->gateway_fee,
            'subscription_id' => $this->subscription_id,
            'subscription' => Subscription::find($this->subscription_id),
            'categories' => CategoryResource::collection($this->categories),
            'image' => $this->image ? asset('uploads/' . $this->image) : null,
            'serials' => SerialResource::collection($this->serials)
        ];
    }

}

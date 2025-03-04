<?php

namespace App\Http\Resources\Product\Bulk;

use App\Http\Resources\Category\CategoryResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Resources\Json\JsonResource;

class BulkProductResource extends JsonResource
{
    public static $wrap = 'bulk_product';

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
            'categories' => CategoryResource::collection($this->categories),
            'image' => $this->image ? Storage::url($this->image) : null,
            'visibility' => $this->visibility,
            'slug_url' => route('api.slug-product.slug', [ 'slug_name' => $this->slug_url]),
            'service_info' => $this->service_info,
            'minimum_quantity' => $this->minimum_quantity,
            'maximum_quantity' => $this->maximum_quantity,
            'serial' => $this->serial,
            'serial_count' => $this->serial_count,
            'payment_method' => $this->payment_method,

        ];
    }

}

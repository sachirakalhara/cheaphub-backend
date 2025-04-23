<?php

namespace App\Http\Resources\Product\Bulk;

use App\Http\Resources\Category\CategoryResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

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
        $disk = Storage::disk('s3');
        $image = $this->image ? $disk->url($this->image) : null;

        $test = !empty(Auth::user()->id) ? `cheaphub.io/bulk/{$this->id}/{$this->name}` : null;
        dd($test,`cheaphub.io/bulk/{$this->id}/{$this->name}`,!empty(Auth::user()->id));



        return [
            'id' => $this->id,
            'name' => $this->name,
            'tag_id' => $this->tag_id,
            'description' => $this->description,
            'price' => $this->price,
            'gateway_fee' => $this->gateway_fee,
            'categories' => CategoryResource::collection($this->categories),
            'image' => $image,
            'visibility' => $this->visibility,
            'url' => !empty(Auth::user()->id) ? `cheaphub.io/bulk/{$this->id}/{$this->name}` : null,
            'service_info' => $this->service_info,
            'minimum_quantity' => $this->minimum_quantity,
            'maximum_quantity' => $this->maximum_quantity,
            'serial' => $this->serial,
            'available_serial_count' => $this->serial_count,
            'payment_method' => $this->payment_method,

        ];
    }

}

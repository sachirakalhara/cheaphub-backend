<?php

namespace App\Http\Resources\Serial;

use App\Http\Resources\Category\CategoryResource;
use App\Models\Serial\Serial;
use App\Models\Subscription\Subscription;
use Illuminate\Http\Resources\Json\JsonResource;

class SerialResource extends JsonResource
{
    public static $wrap = 'serial';

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
            'type' => $this->type,
            'usage' => $this->usage,
            'min_count' => $this->min_count,
            'max_count' => $this->max_count,
            'product_id' => $this->product_id,
            'product' => $this->product
        ];
    }

}

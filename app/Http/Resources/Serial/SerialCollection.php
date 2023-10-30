<?php

namespace App\Http\Resources\Serial;

use App\Http\Resources\Product\ProductResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class SerialCollection extends ResourceCollection
{
    public static $wrap = 'serial_list';

    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'serial_list'=> SerialResource::collection($this->collection)
        ];
    }


}

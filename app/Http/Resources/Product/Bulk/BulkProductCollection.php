<?php

namespace App\Http\Resources\Product\Bulk;

use Illuminate\Http\Resources\Json\ResourceCollection;

class BulkProductCollection extends ResourceCollection
{
    public static $wrap = 'product_list';

    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'bulk_product_list'=> BulkProductResource::collection($this->collection)
        ];
    }


}

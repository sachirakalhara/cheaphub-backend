<?php

namespace App\Http\Resources\Region;

use Illuminate\Http\Resources\Json\ResourceCollection;

class RegionCollection extends ResourceCollection
{
    public static $wrap = 'region_list';

    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'region_list'=> RegionResource::collection($this->collection)
        ];
    }


}

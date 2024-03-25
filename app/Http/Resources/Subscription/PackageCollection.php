<?php

namespace App\Http\Resources\Subscription;

use Illuminate\Http\Resources\Json\ResourceCollection;

class PackageCollection extends ResourceCollection
{
    public static $wrap = 'package_list';

    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'package_list'=> PackageResource::collection($this->collection)
        ];
    }

}

<?php

namespace App\Http\Resources\Tag;

use Illuminate\Http\Resources\Json\ResourceCollection;

class TagCollection extends ResourceCollection
{
    public static $wrap = 'tag_list';

    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'tag_list'=> TagResource::collection($this->collection)
        ];
    }


}

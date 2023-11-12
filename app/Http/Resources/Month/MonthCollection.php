<?php

namespace App\Http\Resources\Month;

use Illuminate\Http\Resources\Json\ResourceCollection;

class MonthCollection extends ResourceCollection
{
    public static $wrap = 'month_list';

    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'month_list'=> MonthResource::collection($this->collection)
        ];
    }


}

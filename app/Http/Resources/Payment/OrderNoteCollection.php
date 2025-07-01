<?php

namespace App\Http\Resources\Payment;

use Illuminate\Http\Resources\Json\ResourceCollection;

class OrderNoteCollection extends ResourceCollection
{
    public static $wrap = 'order_notes_list';

    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'order_notes_list'=> OrderNoteResource::collection($this->collection)
        ];
    }


}

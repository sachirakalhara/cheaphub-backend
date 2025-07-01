<?php

namespace App\Http\Resources\Payment;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderNoteResource extends JsonResource
{
    public static $wrap = 'order_note';

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'=>$this->id,
            'order_id'=>$this->order_id,
            'order'=> new OrderResource($this->whenLoaded('order')),
            'user_id'=>$this->user_id,
            'note'=>$this->note,
            'created_at'=>$this->created_at,
            'updated_at'=>$this->updated_at,
        ];
    }

}

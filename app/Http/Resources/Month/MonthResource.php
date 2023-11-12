<?php

namespace App\Http\Resources\Month;

use Illuminate\Http\Resources\Json\JsonResource;

class MonthResource extends JsonResource
{
    public static $wrap = 'month';

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
            'month_number'=> $this->month_number,
            'month_name'=>$this->month_name
        ];
    }

}

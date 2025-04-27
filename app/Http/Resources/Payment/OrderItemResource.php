<?php

namespace App\Http\Resources\Payment;

use App\Http\Resources\Subscription\PackageResource;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public static $wrap = 'item';

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
            // 'order'=>OrderResource::collection($this->order),
            'quantity'=> $this->quantity,
            'bulkProduct'=> $this->bulkProduct,
            'package'=> $this->package->with('subscription'),
            'created_at'=>$this->created_at,
            // 'package1'=>PackageResource::collection($this->order),

        ];
    }

}

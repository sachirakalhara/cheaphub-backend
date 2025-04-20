<?php

namespace App\Http\Resources\Payment;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public static $wrap = 'order';

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
            'amount'=>$this->amount,
            'currency'=> $this->currency,
            'description'=>$this->description,
            'payment_status'=>$this->payment_status,
            'transaction_id'=>$this->transaction_id,
            'user_id'=>$this->user,
            'amount_paid'=>$this->amount_paid,
            'is_wallet'=>$this->is_wallet,
            'order_items' => OrderItemResource::collection($this->orderItems),
            'order_id'=>$this->order_id,
            'created_at'=>$this->created_at
            
        ];
    }

}

<?php

namespace App\Http\Resources\Subscription;

use App\Models\Subscription\Subscription;
use Illuminate\Http\Resources\Json\JsonResource;

class PackageResource extends JsonResource
{
    public static $wrap = 'package';

    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
  
        return [
            'id' => $this->id,
            'name' => $this->name,
            'price' => $this->price,
            'qty' => $this->qty,
            'expiry_duration' => $this->expiry_duration,
            'payment_method' => $this->payment_method,
            'subscription' => Subscription::find($this->subscription_id)
        ];
    }

}

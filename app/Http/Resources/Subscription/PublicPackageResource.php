<?php

namespace App\Http\Resources\Subscription;

use App\Models\Subscription\Subscription;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicPackageResource extends JsonResource
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
        $subscription = Subscription::find($this->subscription_id);
        unset($subscription->available_serial_count);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'price' => $this->price,
            'replace_count' => $this->replace_count,
            'expiry_duration' => $this->expiry_duration,
            'payment_method' => $this->payment_method,
            'subscription' => $subscription
        ];
    }

}

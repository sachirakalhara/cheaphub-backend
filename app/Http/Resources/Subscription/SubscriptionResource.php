<?php

namespace App\Http\Resources\Subscription;

use App\Models\Subscription\Region;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionResource extends JsonResource
{
    public static $wrap = 'subscription';

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
            'serial' => $this->serial,
            'available_serial_count' => $this->available_serial_count,
            'gateway_fee' => $this->gateway_fee,
            'packages' => PackageResource::collection($this->packages),

        ];
    }

}

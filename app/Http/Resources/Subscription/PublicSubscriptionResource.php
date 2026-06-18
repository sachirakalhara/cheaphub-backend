<?php

namespace App\Http\Resources\Subscription;

use Illuminate\Http\Resources\Json\JsonResource;

class PublicSubscriptionResource extends JsonResource
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
            // 'serial' => $this->serial,
            'available_serial_count' => $this->available_serial_count,
            'delivery_type' => $this->delivery_type ?? 'serial_based',
            'service_qty' => $this->service_qty ?? 0,
            'service_info' => $this->service_info,
            'gateway_fee' => $this->gateway_fee,
            'packages' => PublicPackageResource::collection($this->packages),

        ];
    }

}

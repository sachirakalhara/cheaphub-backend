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
            'type' => $this->type,
            'month' => $this->month,
            'region' => Region::find($this->region_id),
            'product' => $this->product
        ];
    }

}

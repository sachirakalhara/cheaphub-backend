<?php

namespace App\Http\Resources\Subscription;

use App\Http\Resources\Serial\SerialResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class SubscriptionCollection extends ResourceCollection
{
    public static $wrap = 'subscription_list';

    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'subscription_list'=> SubscriptionResource::collection($this->collection)
        ];
    }

}

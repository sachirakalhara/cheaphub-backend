<?php

namespace App\Http\Resources\Subscription;

use App\Http\Resources\Product\Contribution\ContributionProductResource;
use App\Models\Product\Contribution\ContributionProduct;
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
            'replace_count' => $this->replace_count,
            'expiry_duration' => $this->expiry_duration,
            'payment_method' => $this->payment_method,
            'contributionProduct' => new ContributionProductResource($this->subscription->contributionProduct),
        ];
    }

}

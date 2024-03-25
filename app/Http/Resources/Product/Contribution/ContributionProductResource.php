<?php

namespace App\Http\Resources\Product\Contribution;

use App\Http\Resources\Category\CategoryResource;
use App\Http\Resources\Serial\SerialResource;
use App\Http\Resources\Subscription\SubscriptionResource;
use App\Models\Serial\Serial;
use App\Models\Subscription\Subscription;
use Illuminate\Http\Resources\Json\JsonResource;

class ContributionProductResource extends JsonResource
{
    public static $wrap = 'contribution_product';

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
            'tag_id' => $this->tag_id,
            'description' => $this->description,
            'categories' => CategoryResource::collection($this->categories),
            'image' => $this->image ? asset('uploads/' . $this->image) : null,
            'visibility' => $this->visibility,
            'slug_url' => route('api.slug-contribution-product.slug', [ 'slug_name' => $this->slug_url]),
            'service_info' => $this->service_info,
            'subscriptions' => SubscriptionResource::collection($this->subscriptions),

        ];
    }

}

<?php

namespace App\Http\Resources\Product\Contribution;

use App\Http\Resources\Category\CategoryResource;
use App\Http\Resources\Subscription\PublicSubscriptionResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicContributionProductResource extends JsonResource
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
        $disk = Storage::disk('s3');
        $image = $this->image ? $disk->url($this->image) : null;
        return [
            'id' => $this->id,
            'name' => $this->name,
            'tag_id' => $this->tag_id,
            'description' => $this->description,
            'categories' => CategoryResource::collection($this->categories),
            'image' => $image,
            'visibility' => $this->visibility,
            'service_info' => $this->service_info,
            'subscriptions' => PublicSubscriptionResource::collection($this->subscriptions),

        ];
    }

}

<?php

namespace App\Http\Resources\Product\Contribution;

use App\Helpers\Helper;
use App\Http\Resources\Category\CategoryResource;
use App\Http\Resources\Serial\SerialResource;
use App\Http\Resources\Subscription\PublicSubscriptionResource;
use App\Http\Resources\Subscription\SubscriptionResource;
use App\Models\Serial\Serial;
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
            'service_info' => str_replace("\n", "<br>", $this->service_info),
            'subscriptions' => PublicSubscriptionResource::collection($this->subscriptions),
            'reviews' => $this->review(),

        ];
    }

     public function review()
    {
        $final_review = [];
        foreach ($this->reviews as $review) {
            $review =
                [
                    'id' => $review->id,
                    'review' => $review->review,
                    'rating_avg' => Helper::getCalculateAverageRating($review->product_type,$review->product_id),
                    'rating_count' => $review->rating,
                    'user_id' => $review->user_id,
                    'user_name' => $review->user->display_name,
                    "created_at" => $review->created_at,
                    "updated_at" => $review->updated_at

                ];
            $final_review[] = $review;
        }
        return $final_review;
    }

}

<?php

namespace App\Http\Resources\Product\Bulk;

use App\Helpers\Helper;
use App\Http\Resources\Category\CategoryResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class BulkProductResource extends JsonResource
{
    public static $wrap = 'bulk_product';

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
        $rating_avg = Helper::getCalculateAverageRating('bulk', $this->id) ?? 0;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'tag_id' => $this->tag_id,
            'description' => $this->description,
            'price' => $this->price,
            'gateway_fee' => $this->gateway_fee,
            'categories' => CategoryResource::collection($this->categories),
            'image' => $image,
            'visibility' => $this->visibility,
            'url' => !empty(Auth::user()->id) ? "cheaphub.io/bulk/{$this->id}/{$this->name}" : null,
            'service_info' => $this->service_info,
            'minimum_quantity' => $this->minimum_quantity,
            'maximum_quantity' => $this->maximum_quantity,
            'serial' => $this->serial,
            'available_serial_count' => $this->serial_count,
            'payment_method' => $this->payment_method,
            'bulk_type' => $this->bulk_type,
            'rating_avg' =>  $rating_avg,
            'reviews' => $this->review(),
        ];
    }

    public function review()
    {
        $final_review = [];
        foreach ($this->reviews as $review) {
            if($review->product_type == 'bulk'){
                $review =
                    [
                        'id' => $review->id,
                        'review' => $review->review,
                        'rating_count' => $review->rating,
                        'user_id' => $review->user_id,
                        'user_name' => $review->user->display_name,
                        "created_at" => $review->created_at,
                        "updated_at" => $review->updated_at

                    ];
                $final_review[] = $review;
            }
        }
        return $final_review;
    }

}

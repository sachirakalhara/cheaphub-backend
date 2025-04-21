<?php

namespace App\Http\Resources\Product\Contribution;

use Illuminate\Http\Resources\Json\ResourceCollection;

class PublicContributionProductCollection extends ResourceCollection
{
    public static $wrap = 'contribution_product_list';

    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'contribution_product_list'=> PublicContributionProductResource::collection($this->collection)
        ];
    }


}

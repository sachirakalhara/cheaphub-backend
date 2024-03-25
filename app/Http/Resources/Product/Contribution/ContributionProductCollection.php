<?php

namespace App\Http\Resources\Product\Contribution;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ContributionProductCollection extends ResourceCollection
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
            'contribution_product_list'=> ContributionProductResource::collection($this->collection)
        ];
    }


}

<?php

namespace App\Http\Resources\Tag;

use App\Models\Product\Product;
use Illuminate\Http\Resources\Json\JsonResource;

class TagResource extends JsonResource
{
    public static $wrap = 'tag';

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'=>$this->id,
            'name'=> $this->name,
            'description'=>$this->description,
            'count' => Product::whereHas('tag', function ($query) {
                $query->where('id', $this->id);
            })->count()

        ];
    }

}

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
        // $products = Product::where('tag_id',$this->id)->get();
        // Product::whereHas('tag', function ($query) {
        //         $query->where('id', $this->id);
        //     })->count();
        return [
            'id'=>$this->id,
            'name'=> $this->name,
            'description'=>$this->description,
            // 'count' => count($products),

            'count' => Product::whereHas('tag', function ($query) {
                $query->where('id', $this->id);
            })->count()

        ];
    }

}

<?php

namespace App\Http\Resources\Category;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class CategoryResource extends JsonResource
{
    public static $wrap = 'category';

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $disk = Storage::disk('s3');
        $image = $this->image ? $disk->url($this->image) : null;

        return [
            'id'=>$this->id,
            'name'=> $this->name,
            'description'=>$this->description,
            'image'=>$image

        ];
    }

}

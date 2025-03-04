<?php

namespace App\Http\Resources\User;

use App\Helpers\Helper;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class UserResource extends JsonResource
{
    public static $wrap = 'user';

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $disk = Storage::disk('s3');
        $profile_photo = $this->profile_photo ? $disk->url($this->profile_photo) : null;

        return [
            'id'=>$this->id,
            'display_name'=> $this->display_name,
            'fname'=>$this->fname,
            'lname'=>$this->lname,
            'contact_no'=>$this->contact_no,
            'email'=> $this->email,
            'contact_number'=> $this->contact_no,
            'profile_photo' => $profile_photo,
            'user_level'=>$this->userLevel->scope

        ];
    }
}

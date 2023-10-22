<?php

namespace App\Http\Resources\User;

use App\Helpers\Helper;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthUserResource extends JsonResource
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
        return [
            'id'=>$this->id,
            'first_name'=>$this->fname,
            'last_name'=>$this->lname,
            'email'=>$this->email,
            'email_verified_at'=>$this->email_verified_at,
            'profile_photo'=>Helper::singleImageResponse($this->profile_photo),
            'active'=>$this->active,
            'contact_number'=> $this->contact_no,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'user_level'=>$this->userLevel->scope

        ];
    }
    public function profilePicCoverUrl(){

        if($this->cover_image) {
            return asset('storage').$this->cover_image;
        } else {
            return '';
        }
    }

    public function profilePicUrl(){

        if($this->profile_photo) {
            return asset('storage').$this->profile_photo;
        } else {
            return $this->social_pic_url;
        }
    }
}

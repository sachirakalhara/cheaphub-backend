<?php

namespace App\Repositories\User;

use App\Helpers\Helper;
use App\Http\Resources\User\UserCollection;
use App\Http\Resources\User\UserResource;
use App\Models\User\User;
use App\Repositories\User\Interface\UserRepositoryInterface;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class UserRepository implements UserRepositoryInterface
{

    public function all($request)
    {

        if($request->input('all', '') == 1) {
            $user_list = User::all();
        } else {
            $user_list = User::orderBy('created_at', 'desc')->paginate(10);
        }

        if (count($user_list) > 0) {
            return new UserCollection($user_list);
        } else {
            return Helper::success(Response::$statusTexts[Response::HTTP_NO_CONTENT], Response::HTTP_NO_CONTENT);
        }
    }

    public function update($request)
    {
        $user = User::find($request->id);
        $user->fname = $request->fname;
        $user->lname = $request->lname;
        $user->display_name = $request->fname.' '.$request->lname;
        $user->contact_no = $request->contact_no;

        if ($request->file('profile_photo')) {
            $disk = Storage::disk('s3');
            if ($user->profile_photo && $disk->exists($user->profile_photo)) {
                $disk->delete($user->profile_photo);
            }

            $image = $request->file('profile_photo');
            $filename = 'user/profile/' . uniqid() . '.' . $image->getClientOriginalExtension();
            $disk->put($filename, file_get_contents($image));
            $user->profile_photo = $filename;
        }

        if ($user->save()) {
            activity('user')->causedBy($user)->performedOn($user)->log('updated');
            return new UserResource($user);
        } else {
            return Helper::error(Response::$statusTexts[Response::HTTP_NO_CONTENT], Response::HTTP_NO_CONTENT);
        }
    }


}

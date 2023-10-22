<?php

namespace App\Helpers;

use App\Models\User\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use DateTime;
use Illuminate\Support\Facades\Storage;

class Helper
{
    public static function error($message, $code)
    {
        return Response::json([
            'status' => 'error',
            'success' => false,
            'code' => $code,
            'message' => $message], $code);
    }

    public static function success($message, $code)
    {
        return Response::json([
            'status' => 'success',
            'success' => true,
            'code' => $code,
            'message' => $message], $code);
    }

    public static function imageResponse($request)
    {
        $url = [];
        foreach ($request['data'] as $data) {
            if (Storage::disk('s3')->exists($data->path)) {
                $value = [
                    'id' => $data->id,
                    'path' => Storage::disk('s3')->url($data->path)
                ];
                array_push($url , $value);
            }
        }
        return [
            'url' => $url,
        ];
    }
    public static function singleImageResponse($request)
    {
        $url = null;
        if ($request != null && Storage::disk('s3')->exists($request)) {
            $url = Storage::disk('s3')->url($request);
            return $url;
        }else{
            return $url;
        }

    }

    public static function isSuperAdmin()
    {
        return Auth::user()->userLevel->scope == 'super_admin' ? true : null;
    }

    public static function superAdmin()
    {
        return User::where('user_level_id',1)->first();
    }

    public static function disponibility($disponibility)
    {
        switch (strtolower($disponibility)) {
            case 'morning':
                return ["from" => '00:00:00', "to" => '11:59:00'];
                break;

            case 'afternoon':
                return ["from" => '12:00:00', "to" => '16:59:00'];
                break;

            case 'evening':
                return ["from" => '17:00:00', "to" => '19:59:00'];
                break;

            case 'night':
                return ["from" => '20:00:00', "to" => '23:59:00'];
                break;

            default:
                break;
        }
    }

    public static function paginate($items, $perPage = 10, $page = null, $options = [])
    {
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }

}

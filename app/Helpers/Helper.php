<?php

namespace App\Helpers;

use App\Models\User\User;
use App\Models\Review\Review;
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

    public static function imageResponse($image)
    {
//        $path = public_path('uploads/' . $image);
//        if (file_exists($path)) {
//            return response()->file($path);
//        }else{
//            return null;
//        }
    }


    public static function isSuperAdmin()
    {
        return Auth::user()->userLevel->scope == 'super_admin' ? true : null;
    }

    public static function superAdmin()
    {
        return User::where('user_level_id',1)->first();
    }

    public static function paginate($items, $perPage = 10, $page = null, $options = [])
    {
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }



     public static function getCalculateAverageRating($productType, $productId)
    {
        $sumOfRatings = Review::where('product_type', $productType)
            ->where('product_id', $productId)
            ->selectRaw('SUM(rating) as total_rating')
            ->get()->first();
        $reviewCount = Review::where('product_type', $productType)
            ->where('product_id', $productId)
            ->count();

        if($sumOfRatings->total_rating > 0){
            return ($sumOfRatings->total_rating / $reviewCount);
        }else{
            return 0;
        }
    }

}

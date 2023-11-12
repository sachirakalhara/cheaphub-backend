<?php

namespace App\Repositories\Subscription;

use App\Helpers\Helper;
use App\Http\Resources\Month\MonthCollection;
use App\Models\Subscription\Month;
use App\Repositories\Subscription\Interface\MonthRepositoryInterface;
use Illuminate\Http\Response;

class MonthRepository implements MonthRepositoryInterface
{
    public function all($request)
    {

        if($request->input('all', '') == 1) {
            $month_list = Month::all();
        } else {
            $month_list = Month::orderBy('created_at', 'desc')->paginate(10);
        }

        if (count($month_list) > 0) {
            return new MonthCollection($month_list);
        } else {
            return Helper::success(Response::$statusTexts[Response::HTTP_NO_CONTENT], Response::HTTP_NO_CONTENT);
        }
    }

}

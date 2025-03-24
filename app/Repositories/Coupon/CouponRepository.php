<?php

namespace App\Repositories\Coupon;

use App\Helpers\Helper;
use App\Http\Resources\Coupon\CouponCollection;
use App\Http\Resources\Coupon\CouponResource;
use App\Models\Coupon\Coupon;
use App\Repositories\Coupon\Interface\CouponRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Http\Response;

class CouponRepository implements CouponRepositoryInterface
{
    public function all($request)
    {

        $query = Coupon::query();

        if ($request->filled('coupon_code')) {
            $query->where('coupon_code', 'like', '%' . $request->coupon_code . '%');
        }

        if ($request->filled('product_type')) {
            $query->where('product_type', 'like', '%' . $request->product_type . '%');
        }

        if($request->input('all', '') == 1) {
            $coupon_list = $query->get();
        } else {
            $coupon_list = $query->orderBy('created_at', 'desc')->paginate(10);
        }

        if (count($coupon_list) > 0) {
            return new CouponCollection($coupon_list);
        } else {
            return Helper::success(Response::$statusTexts[Response::HTTP_NO_CONTENT], Response::HTTP_NO_CONTENT);
        }
    }

    public function store($request)
    {
        $formattedDate = Carbon::createFromFormat('d-m-Y', $request->expiry_date)->format('Y-m-d');

        $coupon = new Coupon();
        $coupon->product_type = $request->product_type;
        $coupon->discount_percentage = $request->discount_percentage;
        $coupon->max_discount_amount = $request->max_discount_amount;
        $coupon->expiry_date = $formattedDate;
        $coupon->coupon_code = $request->coupon_code;
        if ($coupon->save()) {
            activity('coupon')->causedBy($coupon)->performedOn($coupon)->log('created');
            return new CouponResource($coupon);
        } else {
            return Helper::error(Response::$statusTexts[Response::HTTP_NO_CONTENT], Response::HTTP_NO_CONTENT);
        }
    }
    
    public function update($request)
    {
        $formattedDate = Carbon::createFromFormat('d-m-Y', $request->expiry_date)->format('Y-m-d');
        $coupon = Coupon::find($request->id);
        $coupon->product_type = $request->product_type;
        $coupon->discount_percentage = $request->discount_percentage;
        $coupon->max_discount_amount = $request->max_discount_amount;
        $coupon->expiry_date = $formattedDate;
        if ($coupon->save()) {
            activity('coupon')->causedBy($coupon)->performedOn($coupon)->log('updated');
            return new CouponResource($coupon);
        } else {
            return Helper::error(Response::$statusTexts[Response::HTTP_NO_CONTENT], Response::HTTP_NO_CONTENT);
        }
    }

    public function delete($id)
    {
        $coupon = Coupon::find($id);

        if (!$coupon) {
            return Helper::error('Coupon not found', Response::HTTP_NOT_FOUND);
        }

        if ($coupon->delete()) {
            activity('coupon')->causedBy($coupon)->performedOn($coupon)->log('deleted');
            return Helper::success(Response::$statusTexts[Response::HTTP_OK], Response::HTTP_OK);
        } else {
            return Helper::error('Failed to delete coupon', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


}

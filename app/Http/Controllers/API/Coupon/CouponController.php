<?php

namespace App\Http\Controllers\API\Coupon;

use App\Http\Controllers\Controller;
use App\Repositories\Coupon\Interface\CouponRepositoryInterface;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    private $couponRepository;

    public function __construct(CouponRepositoryInterface $couponRepository)
    {
        $this->couponRepository = $couponRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return $this->couponRepository->all($request);
    }


    public function store(Request $request)
    {
        $request->validate([
            'product_type' => 'required',
            'discount_percentage' => 'required',
            'max_discount_amount' => 'required',
            'expiry_date' => 'required',
            'coupon_code' => 'required|string|unique:coupons'
        ]);

        return $this->couponRepository->store($request);
    }

    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required',
            'product_type' => 'required',
            'discount_percentage' => 'required',
            'max_discount_amount' => 'required',
            'expiry_date' => 'required',

        ]);

        return $this->couponRepository->update($request);
    }

    public function delete($id)
    {
        return $this->couponRepository->delete($id);
    }

    public function checkCoupon(Request $request)
    {
        return $this->couponRepository->checkCoupon($request);
    }
}

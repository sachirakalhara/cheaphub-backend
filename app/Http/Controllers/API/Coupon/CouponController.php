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

    public function index(Request $request)
    {
        return $this->couponRepository->all($request);
    }

    public function store(Request $request)
    {
        $rules = [
            'product_type' => 'required',
            'discount_percentage' => 'required',
            'max_discount_amount' => 'required',
            'expiry_date' => 'required',
            'coupon_code' => 'required|string|unique:coupons',
            'scheduled_start' => 'nullable|date',
            'scheduled_end' => 'nullable|date|after:scheduled_start',
            'campaign_email_enabled' => 'nullable|boolean',
            'campaign_audience' => 'required_if:campaign_email_enabled,true|in:all_customers,purchased_customers',
            'campaign_subject' => 'required_if:campaign_email_enabled,true|nullable|string|max:255',
        ];

        $request->validate($rules);

        return $this->couponRepository->store($request);
    }

    public function update(Request $request)
    {
        $rules = [
            'id' => 'required',
            'product_type' => 'required',
            'discount_percentage' => 'required',
            'max_discount_amount' => 'required',
            'expiry_date' => 'required',
            'scheduled_start' => 'nullable|date',
            'scheduled_end' => 'nullable|date|after:scheduled_start',
            'campaign_email_enabled' => 'nullable|boolean',
            'campaign_audience' => 'required_if:campaign_email_enabled,true|in:all_customers,purchased_customers',
            'campaign_subject' => 'required_if:campaign_email_enabled,true|nullable|string|max:255',
        ];

        $request->validate($rules);

        return $this->couponRepository->update($request);
    }

    public function delete($id)
    {
        return $this->couponRepository->delete($id);
    }
}

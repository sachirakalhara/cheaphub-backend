<?php

namespace App\Http\Resources\Coupon;

use Illuminate\Http\Resources\Json\JsonResource;

class CouponResource extends JsonResource
{
    public static $wrap = 'coupon';

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'product_type' => $this->product_type,
            'discount_percentage' => $this->discount_percentage,
            'max_discount_amount' => $this->max_discount_amount,
            'expiry_date' => $this->expiry_date,
            'coupon_code' => $this->coupon_code,
            'is_active' => $this->is_active,
            'scheduled_start' => $this->scheduled_start ? $this->scheduled_start->format('Y-m-d H:i') : null,
            'scheduled_end' => $this->scheduled_end ? $this->scheduled_end->format('Y-m-d H:i') : null,
            'campaign_email_enabled' => $this->campaign_email_enabled,
            'campaign_audience' => $this->campaign_audience,
            'campaign_inactive_days' => $this->campaign_inactive_days,
            'campaign_subject' => $this->campaign_subject,
            'campaign_activation_sent' => $this->campaign_activation_sent,
            'campaign_reminder_sent' => $this->campaign_reminder_sent,
        ];
    }

}

<?php

namespace App\Models\Coupon;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'product_type',
        'discount_percentage',
        'max_discount_amount',
        'expiry_date',
        'coupon_code',
        'is_active',
        'scheduled_start',
        'scheduled_end',
        'campaign_email_enabled',
        'campaign_audience',
        'campaign_inactive_days',
        'campaign_subject',
        'campaign_activation_sent',
        'campaign_reminder_sent',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'scheduled_start' => 'datetime',
        'scheduled_end' => 'datetime',
        'is_active' => 'boolean',
        'campaign_email_enabled' => 'boolean',
        'campaign_inactive_days' => 'integer',
        'campaign_activation_sent' => 'boolean',
        'campaign_reminder_sent' => 'boolean',
    ];

    public function getExpiryDateAttribute($value)
    {
        return Carbon::parse($value)->format('d-m-Y');
    }

    /**
     * Whether the coupon is past its expiry date.
     *
     * Uses the raw DB value on purpose: the accessor above returns a d-m-Y
     * display string, so comparing $coupon->expiry_date against a date does a
     * byte-wise string comparison and gets the wrong answer. Valid through the
     * whole of the expiry day, matching app:delete-expired-coupons, which only
     * removes a coupon once that day has passed.
     */
    public function isExpired(): bool
    {
        $raw = $this->getRawOriginal('expiry_date');

        if (empty($raw)) {
            return false;
        }

        return Carbon::parse($raw)->endOfDay()->isPast();
    }

}

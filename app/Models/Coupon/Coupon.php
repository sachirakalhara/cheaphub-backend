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
        'campaign_activation_sent' => 'boolean',
        'campaign_reminder_sent' => 'boolean',
    ];

    public function getExpiryDateAttribute($value)
    {
        return Carbon::parse($value)->format('d-m-Y');
    }

}

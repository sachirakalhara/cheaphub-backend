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
        'product_type',//bulk
        'discount_percentage',
        'max_discount_amount',
        'expiry_date',
        'coupon_code'
    ];

    protected $casts = [
        'expiry_date' => 'date',
    ];

    // Accessor to format the date when retrieved
    public function getExpiryDateAttribute($value)
    {
        return Carbon::parse($value)->format('d-m-Y');  // e.g., "25-03-2025"
    }

}

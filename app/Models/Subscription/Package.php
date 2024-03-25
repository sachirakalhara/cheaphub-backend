<?php

namespace App\Models\Subscription;

use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    protected $fillable = [
        'id',
        'subscription_id',
        'name',
        'price',
        'payment_method',
        'qty',
        'expiry_duration'
    ];

    public function subscription()
    {
        return $this->belongsTo('App\Models\Subscription\Subscription');
    }
}

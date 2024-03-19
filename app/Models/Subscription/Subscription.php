<?php

namespace App\Models\Subscription;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $fillable = [
        'id',
        'type',
        'month_id',
        'product_id',
        'region_id'
    ];

    public function month()
    {
        return $this->belongsTo('App\Models\Subscription\Month');
    }
    public function region()
    {
        return $this->belongsTo('App\Models\Subscription\Region');
    }
    public function product()
    {
        return $this->belongsTo('App\Models\Product\Product');
    }
}

<?php

namespace App\Models\Subscription;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $fillable = [
        'id',
        'name',
        'contribution_product_id',
        'serial',
        'available_serial_count',
        'delivery_type',
        'service_qty',
        'service_info',
        'gateway_fee',
        'is_manually_out_of_stock'
    ];



    public function contributionProduct()
    {
        return $this->belongsTo('App\Models\Product\Contribution\ContributionProduct');
    }

    public function packages()
    {
        return $this->hasMany('App\Models\Subscription\Package');
    }

}

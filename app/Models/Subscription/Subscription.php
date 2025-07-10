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
        'gateway_fee',
        'service_type'
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

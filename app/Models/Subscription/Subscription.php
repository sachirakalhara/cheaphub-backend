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
        'refresh_count'
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

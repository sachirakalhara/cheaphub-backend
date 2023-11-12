<?php

namespace App\Models\Subscription;

use Illuminate\Database\Eloquent\Model;

class RegionMonth extends Model
{
    protected $fillable = [
        'id',
        'month_id',
        'region_id'
    ];
}

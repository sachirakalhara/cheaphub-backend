<?php

namespace App\Models\Product\Contribution;

use Illuminate\Database\Eloquent\Model;

class ProductReplacement extends Model
{
    protected $fillable = [
        'id',
        'user_id',
        'order_id',
        'package_id',
        'available_replace_count'
    ];

}

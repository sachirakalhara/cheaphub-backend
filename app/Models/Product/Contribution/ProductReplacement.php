<?php

namespace App\Models\Product\Contribution;

use Illuminate\Database\Eloquent\Model;

class ProductReplacement extends Model
{
    protected $fillable = [
        'id',
        'user_id',
        'package_id',
        'avalable_replace_count'
    ];

}

<?php

namespace App\Models\Product\Contribution;

use Illuminate\Database\Eloquent\Model;

class ContributionProductCategory extends Model
{
    protected $fillable = [
        'id',
        'product_id',
        'category_id'
    ];
}

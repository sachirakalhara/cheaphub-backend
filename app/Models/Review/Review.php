<?php

namespace App\Models\Review;

use Illuminate\Database\Eloquent\Model;


class Review extends Model
{
    protected $fillable = [
        'id',
        'user_id',
        'review',
        'rating',
        'product_type',
        'product_id',
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User\User');
    }

    public function bulkProduct()
    {
        return $this->belongsTo('App\Models\Product\Bulk\BulkProduct');
    }

    public function contributionProduct()
    {
        return $this->belongsTo('App\Models\Product\Contribution\ContributionProduct');
    }

}

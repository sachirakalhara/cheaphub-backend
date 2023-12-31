<?php

namespace App\Models\Product;

use App\Models\Category\Category;
use App\Models\Serial\Serial;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    protected $fillable = [
        'id',
        'subscription_id',
        'name',
        'description',
        'price',
        'gateway_fee',
        'image'
    ];

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'product_categories');
    }
    public function serials()
    {
        return $this->hasMany('App\Models\Serial\Serial');
    }
}

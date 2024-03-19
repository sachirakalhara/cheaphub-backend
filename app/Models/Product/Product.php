<?php

namespace App\Models\Product;

use App\Models\Category\Category;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    protected $fillable = [
        'id',
        'tag_id',
        'name',
        'description',
        'price',
        'gateway_fee',
        'image'
    ];

    public function tag()
    {
        return $this->belongsTo('App\Models\Tag\Tag');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'product_categories');
    }
    public function serials()
    {
        return $this->hasMany('App\Models\Serial\Serial');
    }
}

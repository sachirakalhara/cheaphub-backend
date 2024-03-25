<?php

namespace App\Models\Product\Contribution;

use App\Models\Category\Category;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ContributionProduct extends Model
{
    protected $fillable = [
        'id',
        'tag_id',
        'name',
        'description',
        'image',
        'service_info',
        'slug_url',
        'visibility'
    ];

    public function subscriptions()
    {
        return $this->hasMany('App\Models\Subscription\Subscription');
    }
    public function tag()
    {
        return $this->belongsTo('App\Models\Tag\Tag');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'contribution_product_categories');
    }
}

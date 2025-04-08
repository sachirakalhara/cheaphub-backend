<?php

namespace App\Models\Category;

use App\Models\Product\Bulk\BulkProduct;
use App\Models\Product\Contribution\ContributionProduct;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model
{
    protected $fillable = [
        'id',
        'name',
        'description',
        'image'
    ];

    public function bulkProducts(): BelongsToMany
    {
        return $this->belongsToMany(BulkProduct::class, 'bulk_product_categories');
    }

    public function contributionProducts(): BelongsToMany
    {
        return $this->belongsToMany(ContributionProduct::class, 'contribution_product_categories');
    }
}

<?php

namespace App\Models\Category;

use App\Models\Product\Bulk\BulkProduct;
use App\Models\Product\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model
{
    protected $fillable = [
        'id',
        'name',
        'description'
    ];

    public function bulkProducts(): BelongsToMany
    {
        return $this->belongsToMany(BulkProduct::class, 'bulk_product_categories');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_categories');
    }
}

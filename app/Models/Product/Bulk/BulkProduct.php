<?php

namespace App\Models\Product\Bulk;

use App\Models\Category\Category;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class BulkProduct extends Model
{
    protected $fillable = [
        'id',
        'tag_id',
        'name',
        'description',
        'price',
        'gateway_fee',
        'image',
        'payment_method',
        'serial',
        'serial_count',
        'minimum_quantity',
        'maximum_quantity',
        'service_info',
        'visibility',
        'bulk_type'
    ];
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'bulk_product_categories');
    }
}

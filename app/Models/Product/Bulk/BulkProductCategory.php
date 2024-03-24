<?php

namespace App\Models\Product\Bulk;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BulkProductCategory extends Model
{
    protected $fillable = [
        'id',
        'bulk_product_id',
        'category_id'
    ];
}

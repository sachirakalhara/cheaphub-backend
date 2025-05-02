<?php

namespace App\Models\Product\Contribution;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductReplacementSerial extends Model
{
    protected $fillable = [
        'id',
        'product_replacement_id',
        'sirial'
    ];
}

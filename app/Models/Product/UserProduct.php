<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Model;

class UserProduct extends Model
{
    protected $fillable = [
        'id',
        'product_id',
        'user_id'
    ];
}

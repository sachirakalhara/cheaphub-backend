<?php

namespace App\Models\Serial;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Serial extends Model
{
    protected $fillable = [
        'id',
        'product_id',
        'name',
        'type',
        'usage',
        'min_count',
        'max_count'
    ];
}

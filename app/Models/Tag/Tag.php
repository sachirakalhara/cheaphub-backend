<?php

namespace App\Models\Tag;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $fillable = [
        'id',
        'name',
        'description'
    ];
    
    public function products()
    {
        return $this->hasMany('App\Models\Product\Product');
    }
}

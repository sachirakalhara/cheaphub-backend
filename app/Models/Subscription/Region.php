<?php

namespace App\Models\Subscription;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Region extends Model
{
    protected $fillable = [
        'id',
        'region_name'
    ];

    public function months(): BelongsToMany
    {
        return $this->belongsToMany(Month::class, 'region_months');
    }
}

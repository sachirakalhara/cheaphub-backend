<?php

namespace App\Models\Subscription;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Month extends Model
{
    protected $fillable = [
        'id',
        'month_number',
        'month_name'
    ];
    public function regions(): BelongsToMany
    {
        return $this->belongsToMany(Region::class, 'region_months');
    }
}

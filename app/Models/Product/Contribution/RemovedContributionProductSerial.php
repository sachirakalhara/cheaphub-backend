<?php

namespace App\Models\Product\Contribution;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RemovedContributionProductSerial extends Model
{
 protected $fillable = [
        'id',
        'package_id',
        'serial',
        'order_item_id',
        
    ];
}

<?php

namespace App\Models\Product\Bulk;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RemovedBulkProductSerial extends Model
{

     protected $fillable = [
        'id',
        'bulk_product_id',
        'serial',
        'order_item_id',
        
    ];
   
}

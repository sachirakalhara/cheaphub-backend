<?php

namespace App\Models\Product\Contribution;

use App\Models\Payment\OrderItems;
use Illuminate\Database\Eloquent\Model;

class RemovedContributionProductSerial extends Model
{
 protected $fillable = [
        'id',
        'package_id',
        'serial',
        'order_item_id',
    ];

    public function orderItem()
    {
        return $this->belongsTo(OrderItems::class, 'order_item_id');
    }

    public function removedProductReplacementSerials()
    {
        return $this->hasMany(RemovedProductReplacementSerial::class, 'removed_contribution_product_serial_id');
    }
    
}

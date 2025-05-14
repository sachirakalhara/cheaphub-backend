<?php

namespace App\Models\Product\Contribution;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RemovedProductReplacementSerial extends Model
{
    protected $fillable = [
        'id',
        'removed_contribution_product_serial_id',
        'product_replacement_serial_id'
    ];

    public function product_replacement_serial()
    {
        return $this->belongsTo(ProductReplacementSerial::class, 'product_replacement_serial_id');
    }
}

<?php

namespace App\Models\Payment;

use App\Models\Payment\Order;
use App\Models\Product\Bulk\BulkProduct;
use App\Models\Product\Contribution\ContributionProduct;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItems extends Model
{
    use HasFactory;

    protected $fillable = ['order_id','bulk_product_id','contribution_product_id', 'quantity'];

    public function bulkProduct()
    {
        return $this->belongsTo(BulkProduct::class);
    }

    public function contributionProduct()
    {
        return $this->belongsTo(ContributionProduct::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}

<?php

namespace App\Models\Payment;

use App\Models\Payment\Order;
use App\Models\Product\Bulk\BulkProduct;
use App\Models\Product\Contribution\ContributionProduct;
use App\Models\Subscription\Package;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItems extends Model
{
    use HasFactory;

    protected $fillable = ['order_id','bulk_product_id','package_id', 'quantity'];

    public function bulkProduct()
    {
        return $this->belongsTo(BulkProduct::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}

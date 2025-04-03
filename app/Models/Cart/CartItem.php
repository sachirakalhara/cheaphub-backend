<?php

namespace App\Models\Cart;

use App\Models\Product\Bulk\BulkProduct;
use App\Models\Subscription\Package;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = ['cart_id', 'bulk_product_id','package_id', 'quantity'];

    public function bulkProduct()
    {
        return $this->belongsTo(BulkProduct::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }
                
}

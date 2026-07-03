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

    // Bump the parent cart's updated_at whenever items change, so
    // "cart idle since X" (abandoned-cart detection) reflects real activity.
    protected $touches = ['cart'];

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

<?php

namespace App\Models\Cart;

use App\Models\Product\Bulk\BulkProduct;
use App\Models\Subscription\Package;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'bulk_product_id','package_id', 'quantity'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bulkProduct()
    {
        return $this->belongsTo(BulkProduct::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }
}

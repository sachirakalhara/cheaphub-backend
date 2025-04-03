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

    protected $fillable = ['user_id','coupon_code'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cartItems()
    {
        return $this->hasOne(CartItem::class);
    }
}

<?php

namespace App\Models\Payment;

use App\Models\Payment\Order;
use App\Models\Payment\OrderItems;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItemDelivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'order_item_id',
        'delivery_content',
        'delivered_by',
        'delivered_at',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function orderItem()
    {
        return $this->belongsTo(OrderItems::class, 'order_item_id');
    }

    public function deliveredByUser()
    {
        return $this->belongsTo(User::class, 'delivered_by');
    }
}

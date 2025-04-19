<?php

namespace App\Models\Payment;

use App\Models\Payment\OrderItems;
use App\Models\Ticket\Ticket;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'id',
        'amount',
        'currency',
        'description',
        'payment_status',
        'transaction_id',
        'user_id',
        'amount_paid',
        'is_wallet',
        'order_id',
        'discount'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orderItems()
    {
        return $this->hasOne(OrderItems::class);
    }
    
    public function items()
    {
        return $this->hasMany(OrderItems::class, 'order_id');
    }
    
    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }
    
}

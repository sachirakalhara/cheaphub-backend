<?php

namespace App\Models\Payment;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'amount',
        'currency',
        'description',
        'payment_status',
        'transaction_id',
        'amount_paid',
    ];
}

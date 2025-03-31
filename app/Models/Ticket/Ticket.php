<?php

namespace App\Models\Ticket;

use App\Models\Payment\Order;
use App\Models\User\User;
use Generator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;
    
    protected $fillable = ['id','ticket_number', 'order_id', 'customer_id', 'subject', 'description', 'status'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
    
    public static function generateTicketNumber(): string
    {
        return 'TICKET-' . strtoupper(uniqid());
    }
}
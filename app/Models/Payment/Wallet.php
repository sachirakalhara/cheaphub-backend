<?php

namespace App\Models\Payment;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'balance',
        'currency',
    ];

    /**
     * Get the user that owns the virtual wallet.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

class PasswordReset extends Model
{
    protected $table = 'password_reset_tokens'; 
    protected $fillable = [
        'email',
        'token'
    ];
}

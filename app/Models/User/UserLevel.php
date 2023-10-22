<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserLevel extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'scope'
    ];
    protected $dates = ['deleted_at'];

    public function user()
    {
        return $this->hasMany('App\Models\User\User');
    }
}

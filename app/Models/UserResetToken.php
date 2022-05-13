<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserResetToken extends Model
{
    use HasFactory;

    protected $table = 'user_reset_token';

    protected $fillable = [
        'userUid',
        'userToken',
        'active',
    ];

}

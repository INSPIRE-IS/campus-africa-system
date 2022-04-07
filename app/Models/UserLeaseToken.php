<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserLeaseToken extends Model
{
    use HasFactory;

    protected $table = 'user_lease_token';

    protected $fillable = [
        'userUid',
        'leaseToken',
        'active',
        'signed',
    ];
}

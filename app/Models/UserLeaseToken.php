<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserLeaseToken extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'user_lease_token';

    protected $fillable = [
        'userUid',
        'leaseToken',
        'lease_type',
        'active',
        'signed',
        'signature_url',
        'role',
        'year',
        'deleted_at',
    ];
}

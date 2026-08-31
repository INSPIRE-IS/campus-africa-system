<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FirebaseRegistrationLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'students_info_id',
        'booking_id',
        'year',
        'email',
        'firebase_uid',
        'status',
        'stage',
        'message',
        'meta',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'meta' => 'array',
    ];
}

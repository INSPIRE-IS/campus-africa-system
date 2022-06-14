<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicationToken extends Model
{
    use HasFactory;

    protected $table = 'user_application_token';

    protected $fillable = [
        'userUid',
        'applicationToken',
        'verified',
        'security_token',
    ];
}

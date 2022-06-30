<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OnlinePayments extends Model
{
    use HasFactory;

    protected $table = 'online_payment';

    protected $fillable = [
        'userUid',
        'requestID',
        'reference',
        'amount',
        'checkSum',
        'returnUrl',
        'status'
    ];
}

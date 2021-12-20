<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Residence extends Model
{
    use HasFactory;

    protected $table = 'property';

    protected $fillable = [
        'residence',
        'booking_rate',
        'owner',
        'address',
    ];
}

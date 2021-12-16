<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $table = 'bookings_data';

    protected $fillable = [
        'userId',
        'property',
        'roomType',
        'propertyId',
        
        'property_data_id',
        'floor',
        'unit',
        'room',
        'bed',
        'room_rate',
        'bed_code',
    ];
}

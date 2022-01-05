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
        'lease_from_date',
        'lease_from_time',
        'lease_to_date',
        'lease_to_time',
    ];

    public function users(){
        return $this->hasOne("App\Models\User", "id", "userId");
    }
}

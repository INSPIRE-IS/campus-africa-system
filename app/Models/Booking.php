<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model
{
    use HasFactory, SoftDeletes;

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
        'waive_reason',
        'lease_unique',
        'year',
        'booking_code',
        'booking_payment_ref',
        'payment_type',
        'cancelled',
    ];

    public function users()
    {
        return $this->hasOne("App\Models\User", "id", "userId");
    }

    public function residence()
    {
        return $this->hasOne("App\Models\Residence", "id", "property");
    }

    public function roomType()
    {
        return $this->hasOne("App\Models\RoomTypes", "id", "roomType");
    }

    public function property()
    {
        return $this->belongsTo("App\Models\Property", "property", "property");
    }

    public function propertyData()
    {
        return $this->belongsTo("App\Models\Property", "property_data_id", "property_data_id");
    }
    public function propertyDataID()
    {
        return $this->belongsTo("App\Models\Property", "property_data_id", "id");
    }
    public function bookingPayment()
    {
        return $this->hasOne("App\Models\BookingPayment", "bookingId", "id");
    }
}

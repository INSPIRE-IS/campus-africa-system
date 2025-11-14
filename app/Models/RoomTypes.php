<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomTypes extends Model
{
    use HasFactory;

    protected $table = 'room_types';

    protected $fillable = [
        'id',
        'property_id',
        'name',
        'online',
        'img_url',
        'room_map_slug',
        'room_map_price',
        'pull_price',
        'booking_price',
        'rent_price',
        'apply_nsfas',
        'apply_bursary',
        'apply_cash',
        'use_nsfas',
    ];

    public function scopeSharedOnly($query)
    {
        return $query->where('name', 'not like', 'Single%');
    }

    public function bookings()
    {
        return $this->hasMany(\App\Models\Booking::class, 'roomType', 'id');
    }
}

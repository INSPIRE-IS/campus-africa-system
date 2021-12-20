<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    use HasFactory;

    protected $table = 'property_data';

    protected $fillable = [
        'residence',
        'meta_info_1',
        'meta_info_2',
        'floor',
        'unit',
        'room',
        'bed',
        'bed_code',
        'inventory',
        'room_type',
        'room_rate',
        'room_available',
        'user_id_room',
    ];
}

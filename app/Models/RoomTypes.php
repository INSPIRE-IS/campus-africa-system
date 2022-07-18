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
    ];
}

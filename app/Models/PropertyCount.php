<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PropertyCount extends Model
{
    use HasFactory;

    protected $table = 'property_count';

    protected $fillable = [
        'property_id',
        'booking_count',
        'date',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PropertyPermission extends Model
{
    use HasFactory;

    protected $table = 'property_permission';

    protected $fillable = [
        'userUid',
        'property',
    ];
}

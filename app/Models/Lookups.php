<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lookups extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'lookup_name',
    ];
}

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
        'lease_from_date',
        'lease_from_time',
        'lease_to_date',
        'lease_to_time',
        'deafault',
        'class',
        'waiver_date_from',
        'waiver_date_to',
    ];
}

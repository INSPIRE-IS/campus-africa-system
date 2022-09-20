<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstitutionProperties extends Model
{
    use HasFactory;

    protected $table = 'institution_properties';

    protected $fillable = [
        'institution_id',
        'property_id',
    ];
}

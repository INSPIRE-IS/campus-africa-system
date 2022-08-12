<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Residence extends Model
{
    use HasFactory;

    protected $table = 'property';

    protected $fillable = [
        'residence',
        'booking_rate',
        'owner',
        'address',
        'nsfas_booking_rate',
        'bursary_booking_rate',
        'building_prefix',
        'account_name',
        'bank_name',
        'account_number',
        'branch',
        'branch_code',
        'company_reg',
        'company_vat',
    ];
}

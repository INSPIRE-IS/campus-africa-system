<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Residence extends Model
{
    use HasFactory;
    use SoftDeletes;

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
        'mda_id',
        'company_reg',
        'company_vat',
        'male',
        'female',
        'active_date',
        'inactive_date',
    ];
}

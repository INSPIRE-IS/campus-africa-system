<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MDAInfo extends Model
{
    use HasFactory;

    protected $table = 'mda_info';

    protected $fillable = [
        'userUid',
        'mda_tenant_id',
        'mda_data',
        'call_type'
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssurityToken extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'assurity_tokens';

    protected $fillable = [
        'userUid',
        'assurity_token',
        'active',
        'signed',
        'signature_url',
        'spouse_required',
        'spouse_not_required_reason',
        'spouse_signature_url',
        'signed_at',
        'signed_on',
        'role',
        'year',
        'deleted_at',
    ];
}

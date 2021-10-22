<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActionList extends Model
{
    use HasFactory;

    protected $fillable = [
        'userUid',
        'action',
        'actionUserUid',
        'actionUserDisplayName',
        'actionUserRole',
    ];
}

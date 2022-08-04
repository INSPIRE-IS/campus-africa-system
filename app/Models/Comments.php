<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comments extends Model
{
    use HasFactory;

    protected $table = 'comments';

    protected $fillable = [
        'user_uid',
        'comment',
        'action_user_uid',
        'bookingId'
    ];

    public function users(){
        return $this->hasOne("App\Models\User", "id", "action_user_uid");
    }
}

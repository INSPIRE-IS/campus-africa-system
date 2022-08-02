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
        'year',
    ];

    public function users(){
        return $this->hasOne("App\Models\User", "id", "actionUserUid");
    }

    public function usersInfo(){
        return $this->hasOne("App\Models\StudentsInfo", "userUid", "actionUserUid");
    }
}

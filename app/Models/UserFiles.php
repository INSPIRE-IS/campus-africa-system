<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserFiles extends Model
{
    use HasFactory;

    protected $fillable = [
        'fileId',
        'userUid',
        'fileName',
        'fileUrl',
        'status',
        'uploaded_by',
        'approved_by',
        'rejectReason',
    ];

    public function users(){
        return $this->hasOne("App\Models\User", "id", "userUid");
    }

    public function booking(){
        return $this->hasOne("App\Models\Booking", "userId", "userUid");
    }
}

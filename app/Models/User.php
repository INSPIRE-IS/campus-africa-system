<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'userRole',
        'email',
        'password',
        'userFirstName',
        'userLastname',
        'userTitle',
        'userDob',
        'userCell',
        'userEmail',
        'userId',
        'userNationality',
        'userPassport',
        'institutionStudy',
        'studentNo',
        'institutionCampus',
        'faculty',
        'year',
        'sponFirstName',
        'sponLastName',
        'sponCell',
        'sponEmail',
        'sponId',
        'burCompanyName',
        'burRegNo',
        'burContactPerson',
        'burContactNo',
        'burEmail',
        'kinFirstName',
        'kinLastName',
        'kinTitle',
        'kinId',
        'kinAddressStr',
        'kinAddressSuburb',
        'kinAddressCity',
        'kinAddressPost',
        'kinRelationship',
        'kinTelNo',
        'kinCellNo',
        'kinWorkNo',
        'kinEmail',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}

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
        'roomBlock',
        'roomNo',
        'roomType',
        'price',
        'studentType',
        'bookingRef',
        'bookingStatus',
        'applicationStatus',
        'yearOfStudy',
        'role_create_app',
        'role_edit_app',
        'role_upload_docs',
        'role_approve_docs',
        'kinNationality',
        'kinPassportNo',
        'pNationality',
        'pPassportNo',
        'pAddress1',
        'pAddress2',
        'pAddressCity',
        'pAddressPostal',
        'role_delete_booking',
        'role_delete_uploaded',
        'userAddress1',
        'userAddress2',
        'userAddressCity',
        'userAddressPostal',
        'app_address',
        'app_address_p',
        'app_parent_kin',
        'role_braamlofts',
        'role_80_jorissen',
        'role_49_jorissen',
        'role_dunvista_mansions',
        'role_wynton_joy',
        'role_126_siemert',
        'role_ymca',
        'bAddress1',
        'bAddress2',
        'bAddressCity',
        'bAddressPostal',
        'bPayment',
        'bint',
        'bIntName',
        'id_data_approved',
        'student_card_approved',
        'registration_approved',
        'bursary_approved',
        'bank_approved',
        'address_approved',
        'kin_approved',
        'parent_approved',
        'burser_approved',
        'role_change_date',
        'overall_status',
        'email_info',
        'role_nsfas_approval_upload',
        'role_cancel_booking',
    ];

    public function files(){
        return $this->hasMany("App\Models\UserFiles", "userUid", "id");
    }
    

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

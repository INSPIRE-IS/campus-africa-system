<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentsInfo extends Model
{
    use HasFactory;

    protected $table = 'students_info';

    protected $fillable = [
        'userUid',
        'bookingId',
        'userFirstName',
        'userLastname',
        'userTitle',
        'userDob',
        'userCellCode',
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
        'yearOfStudy',
        'studentType',
        'userAddress1',
        'userAddress2',
        'userAddressCity',
        'userAddressPostal',
        'bookingRef',
        'bookingStatus',
        'applicationStatus',
        'sponFirstName',
        'sponLastName',
        'sponCellCode',
        'sponCell',
        'sponEmail',
        'pNationality',
        'pPassportNo',
        'sponId',
        'pAddress1',
        'pAddress2',
        'pAddressCity',
        'pAddressPostal',
        'c_credit_risk',
        'c_credit_check',
        'burCompanyName',
        'burRegNo',
        'burContactPerson',
        'burContactCode',
        'burContactNo',
        'burEmail',
        'b_meeting',
        'bAddress1',
        'bAddress2',
        'bAddressCity',
        'bAddressPostal',
        'bPayment',
        'bint',
        'bIntName',
        'kinFirstName',
        'kinLastName',
        'kinTitle',
        'kinAddressStr',
        'kinAddressSuburb',
        'kinAddressCity',
        'kinAddressPost',
        'kinRelationship',
        'kinTelCode',
        'kinTelNo',
        'kinCellCode',
        'kinCellNo',
        'kinWorkCode',
        'kinWorkNo',
        'kinEmail',
        'kinNationality',
        'kinPassportNo',
        'app_address',
        'app_address_p',
        'app_parent_kin',
        'id_data_approved',
        'student_card_approved',
        'registration_approved',
        'bursary_approved',
        'bank_approved',
        'address_approved',
        'kin_approved',
        'parent_approved',
        'burser_approved',
        'overall_status',
        'email_info',
        'nsfas_bursar',
        'rob_10_fee',
        'rob_15_id',
        'rob_20_card',
        'rob_25_reg',
        'rob_30_funds',
        'rob_35_info',
        'rob_40_bed',
        'rob_45_other',
        'rob_50_approve',
        'rob_55_lease',
        'email_data_approved',
        'rob_60_cancelled',
        'onboard_complete',
        'email_verified',
        'fee_approved',
        'app_source',
        'mda_tenant_id',
        'fee_reminder_sent',
        'general_reminder_sent',
        'gender',
        'userEmailAlt',
    ];

    public function files()
    {
        return $this->hasMany("App\Models\UserFiles", "userUid", "userUid");
    }

    public function users()
    {
        return $this->hasOne("App\Models\User", "id", "userUid");
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentsInfo extends Model
{
    use HasFactory, SoftDeletes;

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
        'rob_65_debt',
        'onboard_complete',
        'email_verified',
        'fee_approved',
        'app_source',
        'mda_tenant_id',
        'fee_reminder_sent',
        'general_reminder_sent',
        'gender',
        'userEmailAlt',
        'rob_51_temp_lease_approve',
        'last_step_completed',
        'last_activity_at',
        'marketing_consent',
        'marketing_consent_at',
        'marketing_consent_channel',
    ];

    protected $casts = [
        'last_activity_at' => 'datetime',
        'marketing_consent' => 'boolean',
        'marketing_consent_at' => 'datetime',
    ];

    public function touchActivity(string $step)
    {
        $this->last_step_completed = $step;
        $this->last_activity_at = now();

        return $this->save();
    }

    public function files()
    {
        return $this->hasMany("App\Models\UserFiles", "userUid", "userUid");
    }

    public function users()
    {
        return $this->hasOne("App\Models\User", "id", "userUid");
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'bookingId', 'id');
    }

    public function compatibilityScoresAsA()
    {
        return $this->hasMany(StudentCompatibilityScore::class, 'student_a_id');
    }

    public function compatibilityScoresAsB()
    {
        return $this->hasMany(StudentCompatibilityScore::class, 'student_b_id');
    }

    public function compatibilityScores()
    {
        return $this->compatibilityScoresAsA->merge($this->compatibilityScoresAsB);
    }

    public function questionAnswers()
    {
        return $this->hasMany(StudentQuestionAnswer::class, 'student_id');
    }

    /**
     * Answers for a specific academic year
     */
    public function questionAnswersForYear($year)
    {
        return $this->hasMany(StudentQuestionAnswer::class, 'student_id')
            ->where('academic_year', $year);
    }
}

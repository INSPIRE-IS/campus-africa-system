<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lookups extends Model
{
    use HasFactory;


    const GROUPS = [
        '1' => 'Institution of Study',
        '2' => 'Insitution Campus',
        '3' => 'Faculty',
        '4' => 'Year',
        '5' => 'Nationality',
        '6' => 'Year of Study',
        '7' => 'Waive Booking Fee',
        '8' => 'Lease Admin Fee',
        '9' => 'Online Applications',
        '10' => 'Email - Restrict to Test Domain(s)',
        '11' => 'Email - BCC Email Addresses',
        '12' => 'Email - Reminder Booking Fee',
        '13' => 'Email - Reminder Outstanding Documents',
    ];

    const CLASSES = [
        '1' => 'Students',
        '2' => 'Staff',
    ];

    const DEFAULTS = [
        '1' => 'Yes',
        '2' => 'No',
    ];

    const MONTHS = [
        '1' => 'January',
        '2' => 'February',
        '3' => 'March',
        '4' => 'April',
        '5' => 'May',
        '6' => 'June',
        '7' => 'July',
        '8' => 'August',
        '9' => 'September',
        '10' => 'October',
        '11' => 'November',
        '12' => 'December',
    ];

    const DAYS = [
        '1' => 'Sunday',
        '2' => 'Monday',
        '3' => 'Tuesday',
        '4' => 'Wednesday',
        '5' => 'Thursday',
        '6' => 'Friday',
        '7' => 'Saturday',
    ];

    // Student Types
    const FUNDING_TYPES = [
        '1' => 'NSFAS',
        '2' => 'Bursary',
        '3' => 'Cash / Direct',
        '4' => 'GCRA',
        '5' => 'MQA',
        '6' => 'FUNZA',
        '7' => 'NRF',
    ];

    public static function fundingTypeIdForLabel($label)
    {
        $label = trim((string) $label);
        if ($label === '') {
            return null;
        }

        static $map = null;
        if ($map === null) {
            $map = [];
            foreach (static::FUNDING_TYPES as $id => $name) {
                $key = strtolower(preg_replace('/\s+/', ' ', trim($name)));
                $map[$key] = (string) $id;
            }
            $map['cash'] = '3';
        }

        $normalized = strtolower(preg_replace('/\s+/', ' ', $label));

        return $map[$normalized] ?? null;
    }

    const TEMP_LEASE_WINDOW = [
        'start' => ['month' => 1, 'day' => 1],
        'end' => ['month' => 2, 'day' => 28],
    ];


    protected $fillable = [
        'group_id',
        'lookup_name',
        'data',
        'lease_from_date',
        'lease_from_time',
        'lease_to_date',
        'lease_to_time',
        'deafault',
        'class',
        'waiver_date_from',
        'waiver_date_to',
        'actionUserUid',
        'month_from',
        'day_from',
        'month_to',
        'day_to',
        'nsfas_amount',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function users()
    {
        return $this->hasOne("App\Models\User", "id", "actionUserUid");
    }
}

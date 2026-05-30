<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

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
        'is_active_year',
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
        'is_active_year' => 'boolean',
    ];

    /**
     * Resolve the configured active application year lookup (group_id=4).
     * Falls back to the newest year row if no active flag is set (legacy installs).
     */
    public static function activeYearLookup(): ?self
    {
        $active = static::where('group_id', 4)->where('is_active_year', 1)->first();
        if ($active) {
            return $active;
        }

        return static::where('group_id', 4)->orderByDesc('lookup_name')->first();
    }

    public static function activeYearValue(): ?string
    {
        $lookup = static::activeYearLookup();

        return $lookup ? (string) $lookup->lookup_name : null;
    }

    /**
     * UI year window used across dropdowns (registration/admin/reporting):
     * - Active year
     * - Previous year (active-1) when present
     * - Never show/select anything older than the current calendar year
     *   (prevents students/admins accidentally working on expired years).
     */
    public static function uiYearLookups(): Collection
    {
        $activeYear = (int) static::activeYearValue();
        if ($activeYear <= 0) {
            $activeYear = (int) now()->year;
        }

        $minYear = max((int) now()->year, $activeYear - 1);
        $candidateYears = [];
        for ($y = $minYear; $y <= $activeYear; $y++) {
            $candidateYears[] = (string) $y;
        }

        return static::where('group_id', 4)
            ->whereIn('lookup_name', $candidateYears)
            ->orderByDesc('lookup_name')
            ->get();
    }

    public static function isUiYear($year): bool
    {
        $y = (string) (int) $year;
        if ($y === '0') {
            return false;
        }

        return static::uiYearLookups()->pluck('lookup_name')->contains($y);
    }

    /**
     * Internal admin year window: always show active year + previous year (when that lookup exists).
     * This is intentionally NOT tied to the calendar year, because admins need to work on last year.
     */
    public static function internalYearLookups(): Collection
    {
        $activeYear = (int) static::activeYearValue();
        if ($activeYear <= 0) {
            $activeYear = (int) now()->year;
        }

        $candidateYears = [
            (string) $activeYear,
            (string) ($activeYear - 1),
        ];

        return static::where('group_id', 4)
            ->whereIn('lookup_name', $candidateYears)
            ->orderByDesc('lookup_name')
            ->get();
    }

    public static function isInternalYear($year): bool
    {
        $y = (string) (int) $year;
        if ($y === '0') {
            return false;
        }

        return static::internalYearLookups()->pluck('lookup_name')->contains($y);
    }

    /**
     * Resolve an application year for a user-scoped flow.
     *
     * Resolution order:
     *  1) preferredYear (usually Session::get('year') or an explicit request year)
     *  2) latest booking year for the given user
     *  3) configured active year (falls back to newest lookup row for legacy installs)
     */
    public static function resolveYearForUser(?int $userId = null, $preferredYear = null): ?string
    {
        $preferred = (string) (int) $preferredYear;
        if ($preferred !== '' && $preferred !== '0') {
            return $preferred;
        }

        if (!empty($userId)) {
            $bookingYear = Booking::where('userId', $userId)->orderByDesc('year')->value('year');
            if (!empty($bookingYear)) {
                return (string) $bookingYear;
            }
        }

        return static::activeYearValue();
    }

    public function users()
    {
        return $this->hasOne("App\Models\User", "id", "actionUserUid");
    }
}

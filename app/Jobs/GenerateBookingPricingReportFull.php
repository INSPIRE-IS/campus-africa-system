<?php

namespace App\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use App\Models\Lookups;
use App\Models\Booking;
use App\Models\StudentsInfo;
use App\Models\BookingPayment;
use App\Models\Residence;
use App\Services\BookingPricingCalculator;
use Illuminate\Support\Facades\Mail;

class GenerateBookingPricingReportFull implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 600;

    /**
     * The maximum number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 1;

    protected $email;
    protected $requestedBy;
    protected $reportId;
    protected $lastId;
    protected $chunkSize;
    protected $propertyId;
    protected $propertyName;
    protected $splitByProperty;
    protected $splitByPropertyDefault = true;
    protected $includeAllRows = true;
    protected $fieldToggles = [
        'booking_fee' => false,
        'booking_fee_waived' => true,
        'rob_b_waived' => true,
        'loan_admin_fee' => true,
        'loan_admin_fee_waived' => true,
        'rob_loan_waived' => true,
        'deposit' => true,
        'deposit_waived' => true,
        'rob_deposit_waived' => true,
        'monthly_rental' => true,
        'monthly_rental_total' => true,
        'total_funded' => true,
        'total_top_up' => true,
        'funded_monthly' => true,
        'nsfas_rate' => true,
    ];
    protected $excludeCancelledBookings = true;
    protected $excludeRob60Cancelled = true;
    protected $requireRob10Paid = true;
    protected $excludeBuildingManager = true;
    protected $enforceBedRange = false;
    protected $filterActiveResidences = true;
    protected $requireStudentInfo = true;
    protected $csvDelimiter = "\t";
    protected $csvExtension = "tsv";
    protected $csvMime = "text/tab-separated-values";

    public function __construct(
        $email,
        $requestedBy = null,
        $reportId = null,
        $lastId = 0,
        $chunkSize = 1000,
        $propertyId = null,
        $propertyName = null,
        $splitByProperty = false
    ) {
        $this->email = $email;
        $this->requestedBy = $requestedBy;
        $this->reportId = $reportId;
        $this->lastId = $lastId;
        $this->chunkSize = $chunkSize;
        $this->propertyId = $propertyId;
        $this->propertyName = $propertyName;
        $this->splitByProperty = $splitByProperty ?? $this->splitByPropertyDefault;
    }

    public function handle()
    {
        set_time_limit($this->timeout);
        ini_set('max_execution_time', $this->timeout);

        $currentYear = $this->getCurrentYearLookupName();
        if (!$currentYear) {
            $this->sendReportEmail("Current year lookup not found.", null, false);
            return;
        }

        if ($this->splitByProperty && $this->propertyId === null && $this->lastId === 0) {
            $this->dispatchPropertyJobs($currentYear);
            return;
        }

        // Use the active/current year lookup row deterministically (do not rely on `->first()` ordering).
        $lookupLeaseDates = Lookups::where('group_id', '4')->where('lookup_name', $currentYear)->first();
        if (!$lookupLeaseDates) {
            $lookupLeaseDates = Lookups::activeYearLookup();
        }
        if (!$lookupLeaseDates) {
            $this->sendReportEmail("Year lookup row not found for {$currentYear}.", null, false, $currentYear);
            return;
        }
        $waiveBookingFee = Lookups::where('group_id', '7')->first();
        [$wpResidences, $wpRooms] = $this->getCachedWpData();

        if (empty($wpResidences) || empty($wpRooms)) {
            $message = "WP data missing (residences/rooms empty). Aborting report.";
            $this->sendReportEmail($message, null, false, $currentYear);
            return;
        }

        $this->ensureReportInitialized($currentYear);

        $bookings = $this->buildReportBookingQuery($currentYear)
            ->where('id', '>', $this->lastId)
            ->with('residence:id,residence')
            ->orderBy('id')
            ->limit($this->chunkSize)
            ->get();

        if ($bookings->isEmpty()) {
            $this->finalizeReport();
            return;
        }

        $counts = $this->getCounts();
        $csvRows = [];

        $fields = $this->getFields();
        $studentInfoMap = StudentsInfo::where('year', $currentYear)
            ->whereIn('userUid', $bookings->pluck('userId')->unique())
            ->get()
            ->keyBy('userUid');
        $paymentMap = BookingPayment::whereIn('bookingId', $bookings->pluck('id'))
            ->get()
            ->keyBy('bookingId');

        foreach ($bookings as $booking) {
            $studentInfo = $studentInfoMap->get($booking->userId);
            if (!$studentInfo) {
                $counts["missing_student"]++;
                continue;
            }

            $payment = $paymentMap->get($booking->id);
            if (!$payment) {
                $counts["missing_payment"]++;
                continue;
            }

            $expected = BookingPricingCalculator::calculateWpPricingForBooking(
                $booking,
                $studentInfo,
                $lookupLeaseDates,
                $waiveBookingFee,
                $wpResidences,
                $wpRooms
            );

            if (!$expected) {
                continue;
            }

            $diffFields = [];
            $hasDifference = false;
            $csvRow = [
                $booking->id,
                $booking->userId,
                optional($booking->residence)->residence,
                $studentInfo->studentType,
                $this->formatCancelledStatus($booking),
                $this->formatRob10Status($studentInfo),
                $this->formatRob40Status($studentInfo),
            ];

            foreach ($fields as $field) {
                $currentValue = $payment->$field;
                $expectedValue = array_key_exists($field, $expected) ? $expected[$field] : null;

                if ($this->valuesDiffer($currentValue, $expectedValue)) {
                    $diffFields[] = $field;
                }

                $csvRow[] = $currentValue;
                $csvRow[] = $expectedValue;
            }

            if (!empty($diffFields)) {
                $hasDifference = true;
                $counts["differences"]++;
                foreach ($diffFields as $diffField) {
                    if (!isset($counts["diff_field_counts"][$diffField])) {
                        $counts["diff_field_counts"][$diffField] = 0;
                    }
                    $counts["diff_field_counts"][$diffField]++;
                }
            }

            if ($this->includeAllRows || $hasDifference) {
                $diffSummary = !empty($diffFields) ? implode('|', $diffFields) : '';
                $status = $hasDifference ? 'bad' : 'good';
                array_splice($csvRow, 7, 0, [$diffSummary, $status]);
                $csvRows[] = $csvRow;
            }
        }

        if (!empty($csvRows)) {
            $this->appendCsvRows($csvRows);
        }

        $this->saveCounts($counts);

        $lastId = $bookings->last()->id;
        self::dispatch(
            $this->email,
            $this->requestedBy,
            $this->reportId,
            $lastId,
            $this->chunkSize,
            $this->propertyId,
            $this->propertyName,
            false
        );
    }

    public function failed(Exception $exception)
    {
        $this->sendReportEmail($exception->getMessage(), null, false);
    }

    private function ensureReportInitialized($currentYear)
    {
        $this->getReportId();
        $csvPath = $this->getCsvPath();

        if (!file_exists($csvPath)) {
            $this->ensureReportDirectory();
            $handle = fopen($csvPath, 'w');
            if ($handle !== false) {
                fputcsv($handle, $this->buildCsvHeader(), $this->csvDelimiter);
                fclose($handle);
            }
        }

        if (!Cache::has($this->getCountsCacheKey())) {
            $totalBookingsQuery = $this->buildReportBookingQuery($currentYear);
            $counts = [
                "current_year" => $currentYear,
                "total_bookings" => $totalBookingsQuery->count(),
                "missing_student" => 0,
                "missing_payment" => 0,
                "differences" => 0,
                "diff_field_counts" => [],
            ];
            Cache::put($this->getCountsCacheKey(), $counts, 3600);
        }
    }

    private function finalizeReport()
    {
        $counts = $this->getCounts();
        $summary = $this->buildSummary($counts);
        $csvPath = file_exists($this->getCsvPath()) ? $this->getCsvPath() : null;

        $this->sendReportEmail($summary, $csvPath, true, $counts["current_year"] ?? null);

        Cache::forget($this->getCountsCacheKey());
        if (file_exists($this->getCsvPath())) {
            @unlink($this->getCsvPath());
        }
    }

    private function getCounts()
    {
        $counts = Cache::get($this->getCountsCacheKey());
        if (!is_array($counts)) {
            $counts = [
                "current_year" => null,
                "total_bookings" => 0,
                "missing_student" => 0,
                "missing_payment" => 0,
                "differences" => 0,
                "diff_field_counts" => [],
            ];
        }
        return $counts;
    }

    private function saveCounts(array $counts)
    {
        Cache::put($this->getCountsCacheKey(), $counts, 3600);
    }

    private function getCountsCacheKey()
    {
        return "booking_pricing_report_wp:" . $this->getReportId();
    }

    private function getReportId()
    {
        if (!$this->reportId) {
            $this->reportId = (string) Str::uuid();
        }

        return $this->reportId;
    }

    private function getCsvPath()
    {
        $this->ensureReportDirectory();
        $propertySuffix = $this->propertyId ? '_property_' . $this->propertyId : '';
        $extension = $this->csvExtension ?: "csv";
        return storage_path('app/reports/booking_pricing_report_wp_' . $this->getReportId() . $propertySuffix . '.' . $extension);
    }

    private function ensureReportDirectory()
    {
        $dir = storage_path('app/reports');
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
    }

    private function getCurrentYearLookupName()
    {
        $year = Lookups::activeYearValue();
        return $year ? (string) $year : null;
    }

    private function getFields()
    {
        return array_keys(array_filter($this->fieldToggles));
    }

    private function buildCsvHeader()
    {
        $fields = $this->getFields();
        $header = ["booking_id", "user_id", "building", "student_type", "cancelled", "rob_10_status", "rob_40_status", "diff_fields", "status"];
        foreach ($fields as $field) {
            $header[] = "current_" . $field;
            $header[] = "expected_" . $field;
        }
        return $header;
    }

    private function valuesDiffer($current, $expected)
    {
        if ($current === null && $expected === null) {
            return false;
        }
        if ($current === null || $expected === null) {
            return true;
        }

        if (is_numeric($current) || is_numeric($expected)) {
            return abs(floatval($current) - floatval($expected)) > 0.01;
        }

        return (string) $current !== (string) $expected;
    }

    private function buildSummary(array $report)
    {
        $lines = [
            "Booking Pricing Report (WP)",
        ];

        if ($this->propertyName || $this->propertyId) {
            $propertyLabel = $this->propertyName ?: "Property " . $this->propertyId;
            $lines[] = "Property: " . $propertyLabel;
        }

        $lines[] = "Year: " . ($report["current_year"] ?? "");
        $lines[] = "Total bookings: " . ($report["total_bookings"] ?? 0);
        $lines[] = "Differences: " . ($report["differences"] ?? 0);
        $lines[] = "Missing student: " . ($report["missing_student"] ?? 0);
        $lines[] = "Missing payment: " . ($report["missing_payment"] ?? 0);

        if (!empty($report["diff_field_counts"]) && is_array($report["diff_field_counts"])) {
            $diffCounts = $report["diff_field_counts"];
            arsort($diffCounts);
            $top = array_slice($diffCounts, 0, 10, true);
            $parts = [];
            foreach ($top as $field => $count) {
                $parts[] = $field . "=" . $count;
            }
            if (!empty($parts)) {
                $lines[] = "Top diff fields: " . implode(", ", $parts);
            }
        }

        $disabledFields = array_keys(array_filter($this->fieldToggles, function ($enabled) {
            return !$enabled;
        }));
        if (!empty($disabledFields)) {
            $lines[] = "Disabled fields: " . implode(", ", $disabledFields);
        }

        return implode("\n", $lines);
    }

    private function sendReportEmail($body, $csvPath = null, $isSuccess = true, $year = null)
    {
        $email = $this->email;
        $subject = $isSuccess ? "Booking Pricing Report (WP)" : "Booking Pricing Report (WP) Failed";
        if ($year) {
            $subject .= " - {$year}";
        }
        if ($this->propertyName) {
            $subject .= " - {$this->propertyName}";
        } elseif ($this->propertyId) {
            $subject .= " - Property {$this->propertyId}";
        }

        Mail::raw($body, function ($message) use ($email, $subject, $csvPath, $year) {
            $message->to($email)->subject($subject);

            if (!empty($csvPath)) {
                $propertySuffix = $this->propertyId ? "_property_{$this->propertyId}" : "";
                $extension = $this->csvExtension ?: "csv";
                $mime = $this->csvMime ?: "text/csv";
                $filename = $year ? "booking_pricing_report_wp_{$year}{$propertySuffix}.{$extension}" : "booking_pricing_report_wp{$propertySuffix}.{$extension}";
                $message->attach($csvPath, ['as' => $filename, 'mime' => $mime]);
            }
        });
    }

    private function appendCsvRows(array $rows)
    {
        $csvPath = $this->getCsvPath();
        $handle = fopen($csvPath, 'a');
        if ($handle === false) {
            return;
        }

        foreach ($rows as $row) {
            $cleanRow = array_map([$this, 'normalizeCsvValue'], $row);
            fputcsv($handle, $cleanRow, $this->csvDelimiter);
        }

        fclose($handle);
    }

    private function normalizeCsvValue($value)
    {
        if ($value === null) {
            return null;
        }

        if (is_bool($value)) {
            return $value ? 1 : 0;
        }

        if (is_scalar($value)) {
            $string = (string) $value;
            return str_replace(["\t", "\r", "\n"], " ", $string);
        }

        $string = json_encode($value);
        return str_replace(["\t", "\r", "\n"], " ", $string ?: "");
    }

    private function getActiveResidenceIds($year)
    {
        $exclude = [];
        if ($year >= 2026) {
            $exclude = [3, 4, 7];
        }

        $query = Residence::whereNull('status');
        if (!empty($exclude)) {
            $query->whereNotIn('id', $exclude);
        }

        return $query->pluck('id')->toArray();
    }

    private function buildStudentInfoFilterQuery($currentYear)
    {
        $query = StudentsInfo::where('year', $currentYear);

        if ($this->excludeRob60Cancelled) {
            $query->where(function ($inner) {
                $inner->whereNull('rob_60_cancelled')->orWhere('rob_60_cancelled', '!=', 1);
            });
        }

        if ($this->requireRob10Paid) {
            $query->where('rob_10_fee', '2');
        }

        return $query;
    }

    private function buildReportBookingQuery($currentYear)
    {
        $year = (int) $currentYear;

        $query = Booking::where('year', $currentYear)
            ->when($this->propertyId, function ($inner) {
                return $inner->where('property', $this->propertyId);
            });

        if ($this->filterActiveResidences) {
            $activeResidenceIds = $this->getActiveResidenceIds($year);
            if (!empty($activeResidenceIds)) {
                $query->whereIn('property', $activeResidenceIds);
            }
        }

        if ($this->excludeCancelledBookings) {
            $query->where(function ($inner) {
                $inner->whereNull('cancelled')->orWhere('cancelled', '!=', '1');
            });
        }

        if ($this->excludeBuildingManager) {
            $query->whereHas('roomType', function ($inner) {
                $inner->where('name', '!=', 'Building Manager');
            });
        }

        if ($this->enforceBedRange) {
            $query->whereHas('propertyDataID', function ($inner) use ($year) {
                $inner->where('inventory', '1')
                    ->where('year', '<=', $year)
                    ->where(function ($range) use ($year) {
                        $range->where('year_end', 0)->orWhere('year_end', $year);
                    });
            });
        }

        if ($this->requireStudentInfo || $this->excludeRob60Cancelled || $this->requireRob10Paid) {
            $query->whereIn('userId', $this->buildStudentInfoFilterQuery($currentYear)->select('userUid'));
        }

        return $query;
    }

    private function formatCancelledStatus($booking)
    {
        return ($booking->cancelled == '1' || $booking->cancelled === 1) ? 'Yes' : 'No';
    }

    private function formatRob10Status($studentInfo)
    {
        return ($studentInfo->rob_10_fee == '2') ? 'Paid' : 'Unpaid';
    }

    private function formatRob40Status($studentInfo)
    {
        return ($studentInfo->rob_40_bed == '2') ? 'Allocated' : 'Unallocated';
    }

    private function getCachedWpData()
    {
        return Cache::remember('booking_pricing_wp_data', 1200, function () {
            return BookingPricingCalculator::fetchWpData();
        });
    }

    private function dispatchPropertyJobs($currentYear)
    {
        $propertyIds = $this->buildReportBookingQuery($currentYear)
            ->whereNotNull('property')
            ->distinct()
            ->pluck('property')
            ->filter()
            ->values();

        if ($propertyIds->isEmpty()) {
            $this->sendReportEmail("No bookings found for {$currentYear}.", null, true, $currentYear);
            return;
        }

        $properties = Residence::whereIn('id', $propertyIds)->get(['id', 'residence'])->keyBy('id');

        foreach ($propertyIds as $propertyId) {
            $propertyName = $properties->get($propertyId)->residence ?? "Property {$propertyId}";
            self::dispatch(
                $this->email,
                $this->requestedBy,
                null,
                0,
                $this->chunkSize,
                $propertyId,
                $propertyName,
                false
            );
        }
    }
}

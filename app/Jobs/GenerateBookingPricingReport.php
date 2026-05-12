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
use Illuminate\Support\Facades\Mail;

class GenerateBookingPricingReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 900;

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
    protected $includeAllRows = true;
    protected $excludeCancelledBookings = true;
    protected $excludeRob60Cancelled = true;
    protected $requireRob10Paid = true;
    protected $excludeBuildingManager = true;
    protected $enforceBedRange = false;
    protected $filterActiveResidences = true;
    protected $requireStudentInfo = true;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($email, $requestedBy = null, $reportId = null, $lastId = 0, $chunkSize = 1500)
    {
        $this->email = $email;
        $this->requestedBy = $requestedBy;
        $this->reportId = $reportId;
        $this->lastId = $lastId;
        $this->chunkSize = $chunkSize;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        set_time_limit($this->timeout);

        $currentYear = $this->getCurrentYearLookupName();
        if (!$currentYear) {
            $this->sendReportEmail("Current year lookup not found.", null, null, false);
            return;
        }

        [$leaseFeeData, $depositData] = $this->getYearFundingConfig($currentYear);

        if (empty($leaseFeeData) && empty($depositData)) {
            $this->sendReportEmail("No local fee/deposit config found for {$currentYear} (Year lookup data / group 4).", null, null, false);
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
        $csvLines = [];
        $missingLines = [];

        foreach ($bookings as $booking) {
            $studentInfo = StudentsInfo::where('userUid', $booking->userId)->where('year', $currentYear)->first();
            if (!$studentInfo) {
                $counts["missing_student"]++;
                continue;
            }

            $payment = BookingPayment::where('bookingId', $booking->id)->first();
            if (!$payment) {
                $counts["missing_payment"]++;
                continue;
            }

            $fundingId = $this->mapStudentTypeToFundingId($studentInfo->studentType);
            if (!$fundingId) {
                $counts["missing_funding_type"]++;
                $missingLines[] = implode(',', [
                    $booking->id,
                    $booking->userId,
                    $this->escapeCsv(optional($booking->residence)->residence),
                    $this->escapeCsv($studentInfo->studentType),
                    $this->escapeCsv($this->formatCancelledStatus($booking)),
                    $this->escapeCsv($this->formatRob10Status($studentInfo)),
                    $this->escapeCsv($this->formatRob40Status($studentInfo)),
                ]);
                continue;
            }

            [$expectedLoanFee, $expectedDeposit] = $this->calculateLocalLeaseAndDeposit($fundingId, $payment, $leaseFeeData, $depositData, true);

            $diffLoan = abs(floatval($payment->loan_admin_fee) - floatval($expectedLoanFee)) > 0.01;
            $diffDeposit = $expectedDeposit !== null && abs(floatval($payment->deposit) - floatval($expectedDeposit)) > 0.01;
            $hasDifference = $diffLoan || $diffDeposit;
            $status = $hasDifference ? 'bad' : 'good';

            if ($hasDifference) {
                $counts["differences"]++;
                if ($diffLoan) {
                    $counts["loan_fee_differences"]++;
                }
                if ($diffDeposit) {
                    $counts["deposit_differences"]++;
                }
            }

            if ($this->includeAllRows || $hasDifference) {
                $csvLines[] = implode(',', [
                    $booking->id,
                    $booking->userId,
                    $this->escapeCsv(optional($booking->residence)->residence),
                    $this->escapeCsv($studentInfo->studentType),
                    $this->escapeCsv($this->formatCancelledStatus($booking)),
                    $this->escapeCsv($this->formatRob10Status($studentInfo)),
                    $this->escapeCsv($this->formatRob40Status($studentInfo)),
                    $payment->loan_admin_fee,
                    $expectedLoanFee,
                    $payment->deposit,
                    $expectedDeposit !== null ? $expectedDeposit : '',
                    $status,
                ]);
            }
        }

        if (!empty($csvLines)) {
            $csvData = implode("\n", $csvLines) . "\n";
            file_put_contents($this->getCsvPath(), $csvData, FILE_APPEND);
        }

        if (!empty($missingLines)) {
            $missingData = implode("\n", $missingLines) . "\n";
            file_put_contents($this->getMissingCsvPath(), $missingData, FILE_APPEND);
        }

        $this->saveCounts($counts);

        $lastId = $bookings->last()->id;
        self::dispatch($this->email, $this->requestedBy, $this->reportId, $lastId, $this->chunkSize);
    }

    /**
     * The job failed to process.
     *
     * @param  Exception  $exception
     * @return void
     */
    public function failed(Exception $exception)
    {
        $this->sendReportEmail($exception->getMessage(), null, null, false);
    }

    private function ensureReportInitialized($currentYear)
    {
        $this->getReportId();
        $csvPath = $this->getCsvPath();
        $missingPath = $this->getMissingCsvPath();

        if (!file_exists($csvPath)) {
            $header = "booking_id,user_id,building,student_type,cancelled,rob_10_status,rob_40_status,current_loan_admin_fee,expected_loan_admin_fee,current_deposit,expected_deposit,status\n";
            $this->ensureReportDirectory();
            file_put_contents($csvPath, $header);
        }

        if (!file_exists($missingPath)) {
            $missingHeader = "booking_id,user_id,building,student_type,cancelled,rob_10_status,rob_40_status\n";
            $this->ensureReportDirectory();
            file_put_contents($missingPath, $missingHeader);
        }

        if (!Cache::has($this->getCountsCacheKey())) {
            $counts = [
                "current_year" => $currentYear,
                "total_bookings" => $this->buildReportBookingQuery($currentYear)->count(),
                "missing_student" => 0,
                "missing_payment" => 0,
                "missing_funding_type" => 0,
                "differences" => 0,
                "loan_fee_differences" => 0,
                "deposit_differences" => 0,
            ];
            Cache::put($this->getCountsCacheKey(), $counts, 3600);
        }
    }

    private function finalizeReport()
    {
        $counts = $this->getCounts();
        $summary = $this->buildSummary($counts);
        $csvPath = file_exists($this->getCsvPath()) ? $this->getCsvPath() : null;
        $missingPath = file_exists($this->getMissingCsvPath()) ? $this->getMissingCsvPath() : null;

        if (!$this->includeAllRows && ($counts["differences"] ?? 0) === 0) {
            $csvPath = null;
        }
        if (($counts["missing_funding_type"] ?? 0) === 0) {
            $missingPath = null;
        }

        $this->sendReportEmail($summary, $csvPath, $missingPath, true, $counts["current_year"] ?? null);

        Cache::forget($this->getCountsCacheKey());
        if (file_exists($this->getCsvPath())) {
            @unlink($this->getCsvPath());
        }
        if (file_exists($this->getMissingCsvPath())) {
            @unlink($this->getMissingCsvPath());
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
                "missing_funding_type" => 0,
                "differences" => 0,
                "loan_fee_differences" => 0,
                "deposit_differences" => 0,
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
        return "booking_pricing_report_local:" . $this->getReportId();
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
        return storage_path('app/reports/booking_pricing_report_local_' . $this->getReportId() . '.csv');
    }

    private function getMissingCsvPath()
    {
        $this->ensureReportDirectory();
        return storage_path('app/reports/booking_pricing_report_local_missing_funding_' . $this->getReportId() . '.csv');
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

    private function getLookupDataForYear($groupId, $year)
    {
        $lookup = Lookups::where('group_id', $groupId)->where('lookup_name', $year)->first();
        if (!$lookup) {
            return [];
        }

        if (is_array($lookup->data)) {
            return $lookup->data;
        }

        if (!empty($lookup->data)) {
            $decoded = json_decode($lookup->data, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return [];
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

        $query = Booking::where('year', $currentYear);

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

    private function mapStudentTypeToFundingId($studentType)
    {
        return Lookups::fundingTypeIdForLabel($studentType);
    }

    private function getYearFundingConfig($year)
    {
        if (!$year) {
            return [[], []];
        }

        $lookup = Lookups::where('group_id', '4')->where('lookup_name', $year)->first();
        if (!$lookup) {
            return [[], []];
        }

        $data = [];
        if (is_array($lookup->data)) {
            $data = $lookup->data;
        } elseif (!empty($lookup->data)) {
            $decoded = json_decode($lookup->data, true);
            if (is_array($decoded)) {
                $data = $decoded;
            }
        }

        $leaseFees = is_array($data) ? ($data['lease_fees'] ?? []) : [];
        $depositApplies = is_array($data) ? ($data['deposit_applies'] ?? []) : [];

        return [$leaseFees, $depositApplies];
    }

    private function calculateLocalLeaseAndDeposit($fundingId, $payment, $leaseFeeData, $depositData, $treatMissingLeaseFeeAsZero = false)
    {
        $expectedLoanFee = null;
        if (!empty($leaseFeeData) && array_key_exists($fundingId, $leaseFeeData)) {
            $expectedLoanFee = floatval($leaseFeeData[$fundingId]);
        } elseif ($treatMissingLeaseFeeAsZero) {
            $expectedLoanFee = 0;
        }

        $expectedDeposit = null;
        if (!empty($depositData)) {
            $depositApplies = array_key_exists($fundingId, $depositData) && $depositData[$fundingId];
            $depositBase = floatval($payment->monthly_rental);
            $expectedDeposit = $depositApplies ? $depositBase : 0;
        }

        return [$expectedLoanFee, $expectedDeposit];
    }

    private function buildSummary(array $report)
    {
        $lines = [
            "Booking Pricing Report (Local)",
            "Year: " . ($report["current_year"] ?? ""),
            "Total bookings: " . ($report["total_bookings"] ?? 0),
            "Differences: " . ($report["differences"] ?? 0),
            "Loan fee differences: " . ($report["loan_fee_differences"] ?? 0),
            "Deposit differences: " . ($report["deposit_differences"] ?? 0),
            "Missing student: " . ($report["missing_student"] ?? 0),
            "Missing payment: " . ($report["missing_payment"] ?? 0),
            "Missing funding type: " . ($report["missing_funding_type"] ?? 0),
        ];

        if (($report["missing_funding_type"] ?? 0) > 0) {
            $lines[] = "Missing funding type list attached.";
        }

        return implode("\n", $lines);
    }

    private function sendReportEmail($body, $csvPath = null, $missingPath = null, $isSuccess = true, $year = null)
    {
        $email = $this->email;
        $subject = $isSuccess ? "Booking Pricing Report (Local)" : "Booking Pricing Report (Local) Failed";
        if ($year) {
            $subject .= " - {$year}";
        }

        Mail::raw($body, function ($message) use ($email, $subject, $csvPath, $missingPath, $year) {
            $message->to($email)->subject($subject);

            if (!empty($csvPath)) {
                $filename = $year ? "booking_pricing_report_local_{$year}.csv" : "booking_pricing_report_local.csv";
                $message->attach($csvPath, ['as' => $filename, 'mime' => 'text/csv']);
            }

            if (!empty($missingPath)) {
                $filename = $year ? "booking_pricing_report_local_missing_funding_{$year}.csv" : "booking_pricing_report_local_missing_funding.csv";
                $message->attach($missingPath, ['as' => $filename, 'mime' => 'text/csv']);
            }
        });
    }

    private function escapeCsv($value)
    {
        $value = (string) $value;
        $needsQuotes = strpos($value, ',') !== false || strpos($value, '"') !== false || strpos($value, "\n") !== false;

        if ($needsQuotes) {
            $value = '"' . str_replace('"', '""', $value) . '"';
        }

        return $value;
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
}

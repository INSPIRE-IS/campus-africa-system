<?php

namespace App\Console\Commands;

use App\Models\ActionList;
use App\Models\Booking;
use App\Models\BookingPayment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ImportMonthlyPop extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'booking:import-monthly-pop {--file=} {--year=} {--dry-run}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import monthly POP amount (and optional send flags) into booking_payments from CSV (or XLSX if supported).';

    public function handle()
    {
        $file = $this->option('file');
        $year = $this->option('year');
        $dryRun = (bool) $this->option('dry-run');

        if (empty($file) || !file_exists($file)) {
            $this->error('File not found. Use --file=/path/to/file.csv');
            return 1;
        }

        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        $rows = [];

        if ($ext === 'csv') {
            if (($handle = fopen($file, 'r')) === false) {
                $this->error('Unable to open CSV file.');
                return 1;
            }
            while (($data = fgetcsv($handle)) !== false) {
                $rows[] = $data;
            }
            fclose($handle);
        } elseif ($ext === 'xlsx') {
            if (!class_exists('\\Shuchkin\\SimpleXLSX')) {
                $this->error('XLSX support not installed. Please save as CSV or install shuchkin/simplexlsx.');
                return 1;
            }
            /** @disregard P1009 */
            $xlsx = \Shuchkin\SimpleXLSX::parse($file);
            if (!$xlsx) {
                $this->error('Unable to parse XLSX file.');
                return 1;
            }
            $rows = $xlsx->rows();
        } else {
            $this->error('Unsupported file type. Use CSV or XLSX.');
            return 1;
        }

        if (count($rows) < 2) {
            $this->error('No data rows found.');
            return 1;
        }

        $header = array_map(function ($h) {
            return strtolower(trim($h));
        }, $rows[0]);

        $colIndex = function ($names) use ($header) {
            foreach ($names as $name) {
                $idx = array_search($name, $header, true);
                if ($idx !== false) {
                    return $idx;
                }
            }
            return null;
        };

        $idxBookingId = $colIndex(['booking_id', 'bookingid', 'booking id']);
        $idxBookingRef = $colIndex(['booking_ref', 'bookingref', 'booking code', 'booking_code']);
        $idxUserUid = $colIndex(['user_uid', 'useruid', 'user_id', 'userid']);
        $idxAmount = $colIndex(['monthly_pop_amount', 'amount', 'amount_due', 'amount due']);
        $idxSendStudent = $colIndex(['send_to_student', 'send student', 'send_student']);
        $idxSendSponsor = $colIndex(['send_to_sponsor', 'send sponsor', 'send_sponsor']);

        if ($idxAmount === null) {
            $this->error('Missing required column: amount.');
            return 1;
        }

        if ($idxBookingId === null && $idxBookingRef === null && $idxUserUid === null) {
            $this->error('Missing identifier column: booking_id, booking_ref, or user_uid.');
            return 1;
        }

        $counts = [
            'processed' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => 0,
        ];

        foreach (array_slice($rows, 1) as $row) {
            $counts['processed']++;

            $booking = null;
            if ($idxBookingId !== null && !empty($row[$idxBookingId])) {
                $booking = Booking::where('id', trim($row[$idxBookingId]))->first();
            } elseif ($idxBookingRef !== null && !empty($row[$idxBookingRef])) {
                $booking = Booking::where('booking_code', trim($row[$idxBookingRef]))->first();
            } elseif ($idxUserUid !== null && !empty($row[$idxUserUid])) {
                if (empty($year)) {
                    $this->warn('Year required when using user_uid. Skipping row.');
                    $counts['skipped']++;
                    continue;
                }
                $booking = Booking::where('userId', trim($row[$idxUserUid]))->where('year', $year)->first();
            }

            if (!$booking) {
                $counts['skipped']++;
                continue;
            }

            $payment = BookingPayment::where('bookingId', $booking->id)->first();
            if (!$payment) {
                $counts['skipped']++;
                continue;
            }

            $amountRaw = $row[$idxAmount] ?? null;

            $amount = $this->parseAmount($amountRaw);
            if ($amount === null) {
                $this->warn("Invalid amount for booking {$booking->id}. Skipping.");
                $counts['skipped']++;
                continue;
            }

            $dueDate = Carbon::today()->addMonthNoOverflow()->startOfMonth()->toDateString();

            $oldDue = $payment->monthly_pop_due_date;
            $oldAmount = $payment->monthly_pop_amount;
            $oldSendStudent = $payment->send_to_student;
            $oldSendSponsor = $payment->send_to_sponsor;

            if ($dryRun) {
                $counts['updated']++;
                continue;
            }

            $payment->monthly_pop_due_date = $dueDate;
            $payment->monthly_pop_amount = $amount;

            if ($idxSendStudent !== null) {
                $payment->send_to_student = $this->parseFlag($row[$idxSendStudent] ?? null);
            }
            if ($idxSendSponsor !== null) {
                $payment->send_to_sponsor = $this->parseFlag($row[$idxSendSponsor] ?? null);
            }
            $payment->save();

            if (
                $oldDue != $dueDate ||
                (string) $oldAmount !== (string) $amount ||
                (string) $oldSendStudent !== (string) $payment->send_to_student ||
                (string) $oldSendSponsor !== (string) $payment->send_to_sponsor
            ) {
                ActionList::create([
                    'userUid' => $booking->userId,
                    'action' => "Monthly POP schedule updated (import). Due: {$oldDue} -> {$dueDate}; Amount: {$oldAmount} -> {$amount}; Send student: {$oldSendStudent} -> {$payment->send_to_student}; Send sponsor: {$oldSendSponsor} -> {$payment->send_to_sponsor}",
                    'actionUserUid' => 0,
                    'actionUserDisplayName' => 'System',
                    'actionUserRole' => 'system',
                    'year' => $booking->year,
                ]);
            }

            $counts['updated']++;
        }

        Log::info('booking:import-monthly-pop completed', $counts);
        $this->info(json_encode($counts));

        return 0;
    }

    private function parseDate($value)
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            // Excel serial date (days since 1899-12-30)
            return Carbon::create(1899, 12, 30)->addDays((int) $value)->toDateString();
        }

        try {
            return Carbon::parse($value)->toDateString();
        } catch (\Exception $e) {
            return null;
        }
    }

    private function parseAmount($value)
    {
        if ($value === null || $value === '') {
            return null;
        }
        $clean = str_replace([',', 'R', 'r', ' '], '', (string) $value);
        if (!is_numeric($clean)) {
            return null;
        }
        return (float) $clean;
    }

    private function parseFlag($value)
    {
        if ($value === null || $value === '') {
            return 0;
        }
        $clean = strtolower(trim((string) $value));
        if (in_array($clean, ['1', 'yes', 'y', 'true'], true)) {
            return 1;
        }
        return 0;
    }
}

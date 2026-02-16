<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixBookingFees extends Command
{
    protected $signature = 'bookings:fix-fees {file}';
    // php artisan bookings:fix-fees public/temp/fees.csv
    protected $description = 'Fix booking loan admin fees and deposits from CSV file';

    public function handle()
    {
        $filePath = $this->argument('file');

        if (!file_exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return Command::FAILURE;
        }

        $this->info("Reading CSV: {$filePath}");

        $handle = fopen($filePath, 'r');
        $header = fgetcsv($handle);

        $updated = 0;
        $skipped = 0;
        $notFound = 0;

        DB::beginTransaction();

        try {

            while (($row = fgetcsv($handle)) !== false) {

                $data = array_combine($header, $row);

                $bookingId = $data['booking_id'] ?? null;
                $userId = $data['user_id'] ?? null;
                $expectedLoanAdminFee = $data['expected_loan_admin_fee'] ?? null;
                $expectedDeposit = $data['expected_deposit'] ?? null;

                if (!$bookingId || !$userId) {
                    $skipped++;
                    continue;
                }

                $booking = DB::table('booking_payments')
                    ->where('bookingId', $bookingId)
                    //->where('userId', $userId)
                    ->first();

                if (!$booking) {
                    $this->warn("Not found: Booking {$bookingId} / User {$userId}");
                    $notFound++;
                    continue;
                }

                // Only update if different
                if (
                    $booking->loan_admin_fee != $expectedLoanAdminFee ||
                    $booking->deposit != $expectedDeposit
                ) {
                    if (true) {
                        DB::table('booking_payments')
                            ->where('bookingId', $bookingId)
                            // ->where('userId', $userId)
                            ->update([
                                'loan_admin_fee' => $expectedLoanAdminFee,
                                'deposit' => $expectedDeposit,
                                'updated_at' => now(),
                            ]);
                    }
                    // $this->info('bookingId ' . $bookingId);
                    // $this->info('userId ' . $userId);
                    // $this->info('loan_admin_fee ' . $expectedLoanAdminFee);
                    // $this->info('deposit ' . $expectedDeposit,);
                    // $this->info('loan_admin_fee ' . $booking->loan_admin_fee);
                    // $this->info('deposit ' . $booking->deposit);

                    $updated++;
                } else {
                    $this->info('bookingId ' . $bookingId);
                    $this->info('userId ' . $userId);
                    $this->info('loan_admin_fee ' . $expectedLoanAdminFee);
                    $this->info('deposit ' . $expectedDeposit,);
                    $this->info('loan_admin_fee ' . $booking->loan_admin_fee);
                    $this->info('deposit ' . $booking->deposit);
                    $skipped++;
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Error: " . $e->getMessage());
            return Command::FAILURE;
        }

        fclose($handle);

        $this->info("Done.");
        $this->line("Updated: {$updated}");
        $this->line("Skipped (no change): {$skipped}");
        $this->line("Not Found: {$notFound}");

        return Command::SUCCESS;
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Request;
use App\Models\UserLeaseToken;
use App\Models\User;
use App\Models\UserFiles;
use App\Models\Booking;
use App\Models\Property;
use App\Models\Residence;
use App\Models\Lookups;
use App\Models\StudentsInfo;
use App\Models\BookingPayment;
use App\Jobs\SendResendLeaseMail;
use Illuminate\Support\Facades\Log;

class TestLeaseGeneration extends Command
{
    protected $signature = 'command:test_lease_generation {userUid} {year}';
    // php artisan command:test_lease_generation 10773 2026
    protected $description = 'Test the lease resend + generation process manually';

    public function handle()
    {
        $userUid = $this->argument('userUid');


        $this->info("Testing lease generation for userUid: $userUid");

        // ==========================================================
        // 2. Determine year
        // ==========================================================
        $this_year = $this->argument('year');
        // $lookupYear = Lookups::where('group_id', '4')->first();
        // $this_year = $lookupYear ? $lookupYear->lookup_name : date("Y");

        $this->info("Lease year: $this_year");

        // ==========================================================
        // 1. Deactivate previous lease tokens
        // ==========================================================
        $this->info("Deactivating previous tokens...");

        $prevLeaseToken = UserLeaseToken::where("userUid", $userUid)
            ->where("active", 1)
            ->where("signed", 0)
            ->get();

        foreach ($prevLeaseToken as $token) {
            $token->update(["active" => 0]);
        }



        // ==========================================================
        // 3. Create New Lease Token
        // ==========================================================
        $this->info("Creating new lease token...");

        $leaseToken = UserLeaseToken::create([
            "userUid" => $userUid,
            "leaseToken" => $this->generateRandomLongString(),
            "active" => 1,
            "signed" => 0,
            "year" => $this_year
        ]);

        $lease_link = config('app.url') . "/sign-lease/" . $leaseToken->leaseToken;

        $this->info("Generated lease link:");
        $this->info($lease_link);

        // ==========================================================
        // 4. Test sending the email job
        // ==========================================================
        $this->info("Testing email dispatch...");

        $userUidData = User::where('id', $userUid)->first();

        if (!$userUidData) {
            $this->error("User record not found in users table!");
        } else {
            try {
                dispatch(new SendResendLeaseMail($userUidData, $leaseToken->leaseToken, $lease_link, $this_year));
                $this->info("Email job dispatched successfully!");
            } catch (\Exception $e) {
                $this->error("Email job failed: " . $e->getMessage());
            }
        }

        // ==========================================================
        // 5. Load student leasing data for PDF generation testing
        // ==========================================================
        $this->info("Checking leasing data integrity...");

        $student = StudentsInfo::where('userUid', $userUid)
            ->where('year', $this_year)
            ->first();

        if (!$student) {
            $this->error("❌ No student record found for the given year.");
        } else {
            $this->info("Student found: {$student->userFirstName} {$student->userLastname}");
        }

        $booking = Booking::where('userId', $userUid)
            ->where('year', $this_year)
            ->first();

        if (!$booking) {
            $this->error("❌ No booking found for this user.");
        } else {
            $this->info("Booking ID: {$booking->id}");
        }

        $this->info("Process Completed.");
        return Command::SUCCESS;
    }

    private function generateRandomLongString($length = 24)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        return substr(str_shuffle(str_repeat($characters, $length)), 0, $length);
    }
}

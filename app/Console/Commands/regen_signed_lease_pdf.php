<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\BookingPayment;
use App\Models\Lookups;
use App\Models\Property;
use App\Models\Residence;
use App\Models\StudentsInfo;
use App\Models\User;
use App\Models\UserLeaseToken;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PDF;

class regen_signed_lease_pdf extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:regen_signed_lease_pdf {userId} {year}';
    // Usage: php artisan command:regen_signed_lease_pdf 11117 2025

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Regenerate signed lease PDF for a given user and year';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $userUid = $this->argument('userId');
        $year = $this->argument('year');
        $signed_at = 'Regenerated via CLI';
        $role = 'student';

        if ($role = 'parent') {
            $lease_link = UserLeaseToken::where('userUid', $userUid)
                ->where('active', 0)
                ->where('signed', 1)
                ->where('role', 'student')
                ->where('year', $year)
                ->orderBy('created_at', 'desc')
                ->first();
            $lease_link_parent = UserLeaseToken::where('userUid', $userUid)
                ->where('active', 0)
                ->where('signed', 1)
                ->where('role', 'parent')
                ->where('year', $year)
                ->orderBy('created_at', 'desc')
                ->first();

            $this->info("Found lease linkId {$lease_link->id} and year {$year}");

            if (!$lease_link) {
                $this->error("No lease token found for userId {$userUid} and year {$year}");
                return Command::FAILURE;
            }

            $user = User::where('id', $lease_link->userUid)->first();
            $data = StudentsInfo::where('userUid', $lease_link->userUid)
                ->where('year', $lease_link->year)
                ->first();
            $booking = Booking::where('userId', $lease_link->userUid)
                ->where('year', $lease_link->year)
                ->first();

            if (!$booking) {
                $this->error("No booking found for user {$lease_link->userUid} and year {$lease_link->year}");
                return Command::FAILURE;
            }

            $propertyData = Property::where('id', $booking->property_data_id)->first();
            $residence = Residence::where('id', $propertyData->residence)->first();
            $leaseAdminFee = Lookups::where('group_id', '8')->first();

            $userIdentification = !empty($user->userId) ? $user->userId : $user->userPassport;
            // $signature = 'storage/files/' . $userIdentification . '/' . $lease_link->signature_url;
            // $this->info("Signature URL: {$signature}");
            // $owner = 'storage/leases/juanita-cronje.jpg';
            $signatureRelative  = 'storage/files/' . $userIdentification . '/' . $lease_link->signature_url;
            $ownerRelative      = 'storage/leases/juanita-cronje.jpg';
            $studentSignature = pdfImagePath($signatureRelative);
            $owner     = pdfImagePath($ownerRelative);
            $parentRelative      = 'storage/files/' . $userIdentification . '/' . $lease_link_parent->signature_url;
            $parentSignature = pdfImagePath($parentRelative);


            $paymentBooking = BookingPayment::where('bookingId', $booking->id)->first();

            // generate PDF
            $baseUrl = config('app.url') ?? 'https://campusafrica.co.za';
            $pdf = PDF::loadView('PDF.leaseSignParent', compact(
                'data',
                'propertyData',
                'residence',
                'booking',
                'leaseAdminFee',
                'signed_at',
                'studentSignature',
                'parentSignature',
                'signature',
                'owner',
                'paymentBooking'
            ));
            $bookingYear = substr($booking->year, -2);
            $bookingPrefix = Residence::where('id', $booking->property)->pluck('building_prefix')->first();
            $timestamp = date('YmdHis');
            $fileNameToSave = $bookingYear . $bookingPrefix . "_" .
                $booking->bed_code . "_" .
                $user->userLastname . "_" .
                $user->userFirstName .
                $timestamp . ".pdf";
        } else {
            $lease_link = UserLeaseToken::where('userUid', $userUid)
                ->where('active', 0)
                ->where('signed', 1)
                ->where('role', 'student')
                ->where('year', $year)
                ->orderBy('created_at', 'desc')
                ->first();

            $this->info("Found lease linkId {$lease_link->id} and year {$year}");

            if (!$lease_link) {
                $this->error("No lease token found for userId {$userUid} and year {$year}");
                return Command::FAILURE;
            }

            $user = User::where('id', $lease_link->userUid)->first();
            $data = StudentsInfo::where('userUid', $lease_link->userUid)
                ->where('year', $lease_link->year)
                ->first();
            $booking = Booking::where('userId', $lease_link->userUid)
                ->where('year', $lease_link->year)
                ->first();

            if (!$booking) {
                $this->error("No booking found for user {$lease_link->userUid} and year {$lease_link->year}");
                return Command::FAILURE;
            }

            $propertyData = Property::where('id', $booking->property_data_id)->first();
            $residence = Residence::where('id', $propertyData->residence)->first();
            $leaseAdminFee = Lookups::where('group_id', '8')->first();

            $userIdentification = !empty($user->userId) ? $user->userId : $user->userPassport;
            // $signature = 'storage/files/' . $userIdentification . '/' . $lease_link->signature_url;
            // $this->info("Signature URL: {$signature}");
            // $owner = 'storage/leases/juanita-cronje.jpg';
            $signatureRelative  = 'storage/files/' . $userIdentification . '/' . $lease_link->signature_url;
            $ownerRelative      = 'storage/leases/juanita-cronje.jpg';
            $signature = pdfImagePath($signatureRelative);
            $owner     = pdfImagePath($ownerRelative);


            $paymentBooking = BookingPayment::where('bookingId', $booking->id)->first();

            // generate PDF
            $baseUrl = config('app.url') ?? 'https://campusafrica.co.za';
            $pdf = PDF::loadView('PDF.leaseSign', compact(
                'data',
                'propertyData',
                'residence',
                'booking',
                'leaseAdminFee',
                'signed_at',
                'signature',
                'owner',
                'paymentBooking',
                'baseUrl'
            ));

            $bookingYear = substr($booking->year, -2);
            $bookingPrefix = Residence::where('id', $booking->property)->pluck('building_prefix')->first();
            $timestamp = date('YmdHis');
            $fileNameToSave = $bookingYear . $bookingPrefix . "_" .
                $booking->bed_code . "_" .
                $user->userLastname . "_" .
                $user->userFirstName .
                $timestamp . ".pdf";
        }


        $filePath = "public/files/leases/{$userIdentification}/{$fileNameToSave}";

        Storage::put($filePath, $pdf->output());

        $publicPath = Storage::url($filePath);

        // log + console output
        Log::info("Lease PDF regenerated for user {$userUid} ({$year}): {$publicPath}");
        $this->info("Lease PDF saved: {$publicPath}");

        return Command::SUCCESS;
    }
}

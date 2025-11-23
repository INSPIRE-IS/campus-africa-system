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

class TestLeasePDFGeneration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:regen_signed_lease_pdf {userId} {year}';
    // php artisan command:regen_signed_lease_pdf 17617 2025

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Regenerate signed lease PDF for a given user and year';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

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
        $studentRelative      = 'images/lease/test_student_001.png';
        $parentRelative      = 'images/lease/test_parent_001.png';
        $ownerRelative      = 'images/lease/juanita-cronje.jpg';
        try {
            if ($role == 'parent') {
                $this->info("parent lease");
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
                // $studentRelative  = 'storage/files/' . $userIdentification . '/' . $lease_link->signature_url;
                // $ownerRelative      = 'storage/leases/juanita-cronje.jpg';
                // app/helpers.php
                $studentSignature = pdfImagePath($studentRelative);
                $signature = pdfImagePath($studentRelative);
                $owner     = pdfImagePath($ownerRelative);
                //$parentRelative      = 'storage/files/' . $userIdentification . '/' . $lease_link_parent?->signature_url;
                $parentSignature = pdfImagePath($parentRelative);
                $parent = pdfImagePath($parentRelative);


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
                    'parent',
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


                if (!$lease_link) {
                    $this->error("No lease token found for userId {$userUid} and year {$year}");
                    return Command::FAILURE;
                }

                $this->info("Found lease linkId {$lease_link->id} and year {$year}");
                $user = User::where('id', $lease_link->userUid)->first();
                $data = StudentsInfo::where('userUid', $lease_link->userUid)
                    ->where('year', $lease_link->year)
                    ->first();
                if (!$user || !$data) {
                    $this->error("User or StudentsInfo record not found for userId {$userUid} and year {$year}");
                    return Command::FAILURE;
                }
                $this->info("here 1");
                $booking = Booking::where('userId', $lease_link->userUid)
                    ->where('year', $lease_link->year)
                    ->first();

                if (!$booking) {
                    $this->error("No booking found for user {$lease_link->userUid} and year {$lease_link->year}");
                    return Command::FAILURE;
                }
                $this->info("here 2");
                // property_data property_data_id
                $propertyData = Property::where('id', $booking->property_data_id)->first();
                $this->info("propertyData: " . $propertyData->id);
                // property property
                $residence = Residence::where('id', $booking->property)->first();
                $this->info("residence: " . $residence->id);
                $leaseAdminFee = Lookups::where('group_id', '8')->first();
                $this->info("leaseAdminFee: " . $leaseAdminFee->id);

                $userIdentification = !empty($user->userId) ? $user->userId : $user->userPassport;
                // $signature = 'storage/files/' . $userIdentification . '/' . $lease_link->signature_url;
                // $this->info("Signature URL: {$signature}");
                // $owner = 'storage/leases/juanita-cronje.jpg';
                $studentRelative  = 'storage/files/' . $userIdentification . '/' . $lease_link->signature_url;
                $ownerRelative      = 'storage/leases/juanita-cronje.jpg';
                $signature =  pdfImagePath($studentRelative); // 'storage/files/' . $userIdentification . '/' . $lease_link->signature_url;
                $this->info("Signature URL: {$signature}");
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
                    $data->userLastname . "_" .
                    $data->userFirstName .
                    $timestamp . ".pdf";
            }

            $content = $pdf->download($fileNameToSave)->getOriginalContent();
            $filePath = "public/files/leases/{$userIdentification}/{$fileNameToSave}";

            // Storage::put($filePath, $pdf->output());
            //file_put_contents($filePath, $content);
            //  \Storage::put($filePath, $content);
            Storage::disk('local')->put($filePath, $content);
            Log::info("Lease PDF regenerated for user {$userUid} ({$year}): {$filePath}");
            $this->info("Lease PDF saved: {$filePath}");

            // $publicPath = Storage::url($filePath);

            // // log + console output
            // Log::info("Lease PDF regenerated for user {$userUid} ({$year}): {$publicPath}");
            // $this->info("Lease PDF saved: {$publicPath}");

            return Command::SUCCESS;
        } catch (\Exception $e) {
            Log::error("Error in TestLeasePDFGeneration job", [
                'error' => $e->getMessage(),
                'user_id' => $userUid ?? null,
            ]);
            $this->error("Error in TestLeasePDFGeneration job");
            return Command::FAILURE;
        }
    }
}

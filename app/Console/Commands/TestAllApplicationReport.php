<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\Lookups;
use App\Models\OnlinePayments;
use App\Models\PropertyPermission;
use App\Models\Residence;
use App\Models\RoomTypes;
use App\Models\StudentsInfo;
use App\Models\User;
use App\Models\UserFiles;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class TestAllApplicationReport extends Command
{

    protected $signature = 'download:all_apps_report {year} {email}';
    // php artisan download:all_apps_report 2026 jason@inspireonline.co.za
    protected $description = 'Command description';


    public function handle()
    {
        ini_set('memory_limit', '1024M');

        $year = $this->argument('year') ?? date('Y');
        $email = $this->argument('email') ?? date('Y');

        // fiddle
        $userId = 39; // Auth::user()->id;
        $user = User::where('id', $userId)->first();


        $accessArray = PropertyPermission::where('userUid', $userId)
            ->pluck('property') // assuming this is the booking.property
            ->toArray();

        $allUsers = StudentsInfo::with('booking')
            ->when($year !== 'All', fn($q) => $q->where('year', $year))
            ->whereHas('booking', fn($q) => $q->whereIn('property', $accessArray))
            ->get();
        //dd($allUsers);
        // if ($year !== "All") {
        //     $allUsers = StudentsInfo::with('booking')->where('year', $year)->get();
        // } else {
        //     $allUsers = StudentsInfo::with('booking')->get();
        // }

        $users = [
            [
                '<b>Booking Ref</b>',
                '<b>Application Date</b>',
                '<b>User ID</b>',
                '<b>Student ID</b>',
                '<b>Booking Fee</b>',
                '<b>First Name</b>',
                '<b>Last Name</b>',
                '<b>Title</b>',
                '<b>DOB</b>',
                '<b>Cell</b>',
                '<b>Email</b>',
                '<b>Nationality</b>',
                '<b>I.D Number</b>',
                '<b>Passport</b>',
                '<b>Student Type</b>',
                '<b>Year</b>',
                '<b>Institution of Study</b>',
                '<b>Student Number</b>',
                '<b>Year of Study</b>',
                '<b>Faculty</b>',

                '<b>Booking Fee</b>',
                '<b>Payment Type</b>',
                '<b>Payment Reference</b>',
                '<b>Payment Date</b>',
                '<b>Table Used</b>',
                '<b>Copy Of ID</b>',
                '<b>Student Card</b>',
                '<b>Proof of Registration</b>',
                '<b>Funds</b>',
                '<b>Information</b>',
                '<b>Bed Assigned</b>',
                '<b>Other</b>',
                '<b>Application Approved</b>',
                '<b>Lease Signed</b>',
                '<b>Cancelled</b>',

                '<b>Residence</b>',
                '<b>Room Type</b>',
                '<b>Floor</b>',
                '<b>Unit</b>',
                '<b>Room</b>',
                '<b>Bed Code</b>',

                '<b>Burser Company Name</b>',
                '<b>Burser Registeration Number</b>',
                '<b>Burser Contact Person</b>',
                '<b>Burser Contact Number</b>',
                '<b>Burser Email</b>',
                '<b>Parent First Name</b>',
                '<b>Parent Last Name</b>',
                '<b>Parent Cellphone Number</b>',
                '<b>Parent Email</b>',
                '<b>Parent Nationality</b>',
                '<b>Parent I.D Number</b>',
                '<b>Parent Passport</b>',
                '<b>Parent Street No</b>',
                '<b>Parent Suburb</b>',
                '<b>Parent City</b>',
                '<b>Parent Postal Code</b>',
                '<b>Next of Kin First Name</b>',
                '<b>Next of Kin Last Name</b>',
                '<b>Next of Kin Title</b>',
                '<b>Next of Kin Relationship</b>',
                '<b>Next of Kin Nationality</b>',
                '<b>Next of Kin I.D Number</b>',
                '<b>Next of Kin Passport</b>',
                '<b>Next of Kin Cellphone Number</b>',
                '<b>Next of Kin Email</b>',
                '<b>Next of Kin Work Number</b>',
                '<b>Next of Kin Telephone Number</b>',
                '<b>Next of Kin Street No</b>',
                '<b>Next of Kin Suburb</b>',
                '<b>Next of Kin City</b>',
                '<b>Next of Kin Postal Code</b>',
            ]
        ];

        foreach ($allUsers as $user) {

            $lookupsInstit = Lookups::where('id', $user->institutionStudy)->first();
            $lookupsFaculty = Lookups::where('id', $user->faculty)->first();

            $booking = Booking::with('bookingPayment')->where('id', $user->bookingId)->first();

            if ($user->rob_10_fee == 1 && $user->rob_60_cancelled != 1) {
                $rob_10_fee = "Uploaded";
            } elseif ($user->rob_10_fee == 2 && $user->rob_60_cancelled != 1) {
                $rob_10_fee = "Approved";
            } else {
                $rob_10_fee = "";
            }

            if ($user->rob_15_id == 1 && $user->rob_60_cancelled != 1) {
                $rob_15_id = "Uploaded";
            } elseif ($user->rob_15_id == 2 && $user->rob_60_cancelled != 1) {
                $rob_15_id = "Approved";
            } else {
                $rob_15_id = "";
            }

            if ($user->rob_20_card == 1 && $user->rob_60_cancelled != 1) {
                $rob_20_card = "Uploaded";
            } elseif ($user->rob_20_card == 2 && $user->rob_60_cancelled != 1) {
                $rob_20_card = "Approved";
            } else {
                $rob_20_card = "";
            }

            if ($user->rob_25_reg == 1 && $user->rob_60_cancelled != 1) {
                $rob_25_reg = "Uploaded";
            } elseif ($user->rob_25_reg == 2 && $user->rob_60_cancelled != 1) {
                $rob_25_reg = "Approved";
            } else {
                $rob_25_reg = "";
            }

            if ($user->rob_30_funds == 1 && $user->rob_60_cancelled != 1) {
                $rob_30_funds = "Uploaded";
            } elseif ($user->rob_30_funds == 2 && $user->rob_60_cancelled != 1) {
                $rob_30_funds = "Approved";
            } else {
                $rob_30_funds = "";
            }

            if ($user->rob_35_info == 1 && $user->rob_60_cancelled != 1) {
                $rob_35_info = "Uploaded";
            } elseif ($user->rob_35_info == 2 && $user->rob_60_cancelled != 1) {
                $rob_35_info = "Approved";
            } else {
                $rob_35_info = "";
            }

            if ($user->rob_40_bed == 1 && $user->rob_60_cancelled != 1) {
                $rob_40_bed = "Uploaded";
            } elseif ($user->rob_40_bed == 2 && $user->rob_60_cancelled != 1) {
                $rob_40_bed = "Approved";
            } else {
                $rob_40_bed = "";
            }

            if ($user->rob_45_other == 1 && $user->rob_60_cancelled != 1) {
                $rob_45_other = "Uploaded";
            } elseif ($user->rob_45_other == 2 && $user->rob_60_cancelled != 1) {
                $rob_45_other = "Approved";
            } else {
                $rob_45_other = "";
            }

            if ($user->rob_50_approve == 1 && $user->rob_60_cancelled != 1) {
                $rob_50_approve = "Uploaded";
            } elseif ($user->rob_50_approve == 2 && $user->rob_60_cancelled != 1) {
                $rob_50_approve = "Approved";
            } else {
                $rob_50_approve = "";
            }

            if ($user->rob_55_lease == 1 && $user->rob_60_cancelled != 1) {
                $rob_55_lease = "Uploaded";
            } elseif ($user->rob_55_lease == 2 && $user->rob_60_cancelled != 1) {
                $rob_55_lease = "Approved";
            } else {
                $rob_55_lease = "";
            }

            if ($user->rob_60_cancelled != 1) {
                $cancelled = "No";
            } else {
                $cancelled = "Yes";
            }

            if ($booking) {
                $residence = Residence::where('id', $booking->property)->pluck('residence')->first();
                $roomType = RoomTypes::where('id', $booking->roomType)->pluck('name')->first();
                $payment_date = ""; // 
                $table_used = ""; // 
                if (($booking->payment_type == 'Credit/Debit Card' || $booking->payment_type == 'Credit$Debit Card' || $booking->payment_type == 'Online EFT')
                    && !empty($booking->booking_payment_ref)
                ) {
                    $onlinePayment = OnlinePayments::select('created_at')
                        ->where('userUid', $booking->userId)
                        ->where('reference', $booking->booking_payment_ref)
                        ->where('payment_status', 1)
                        ->orderBy('created_at', 'desc')->first();
                    $payment_date = $onlinePayment ? $onlinePayment->created_at : "";
                    $table_used = $onlinePayment ? "Online Payments" : "";
                }
                // 
                if (($booking->payment_type == 'Manual EFT' || $booking->payment_type == 'Online EFT')
                    && !empty($booking->booking_payment_ref)
                ) {
                    $pop = UserFiles::select('created_at')
                        ->where('userUid', $booking->userId)
                        ->where('fileId', 'proof_of_payment')
                        ->where('year', $booking->year)
                        ->orderBy('created_at', 'desc')->first();
                    $payment_date = $pop ? $pop->created_at : "";
                    $table_used = $pop ? "User Files" : "";
                }


                $floor = $booking->floor;
                $unit = $booking->unit;
                $room = $booking->room;
                $bed_code = $booking->bed_code;

                $booking_ref = $booking->booking_code;

                $booking_payment_type = $booking->payment_type;
                $booking_payment_ref = $booking->booking_payment_ref;
                $booking_date = $payment_date;
            } else {
                $residence = "";
                $roomType = "";
                $floor = "";
                $unit = "";
                $room = "";
                $bed_code = "";
                $booking_ref = "";
                $booking_payment_type = "";
                $booking_payment_ref = "";
            }


            $studentData = [
                $booking_ref,
                $user->created_at,
                $user->userUid,
                $user->id,
                $booking->bookingPayment->booking_fee,
                $user->userFirstName,
                $user->userLastname,
                $user->userTitle,
                $user->userDob,
                $user->userCell,
                $user->userEmail,
                $user->userNationality,
                $user->userId,
                $user->userPassport,
                $user->studentType,
                $user->year,
                $lookupsInstit->lookup_name,
                $user->studentNo,
                $user->yearOfStudy,
                $lookupsFaculty->lookup_name,
                $rob_10_fee,
                $booking_payment_type,
                $booking_payment_ref,
                $booking_date,
                $table_used,
                $rob_15_id,
                $rob_20_card,
                $rob_25_reg,
                $rob_30_funds,
                $rob_35_info,
                $rob_40_bed,
                $rob_45_other,
                $rob_50_approve,
                $rob_55_lease,
                $cancelled,

                $residence,
                $roomType,
                $floor,
                $unit,
                $room,
                $bed_code,

                $user->burCompanyName,
                $user->burRegNo,
                $user->burContactPerson,
                $user->burContactNo,
                $user->burEmail,
                $user->sponFirstName,
                $user->sponLastName,
                $user->sponCell,
                $user->sponEmail,
                $user->pNationality,
                $user->sponId,
                $user->pPassportNo,
                $user->pAddress1,
                $user->pAddress2,
                $user->pAddressCity,
                $user->pAddressPostal,
                $user->kinFirstName,
                $user->kinLastName,
                $user->kinTitle,
                $user->kinRelationship,
                $user->kinNationality,
                $user->kinId,
                $user->kinPassportNo,
                $user->kinCellNo,
                $user->kinEmail,
                $user->kinWorkNo,
                $user->kinTelNo,
                $user->kinAddressStr,
                $user->kinAddressSuburb,
                $user->kinAddressCity,
                $user->kinAddressPost
            ];

            array_push($users, $studentData);
        }


        $xlsx = new \Shuchkin\SimpleXLSXGen();
        $xlsx->addSheet($users, 'Students');
        // $xlsx->saveAs('All_Applications_' . $year . '_download.xlsx');

        $filename = "all_applications_report_{$year}_" . now()->format('Ymd_His') . ".xlsx";
        $savePath = storage_path("app/{$filename}");
        $xlsx->saveAs($savePath);

        $this->info("🎉 Report generated successfully: {$savePath}");

        // Public URL (if your 'storage/app' is NOT public, move file to 'storage/app/public')
        $publicUrl = "https://apply.campusafrica.co.za/{$filename}";

        // Mail::raw("Your XLSX report is ready for download: {$publicUrl}", function ($message) use ($email) {
        //     $message->to($email)->subject('Your XLSX Report');
        // });
        Mail::raw("Your XLSX report is attached.", function ($message) use ($email, $savePath, $filename) {
            $message->to($email)
                ->subject('Your XLSX Report')
                ->attach($savePath, [
                    'as' => $filename,
                    'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                ]);
        });
        $this->info("Download link sent to: {$email}");


        return 'All_Applications_' . $year . '_download.xlsx';


        return 0;
    }
}

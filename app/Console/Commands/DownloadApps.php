<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\Lookups;
use App\Models\OnlinePayments;
use App\Models\PropertyPermission;
use App\Models\Residence;
use App\Models\RoomTypes;
use App\Models\StudentsInfo;
use App\Models\UserFiles;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class DownloadApps extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'download:apps {year} {user_id} {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate applications XLSX and email a download link';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        ini_set('memory_limit', '512M');
        set_time_limit(0);

        $year = $this->argument('year');
        $userId = (int) $this->argument('user_id');
        $email = filter_var($this->argument('email'), FILTER_VALIDATE_EMAIL);

        if (!$email) {
            $this->error('Invalid email address.');
            return 1;
        }
        if ($year === 'All') {
            Mail::raw('Download failed: please select a specific year for the applications report.', function ($message) use ($email) {
                $message->to($email)->subject('Download failed');
            });
            return 0;
        }

        $accessArray = PropertyPermission::where('userUid', $userId)
            ->pluck('property')
            ->toArray();

        $allUsers = StudentsInfo::with('booking')
            ->when($year !== 'All', fn($q) => $q->where('year', $year))
            ->when(!empty($accessArray), fn($q) => $q->whereHas('booking', fn($q2) => $q2->whereIn('property', $accessArray)))
            ->get();

        if ($allUsers->isEmpty()) {
            Mail::raw('Download failed: no applications were found for the selected criteria.', function ($message) use ($email) {
                $message->to($email)->subject('Download failed');
            });
            return 0;
        }

        $users = [
            [
                'Booking Ref',
                'Application Date',
                'First Name',
                'Last Name',
                'Title',
                'DOB',
                'Cell',
                'Email',
                'Nationality',
                'I.D Number',
                'Passport',
                'Student Type',
                'Year',
                'Institution of Study',
                'Student Number',
                'Year of Study',
                'Faculty',
                'Booking Fee',
                'Payment Type',
                'Payment Reference',
                'Payment Date',
                'Table Used',
                'Copy Of ID',
                'Student Card',
                'Proof of Registration',
                'Funds',
                'Information',
                'Bed Assigned',
                'Other',
                'Application Approved',
                'Lease Signed',
                'Cancelled',
                'Residence',
                'Room Type',
                'Floor',
                'Unit',
                'Room',
                'Bed Code',
                'Burser Company Name',
                'Burser Registeration Number',
                'Burser Contact Person',
                'Burser Contact Number',
                'Burser Email',
                'Parent First Name',
                'Parent Last Name',
                'Parent Cellphone Number',
                'Parent Email',
                'Parent Nationality',
                'Parent I.D Number',
                'Parent Passport',
                'Parent Street No',
                'Parent Suburb',
                'Parent City',
                'Parent Postal Code',
                'Next of Kin First Name',
                'Next of Kin Last Name',
                'Next of Kin Title',
                'Next of Kin Relationship',
                'Next of Kin Nationality',
                'Next of Kin I.D Number',
                'Next of Kin Passport',
                'Next of Kin Cellphone Number',
                'Next of Kin Email',
                'Next of Kin Work Number',
                'Next of Kin Telephone Number',
                'Next of Kin Street No',
                'Next of Kin Suburb',
                'Next of Kin City',
                'Next of Kin Postal Code',
            ],
        ];

        foreach ($allUsers as $user) {
            $lookupsInstit = Lookups::where('id', $user->institutionStudy)->first();
            $lookupsFaculty = Lookups::where('id', $user->faculty)->first();
            $booking = Booking::where('id', $user->bookingId)->first();

            $rob_10_fee = $this->robStatus($user->rob_10_fee, $user->rob_60_cancelled);
            $rob_15_id = $this->robStatus($user->rob_15_id, $user->rob_60_cancelled);
            $rob_20_card = $this->robStatus($user->rob_20_card, $user->rob_60_cancelled);
            $rob_25_reg = $this->robStatus($user->rob_25_reg, $user->rob_60_cancelled);
            $rob_30_funds = $this->robStatus($user->rob_30_funds, $user->rob_60_cancelled);
            $rob_35_info = $this->robStatus($user->rob_35_info, $user->rob_60_cancelled);
            $rob_40_bed = $this->robStatus($user->rob_40_bed, $user->rob_60_cancelled);
            $rob_45_other = $this->robStatus($user->rob_45_other, $user->rob_60_cancelled);
            $rob_50_approve = $this->robStatus($user->rob_50_approve, $user->rob_60_cancelled);
            $rob_55_lease = $this->robStatus($user->rob_55_lease, $user->rob_60_cancelled);

            $cancelled = $user->rob_60_cancelled != 1 ? 'No' : 'Yes';

            if ($booking) {
                $residence = Residence::where('id', $booking->property)->pluck('residence')->first();
                $roomType = RoomTypes::where('id', $booking->roomType)->pluck('name')->first();
                $payment_date = '';
                $table_used = '';

                if (
                    ($booking->payment_type == 'Credit/Debit Card' || $booking->payment_type == 'Credit$Debit Card' || $booking->payment_type == 'Online EFT')
                    && !empty($booking->booking_payment_ref)
                ) {
                    $onlinePayment = OnlinePayments::select('created_at')
                        ->where('userUid', $booking->userId)
                        ->where('reference', $booking->booking_payment_ref)
                        ->where('payment_status', 1)
                        ->orderBy('created_at', 'desc')
                        ->first();
                    $payment_date = $onlinePayment ? $onlinePayment->created_at : '';
                    $table_used = $onlinePayment ? 'Online Payments' : '';
                }

                if (
                    ($booking->payment_type == 'Manual EFT' || $booking->payment_type == 'Online EFT')
                    && !empty($booking->booking_payment_ref)
                ) {
                    $pop = UserFiles::select('created_at')
                        ->where('userUid', $booking->userId)
                        ->where('fileId', 'proof_of_payment')
                        ->where('year', $booking->year)
                        ->orderBy('created_at', 'desc')
                        ->first();
                    $payment_date = $pop ? $pop->created_at : '';
                    $table_used = $pop ? 'User Files' : '';
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
                $residence = '';
                $roomType = '';
                $floor = '';
                $unit = '';
                $room = '';
                $bed_code = '';
                $booking_ref = '';
                $booking_payment_type = '';
                $booking_payment_ref = '';
                $booking_date = '';
                $table_used = '';
            }

            $studentData = [
                $booking_ref,
                $user->created_at,
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
                $lookupsInstit ? $lookupsInstit->lookup_name : '',
                $user->studentNo,
                $user->yearOfStudy,
                $lookupsFaculty ? $lookupsFaculty->lookup_name : '',
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
                $user->kinAddressPost,
            ];

            $users[] = $studentData;
        }

        $timestamp = now()->format('Ymd_His');
        $safeYear = Str::of($year)->replace([' ', '/'], '_');
        $fileName = "All_Applications_{$safeYear}_{$timestamp}.xlsx";
        $filePath = storage_path('app/public/' . $fileName);

        $xlsx = new \Shuchkin\SimpleXLSXGen();
        $xlsx->addSheet($users, 'Students');
        $xlsx->saveAs($filePath);

        if (file_exists($filePath)) {
            Mail::raw("Applications file created - https://apply.campusafrica.co.za/storage/{$fileName}", function ($message) use ($email) {
                $message->to($email)->subject('Applications download');
            });
            return 0;
        }

        Mail::raw('Download failed: unable to create the applications file.', function ($message) use ($email) {
            $message->to($email)->subject('Download failed');
        });

        return 1;
    }

    private function robStatus($value, $cancelled)
    {
        if ((string) $cancelled === '1') {
            return '';
        }

        if ((string) $value === '1') {
            return 'Uploaded';
        }

        if ((string) $value === '2') {
            return 'Approved';
        }

        return '';
    }
}

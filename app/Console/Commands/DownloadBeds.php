<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\BookingPayment;
use App\Models\Lookups;
use App\Models\PropertyPermission;
use App\Models\Residence;
use App\Models\RoomTypes;
use App\Models\StudentsInfo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class DownloadBeds extends Command
{
    protected $signature = 'download:beds {year} {property} {user_id} {email}';

    protected $description = 'Generate Building Manager bed allocation XLSX and email a download link';

    public function handle()
    {
        ini_set('memory_limit', '512M');
        set_time_limit(0);

        $year = (string) $this->argument('year');
        $propertyId = (string) $this->argument('property');
        $userId = (int) $this->argument('user_id');
        $email = filter_var($this->argument('email'), FILTER_VALIDATE_EMAIL);

        if (!$email) {
            $this->error('Invalid email address.');
            return 1;
        }

        if (!Lookups::isInternalYear($year)) {
            $this->emailFailure($email, 'Download failed: invalid report year selected.');
            return 0;
        }

        $allowedProperties = PropertyPermission::where('userUid', $userId)
            ->pluck('property')
            ->map(function ($property) {
                return (string) $property;
            })
            ->toArray();

        if (empty($allowedProperties)) {
            $this->emailFailure($email, 'Download failed: no property permissions were found for your user.');
            return 0;
        }

        if ($propertyId !== 'All' && !in_array($propertyId, $allowedProperties)) {
            $this->emailFailure($email, 'Download failed: you do not have permission to export the selected property.');
            return 0;
        }

        $exportPropertyIds = $propertyId === 'All' ? $allowedProperties : [$propertyId];

        $beds = [$this->bedHeader()];
        $waitlist = [$this->waitlistHeader()];

        $waitlistBookings = Booking::where('year', $year)
            ->whereNull('property_data_id')
            ->whereIn('property', $exportPropertyIds)
            ->orderBy('id', 'asc')
            ->get();

        foreach ($waitlistBookings as $booking) {
            $user = StudentsInfo::where('bookingId', $booking->id)
                ->where('year', $year)
                ->where('rob_10_fee', '2')
                ->first();

            if (!$user || (int) $user->rob_60_cancelled === 1) {
                continue;
            }

            $waitlist[] = $this->studentRow($booking, $user);
        }

        $allBeds = DB::table('property_data as pd')
            ->leftJoin('property as p', 'pd.residence', '=', 'p.id')
            ->leftJoin('room_types as r', 'pd.room_type', '=', 'r.id')
            ->select(
                'pd.id',
                'p.residence',
                'r.name',
                'pd.meta_info_1',
                'pd.meta_info_2',
                'pd.floor',
                'pd.unit',
                'pd.room',
                'pd.bed',
                'pd.bed_code',
                'pd.inventory',
                'pd.year',
                'pd.year_end'
            )
            ->whereIn('pd.residence', $exportPropertyIds)
            ->where(function ($query) use ($year) {
                $query->where('pd.year_end', '0')
                    ->orWhere('pd.year_end', $year);
            })
            ->whereNull('pd.deleted_at')
            ->orderBy('pd.residence')
            ->orderBy('pd.meta_info_1')
            ->orderByRaw('pd.floor + 0 ASC')
            ->orderBy('pd.floor')
            ->orderByRaw('pd.unit + 0 ASC')
            ->orderBy('pd.unit')
            ->orderBy('pd.room')
            ->orderBy('pd.bed')
            ->get();

        foreach ($allBeds as $bed) {
            $booking = Booking::where('property_data_id', $bed->id)
                ->where('year', $year)
                ->first();

            $user = $booking
                ? StudentsInfo::where('bookingId', $booking->id)->where('year', $year)->first()
                : null;

            $bookingData = $booking && $user
                ? $this->studentExportData($booking, $user)
                : $this->emptyStudentExportData();

            $beds[] = array_merge([
                $bed->residence,
                $bed->meta_info_1,
                $bed->meta_info_2,
                $bed->floor,
                $bed->unit,
                $bed->room,
                $bed->bed,
                $bed->bed_code,
                $bed->name,
                $bookingData['leaseRate'],
                (string) $bed->inventory === '1' ? 'Yes' : 'No',
            ], $bookingData['studentColumns']);
        }

        $propertyName = $propertyId === 'All'
            ? 'All'
            : Residence::where('id', $propertyId)->pluck('residence')->first();

        $timestamp = now()->format('Ymd_His');
        $safeProperty = Str::of($propertyName ?: 'Property')->replace([' ', '/', '\\'], '_');
        $fileName = "Bed_Allocations_Export_{$year}_{$safeProperty}_{$timestamp}.xlsx";
        $filePath = storage_path('app/public/' . $fileName);

        $xlsx = new \Shuchkin\SimpleXLSXGen();
        $xlsx->addSheet($beds, 'BED CODES');
        $xlsx->addSheet($waitlist, 'WAITLIST');
        $xlsx->saveAs($filePath);

        if (file_exists($filePath)) {
            Mail::raw("Beds report created - https://apply.campusafrica.co.za/storage/{$fileName}", function ($message) use ($email) {
                $message->to($email)->subject('Beds report download');
            });

            return 0;
        }

        $this->emailFailure($email, 'Download failed: unable to create the beds report file.');
        return 1;
    }

    private function bedHeader(): array
    {
        return [
            '<b>RESIDENCE</b>',
            '<b>FLOOR PREFIX</b>',
            '<b>BED CODE PREFIX</b>',
            '<b>FLOOR</b>',
            '<b>UNIT</b>',
            '<b>ROOM</b>',
            '<b>BED</b>',
            '<b>BED CODE</b>',
            '<b>ROOM TYPE</b>',
            '<b>LEASE RATE (MONTHLY)</b>',
            '<b>INVENTORY</b>',
            '<b>BOOKING REF</b>',
            '<b>FIRST NAME</b>',
            '<b>LAST NAME</b>',
            '<b>EMAIL</b>',
            '<b>CELL NUMBER</b>',
            '<b>NATIONALITY</b>',
            '<b>ID NUMBER / PASSPORT</b>',
            '<b>GENDER</b>',
            '<b>STUDENT TYPE</b>',
            '<b>BURSAR COMPANY</b>',
            '<b>INSTITUTION</b>',
            '<b>STUDENT NUMBER</b>',
            '<b>YEAR OF STUDY</b>',
            '<b>FACULTY</b>',
            '<b>NEXT OF KIN NAME</b>',
            '<b>NEXT OF KIN CONTACT</b>',
            '<b>NEXT OF KIN RELATIONSHIP</b>',
        ];
    }

    private function waitlistHeader(): array
    {
        return array_values(array_diff($this->bedHeader(), [
            '<b>FLOOR PREFIX</b>',
            '<b>BED CODE PREFIX</b>',
            '<b>FLOOR</b>',
            '<b>UNIT</b>',
            '<b>ROOM</b>',
            '<b>BED</b>',
            '<b>BED CODE</b>',
            '<b>INVENTORY</b>',
        ]));
    }

    private function studentRow(Booking $booking, StudentsInfo $user): array
    {
        $propertyName = Residence::where('id', $booking->property)->pluck('residence')->first();
        $room = RoomTypes::where('id', $booking->roomType)->pluck('name')->first();
        $data = $this->studentExportData($booking, $user);

        return array_merge([
            $propertyName,
            $room,
            $data['leaseRate'],
        ], $data['studentColumns']);
    }

    private function studentExportData(Booking $booking, StudentsInfo $user): array
    {
        $paymentBooking = BookingPayment::where('bookingId', $booking->id)->first();
        $leaseRate = $paymentBooking ? $paymentBooking->monthly_rental : '';
        $nationality = $user->userNationality ?? '';
        $gender = '';

        if ($nationality == 'South Africa' && $user->userId != '' && floatval(substr($user->userId, 6, 1)) >= 0 && floatval(substr($user->userId, 6, 1)) <= 4) {
            $gender = 'Female';
        } elseif ($nationality == 'South Africa' && $user->userId != '' && floatval(substr($user->userId, 6, 1)) >= 5 && floatval(substr($user->userId, 6, 1)) <= 9) {
            $gender = 'Male';
        }

        $studentType = $user->studentType == 'Cash / Direct' ? 'Cash' : $user->studentType;
        $bursarCompany = $user->studentType == 'Bursary' ? $user->burCompanyName : '';
        $instit = Lookups::where('id', $user->institutionStudy)->pluck('lookup_name')->first();
        $faculty = Lookups::where('id', $user->faculty)->pluck('lookup_name')->first();

        return [
            'leaseRate' => $leaseRate,
            'studentColumns' => [
                $booking->booking_code,
                $user->userFirstName ?? '',
                $user->userLastname ?? '',
                $user->userEmail ?? '',
                ($user->userCellCode ?? '') . ($user->userCell ?? ''),
                $nationality,
                isset($user->userId) ? ($user->userId != '' ? $user->userId : $user->userPassport) : '',
                $gender,
                $studentType,
                $bursarCompany,
                $instit,
                $user->studentNo,
                $user->yearOfStudy,
                $faculty,
                trim(($user->kinFirstName ?? '') . ' ' . ($user->kinLastName ?? $user->kinLastname ?? '')),
                ($user->kinCellCode ?? $user->kincellCode ?? '') . ($user->kinCellNo ?? ''),
                $user->kinRelationship,
            ],
        ];
    }

    private function emptyStudentExportData(): array
    {
        return [
            'leaseRate' => '',
            'studentColumns' => array_fill(0, 17, ''),
        ];
    }

    private function emailFailure(string $email, string $message): void
    {
        Mail::raw($message, function ($mail) use ($email) {
            $mail->to($email)->subject('Beds report download failed');
        });
    }
}

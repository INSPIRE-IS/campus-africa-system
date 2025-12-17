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
use DateInterval;
use DatePeriod;
use DateTime;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PDF;


class TestQuoteInvoicePDFGeneration extends Command
{

    protected $signature = 'test:generate-invoice  {userId} {year}';
    // php artisan test:generate-invoice 5602 2026
    // php artisan test:generate-invoice 16994 2026
    // php artisan test:generate-invoice 16584 2026
    // php artisan test:generate-invoice 8388 2026

    protected $description = 'Command description';

    public function handle()
    {

        if (true) {
            $userId = $this->argument('userId');
            $this_year = $this->argument('year');

            $studentsInfo = StudentsInfo::where('userUid', $userId)
                ->where('year', $this_year)
                ->first();

            $booking = Booking::where('id', $studentsInfo->bookingId)->first();
            $property = Residence::where('id', $booking->property)->first();
            $bookingPayment = BookingPayment::where('bookingId', $booking->id)->first();

            $user = $studentsInfo;

            $userIdentification = $studentsInfo->userId ?: $studentsInfo->userPassport;

            // Fees
            $leaseFee = $bookingPayment->loan_admin_fee;
            $deposit = $bookingPayment->deposit;

            if ($bookingPayment->rob_loan_waived == '2') {
                $leaseFee = 0;
            }
            if ($bookingPayment->rob_deposit_waived == '2') {
                $deposit = 0;
            }

            /*
            |--------------------------------------------------------------------------
            | Build invoice items
            |--------------------------------------------------------------------------
            */
            $items = [];

            $items[] = [
                'date' => '',
                'description' => 'Lease Fee',
                'amount' => $leaseFee,
            ];

            $period = new DatePeriod(
                new DateTime($booking->lease_from_date),
                new DateInterval('P1M'),
                (new DateTime($booking->lease_to_date))->modify('+1 month')
            );

            foreach ($period as $date) {
                $items[] = [
                    'date' => $date->format('Y-m-d'),
                    'description' => 'Rental for ' . $date->format('F'),
                    'amount' => $bookingPayment->funded_monthly,
                ];
            }

            $totalAmount = collect($items)->sum('amount');

            // Invoice meta
            $invoiceNumber = $user->studentNo . '-' . date('Ymd');
            $invoiceDate = date('Y/m/d');
            if ($studentsInfo->studentType == 'Bursary') {
                $billTo = [
                    'name' => $studentsInfo->burCompanyName,
                    'regNo' => $studentsInfo->burRegNo,
                    'contactName' => $studentsInfo->burContactPerson,
                    'contactEmail' => $studentsInfo->burEmail,
                    'contactPhone' => $studentsInfo->burContactCode . ' ' . $studentsInfo->burContactNo,
                ];
                $billAddress = [
                    'address1' => $studentsInfo->bAddress1,
                    'address2' => $studentsInfo->bAddress2,
                    'addressCity' => $studentsInfo->bAddressCity,
                    'addressPostal' => $studentsInfo->bAddressPostal,
                ];
            } else if ($studentsInfo->studentType == 'Cash / Direct') {
                $billTo = [
                    'name' => '',
                    'regNo' => '',
                    'contactName' => $studentsInfo->sponFirstName . ' ' . $studentsInfo->sponLastName,
                    'contactEmail' => $studentsInfo->sponEmail,
                    'contactPhone' => $studentsInfo->sponCellCode . ' ' . $studentsInfo->sponCell,
                ];
                $billAddress = [
                    'address1' => $studentsInfo->pAddress1,
                    'address2' => $studentsInfo->pAddress2,
                    'addressCity' => $studentsInfo->pAddressCity,
                    'addressPostal' => $studentsInfo->pAddressPostal,
                ];
            } else {
                $billTo = [
                    'name' => '',
                    'regNo' => '',
                    'contactName' => '',
                    'contactEmail' => '',
                    'contactPhone' => '',
                ];
                $billAddress = [
                    'address1' => '',
                    'address2' => '',
                    'addressCity' => '',
                    'addressPostal' => '',
                ];
            }




            /*
            |--------------------------------------------------------------------------
            | Generate PDF
            |--------------------------------------------------------------------------
            */
            $pdf = PDF::loadView(
                'PDF.invoice',
                compact(
                    'user',
                    'booking',
                    'property',
                    'bookingPayment',
                    'items',
                    'totalAmount',
                    'invoiceNumber',
                    'invoiceDate',
                    'billTo',
                    'billAddress'
                )
            );

            $content = $pdf->output();

            $randomNumber = rand(1000, 9999);
            $new_name = $randomNumber . '_invoice.pdf';

            $location = 'public/files/' . $userIdentification . '/' . $new_name;

            \Storage::put($location, $content);

            $this->info("Invoice PDF saved: {$location}");
        }

        if (false) {
            $userId = $this->argument('userId');
            $this_year = $this->argument('year');
            $studentsInfo = StudentsInfo::where('userUid', $userId)->where('year', $this_year)->first();
            $booking = Booking::where('id', $studentsInfo->bookingId)->first();
            $property = Residence::where('id', $booking->property)->first();
            $bookingPayment = BookingPayment::where('bookingId', $booking->id)->first();

            if ($studentsInfo->userId == "") {
                $userIdentification = $studentsInfo->userPassport;
            } else {
                $userIdentification = $studentsInfo->userId;
            }

            $leaseFee = $bookingPayment->loan_admin_fee;
            $deposit = $bookingPayment->deposit;

            if ($bookingPayment->rob_loan_waived == '2') {
                $leaseFee = 0;
            }
            if ($bookingPayment->rob_deposit_waived == '2') {
                $deposit = 0;
            }
            $user = $studentsInfo;

            $pdf = PDF::loadView('PDF.quote', compact('user', 'booking', 'property', 'bookingPayment', 'leaseFee', 'deposit'));
            $content = $pdf->download('test')->getOriginalContent();
            $dateNow = date('Y-m-d');
            $new_name = $dateNow . '_quote.pdf';
            // dd($new_name);
            $location = 'public/files/' . $userIdentification . '/' . $new_name;
            $this->info("Invoice PDF saved: {$location}");
            \Storage::put($location, $content);

            // Build items (same logic as controller)

            // $pdf = PDF::loadView('PDF.invoice', compact(
            //     'user',
            //     'booking',
            //     'bookingPayment',
            //     'leaseFee',
            //     'deposit',
            //     'items'
            // ));

            // $pdf->save(storage_path('app/test-invoice.pdf'));
        }


        $this->info('invoice generated.');
    }
}

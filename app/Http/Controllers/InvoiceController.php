<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lookups;
use App\Models\User;
use App\Models\Booking;
use App\Models\BookingPayment;
use App\Models\Residence;
use App\Models\RoomTypes;
use PDF;
use Illuminate\Support\Facades\Storage;
use App\Models\StudentsInfo;
use DateInterval;
use DatePeriod;
use DateTime;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class InvoiceController extends Controller
{
    public function generateQuote(Request $request)
    {


        $session_year = Session::get('year');
        if (!empty($session_year)) {
            $this_year = Lookups::where('group_id', '4')->where('lookup_name', '=', $session_year)->first();
            $this_year = $this_year->lookup_name;
        } else {
            $this_year = Lookups::where('group_id', '4')->first();
            $this_year = $this_year->lookup_name;
        }
        $userId = $request->userId;
        if (false) {
            $user = StudentsInfo::where('userUid', $userId)->where('year', $this_year)->first();
            $booking = Booking::where('id', $request->bookingId)->first();
            $property = Residence::where('id', $booking->property)->first();
            $bookingPayment = BookingPayment::where('bookingId', $booking->id)->first();

            if ($user->userId == "") {
                $userIdentification = $user->userPassport;
            } else {
                $userIdentification = $user->userId;
            }

            $leaseFee = $bookingPayment->loan_admin_fee;
            $deposit = $bookingPayment->deposit;

            if ($bookingPayment->rob_loan_waived == '2') {
                $leaseFee = 0;
            }
            if ($bookingPayment->rob_deposit_waived == '2') {
                $deposit = 0;
            }

            $pdf = PDF::loadView('PDF.quote', compact('user', 'booking', 'property', 'bookingPayment', 'leaseFee', 'deposit'));
            $content = $pdf->download('test')->getOriginalContent();
            $new_name = $userIdentification . '_quote.pdf';
            // dd($new_name);
            \Storage::put('public/files/' . $userIdentification . '/' . $new_name, $content);
            return $pdf->download($new_name);
        }

        if (true) {

            $user = StudentsInfo::where('userUid', $userId)->where('year', $this_year)->first();
            if (!$user) {
                Log::error("No students info found for userId {$userId} and year {$this_year}");
            }
            Log::info("Students info found for userId {$userId} and year {$this_year}");
            Log::info("Students info: " . json_encode($user));
            $booking = Booking::where('id', $user->bookingId)->first();
            $property = Residence::where('id', $booking->property)->first();
            $bookingPayment = BookingPayment::where('bookingId', $booking->id)->first();

            $userIdentification = $user->userId ?: $user->userPassport;

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
            $deposit = $bookingPayment->deposit;

            if ($bookingPayment->rob_loan_waived == '2') {
                $leaseFee = 0;
            }
            if ($bookingPayment->rob_deposit_waived == '2') {
                $deposit = 0;
            }
            $items[] = [
                'date' => '',
                'description' => 'Deposit',
                'amount' => $deposit,
            ];


            $period = new DatePeriod(
                new DateTime($booking->lease_from_date),
                new DateInterval('P1M'),
                (new DateTime($booking->lease_to_date)) // ->modify('+1 month')
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
            if ($user->studentType == 'Bursary' || $user->studentType == 'NSFAS' || $user->studentType == 'FUNSA' || $user->studentType == 'GCRA' || $user->studentType == 'MQA') {
                switch ($user->studentType) {
                    case 'NSFAS':
                        $billTo = [
                            'name' => 'NSFAS',
                            'regNo' => '',
                            'contactName' => 'University of the Witwatersrand, Johannesburg c/o Financial aid and Scholarship Office',
                            'contactEmail' => 'Mogopa.Ngcobo@gauteng.gov.za',
                            'contactPhone' => '+27113556532',
                        ];
                        $billAddress = [
                            'address1' => 'Ground Floor-Solomon Mahlangu House',
                            'address2' => '1 Jorissen Street',
                            'addressCity' => 'Braamfontein',
                            'addressPostal' => '2017',
                        ];
                        break;
                    case 'FUNSA':
                        $billTo = [
                            'name' => 'NSFAS',
                            'regNo' => '',
                            'contactName' => 'University of the Witwatersrand, Johannesburg c/o Financial aid and Scholarship Office',
                            'contactEmail' => 'Mogopa.Ngcobo@gauteng.gov.za',
                            'contactPhone' => '+27113556532',
                        ];
                        $billAddress = [
                            'address1' => 'Ground Floor-Solomon Mahlangu House',
                            'address2' => '1 Jorissen Street',
                            'addressCity' => 'Braamfontein',
                            'addressPostal' => '2017',
                        ];
                        break;
                    case 'GCRA':
                        $billTo = [
                            'name' => 'GCRA',
                            'regNo' => '',
                            'contactName' => 'Mogopa Ngcobo',
                            'contactEmail' => 'Mogopa.Ngcobo@gauteng.gov.za',
                            'contactPhone' => '+27113556532',
                        ];
                        $billAddress = [
                            'address1' => '17 Simmonds Street',
                            'address2' => 'Johannesburg',
                            'addressCity' => 'Johannesburg',
                            'addressPostal' => '2001',
                        ];
                        break;
                    case 'NRF':
                        $billTo = [
                            'name' => 'NRF',
                            'regNo' => '',
                            'contactName' => 'National Research Foundation',
                            'contactEmail' => '',
                            'contactPhone' => '',
                        ];
                        $billAddress = [
                            'address1' => '1 Jan Smuts Ave',
                            'address2' => 'Braamfontein',
                            'addressCity' => 'Johannesburg',
                            'addressPostal' => '1828',
                        ];
                        break;
                    case 'MQA':
                        $billTo = [
                            'name' => 'Mining Qualifications Authority',
                            'regNo' => '',
                            'contactName' => 'Felicitus Lebetha',
                            'contactEmail' => 'Felicitusl@mqa.org.za',
                            'contactPhone' => '+27115472674',
                        ];
                        $billAddress = [
                            'address1' => '7 Anerley Road',
                            'address2' => 'Parktown',
                            'addressCity' => 'Johannesburg',
                            'addressPostal' => '2193',
                        ];
                        break;
                    default:
                        $billTo = [
                            'name' => $user->burCompanyName,
                            'regNo' => $user->burRegNo,
                            'contactName' => $user->burContactPerson,
                            'contactEmail' => $user->burEmail,
                            'contactPhone' => $user->burContactCode . ' ' . $user->burContactNo,
                        ];
                        $billAddress = [
                            'address1' => $user->bAddress1,
                            'address2' => $user->bAddress2,
                            'addressCity' => $user->bAddressCity,
                            'addressPostal' => $user->bAddressPostal,
                        ];
                        break;
                }
            } else if ($user->studentType == 'Cash / Direct') {
                $billTo = [
                    'name' => '',
                    'regNo' => '',
                    'contactName' => $user->sponFirstName . ' ' . $user->sponLastName,
                    'contactEmail' => $user->sponEmail,
                    'contactPhone' => $user->sponCellCode . ' ' . $user->sponCell,
                ];
                $billAddress = [
                    'address1' => $user->pAddress1,
                    'address2' => $user->pAddress2,
                    'addressCity' => $user->pAddressCity,
                    'addressPostal' => $user->pAddressPostal,
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
            /** @disregard P1009 */
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

            // $randomNumber = rand(1000, 9999);
            // $new_name = $randomNumber . '_invoice.pdf';

            // $content = $pdf->download('test')->getOriginalContent();
            $new_name = $userIdentification . '_quote.pdf';

            $location = 'public/files/' . $userIdentification . '/' . $new_name;

            \Storage::put($location, $content);

            Log::info("Invoice PDF saved: {$location}");

            return response($content, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $new_name . '"',
                'Content-Length' => strlen($content),
            ]);
        }
    }

    public function generateInvoice(Request $request)
    {
        $session_year = Session::get('year');
        if (!empty($session_year)) {
            $this_year = Lookups::where('group_id', '4')->where('lookup_name', '=', $session_year)->first();
            $this_year = $this_year->lookup_name;
        } else {
            $this_year = Lookups::where('group_id', '4')->first();
            $this_year = $this_year->lookup_name;
        }
        $userId = $request->userId;
        if (false) {
            $user = StudentsInfo::where('userUid', $userId)->where('year', $this_year)->first();
            $booking = Booking::where('id', $request->bookingId)->first();
            $property = Residence::where('id', $booking->property)->first();
            $bookingPayment = BookingPayment::where('bookingId', $booking->id)->first();

            if ($user->userId == "") {
                $userIdentification = $user->userPassport;
            } else {
                $userIdentification = $user->userId;
            }

            $leaseFee = $bookingPayment->loan_admin_fee;
            $deposit = $bookingPayment->deposit;

            if ($bookingPayment->rob_loan_waived == '2') {
                $leaseFee = 0;
            }
            if ($bookingPayment->rob_deposit_waived == '2') {
                $deposit = 0;
            }

            $pdf = PDF::loadView('PDF.quote', compact('user', 'booking', 'property', 'bookingPayment', 'leaseFee', 'deposit'));
            $content = $pdf->download('test')->getOriginalContent();
            $new_name = $userIdentification . '_quote.pdf';
            // dd($new_name);
            \Storage::put('public/files/' . $userIdentification . '/' . $new_name, $content);
            return $pdf->download($new_name);
        }
        if (true) {

            $user = StudentsInfo::where('userUid', $userId)->where('year', $this_year)->first();
            if (!$user) {
                Log::error("No students info found for userId {$userId} and year {$this_year}");
            }
            Log::info("Students info found for userId {$userId} and year {$this_year}");
            Log::info("Students info: " . json_encode($user));
            $booking = Booking::where('id', $user->bookingId)->first();
            $property = Residence::where('id', $booking->property)->first();
            $bookingPayment = BookingPayment::where('bookingId', $booking->id)->first();

            $userIdentification = $user->userId ?: $user->userPassport;

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
            $deposit = $bookingPayment->deposit;

            if ($bookingPayment->rob_loan_waived == '2') {
                $leaseFee = 0;
            }
            if ($bookingPayment->rob_deposit_waived == '2') {
                $deposit = 0;
            }
            $items[] = [
                'date' => '',
                'description' => 'Deposit',
                'amount' => $deposit,
            ];


            $period = new DatePeriod(
                new DateTime($booking->lease_from_date),
                new DateInterval('P1M'),
                (new DateTime($booking->lease_to_date)) // ->modify('+1 month')
            );

            foreach ($period as $date) {
                $items[] = [
                    'date' => $date->format('Y-m-d'),
                    'description' => 'Rental for ' . $date->format('F'),
                    'amount' => $bookingPayment->monthly_rental,
                ];
            }

            $totalAmount = collect($items)->sum('amount');

            // Invoice meta
            $invoiceNumber = $user->studentNo . '-' . date('Ymd');
            $invoiceDate = date('Y/m/d');
            if ($user->studentType == 'Bursary' || $user->studentType == 'NSFAS' || $user->studentType == 'FUNSA' || $user->studentType == 'GCRA' || $user->studentType == 'MQA') {
                switch ($user->studentType) {
                    case 'NSFAS':
                        $billTo = [
                            'name' => 'NSFAS',
                            'regNo' => '',
                            'contactName' => 'University of the Witwatersrand, Johannesburg c/o Financial aid and Scholarship Office',
                            'contactEmail' => 'Mogopa.Ngcobo@gauteng.gov.za',
                            'contactPhone' => '+27113556532',
                        ];
                        $billAddress = [
                            'address1' => 'Ground Floor-Solomon Mahlangu House',
                            'address2' => '1 Jorissen Street',
                            'addressCity' => 'Braamfontein',
                            'addressPostal' => '2017',
                        ];
                        break;
                    case 'FUNSA':
                        $billTo = [
                            'name' => 'NSFAS',
                            'regNo' => '',
                            'contactName' => 'University of the Witwatersrand, Johannesburg c/o Financial aid and Scholarship Office',
                            'contactEmail' => 'Mogopa.Ngcobo@gauteng.gov.za',
                            'contactPhone' => '+27113556532',
                        ];
                        $billAddress = [
                            'address1' => 'Ground Floor-Solomon Mahlangu House',
                            'address2' => '1 Jorissen Street',
                            'addressCity' => 'Braamfontein',
                            'addressPostal' => '2017',
                        ];
                        break;
                    case 'NRF':
                        $billTo = [
                            'name' => 'NRF',
                            'regNo' => '',
                            'contactName' => 'National Research Foundation',
                            'contactEmail' => '',
                            'contactPhone' => '',
                        ];
                        $billAddress = [
                            'address1' => '1 Jan Smuts Ave',
                            'address2' => 'Braamfontein',
                            'addressCity' => 'Johannesburg',
                            'addressPostal' => '1828',
                        ];
                        break;
                    case 'GCRA':
                        $billTo = [
                            'name' => 'GCRA',
                            'regNo' => '',
                            'contactName' => 'Mogopa Ngcobo',
                            'contactEmail' => 'Mogopa.Ngcobo@gauteng.gov.za',
                            'contactPhone' => '+27113556532',
                        ];
                        $billAddress = [
                            'address1' => '17 Simmonds Street',
                            'address2' => 'Johannesburg',
                            'addressCity' => 'Johannesburg',
                            'addressPostal' => '2001',
                        ];
                        break;
                    case 'MQA':
                        $billTo = [
                            'name' => 'Mining Qualifications Authority',
                            'regNo' => '',
                            'contactName' => 'Felicitus Lebetha',
                            'contactEmail' => 'Felicitusl@mqa.org.za',
                            'contactPhone' => '+27115472674',
                        ];
                        $billAddress = [
                            'address1' => '7 Anerley Road',
                            'address2' => 'Parktown',
                            'addressCity' => 'Johannesburg',
                            'addressPostal' => '2193',
                        ];
                        break;
                    default:
                        $billTo = [
                            'name' => $user->burCompanyName,
                            'regNo' => $user->burRegNo,
                            'contactName' => $user->burContactPerson,
                            'contactEmail' => $user->burEmail,
                            'contactPhone' => $user->burContactCode . ' ' . $user->burContactNo,
                        ];
                        $billAddress = [
                            'address1' => $user->bAddress1,
                            'address2' => $user->bAddress2,
                            'addressCity' => $user->bAddressCity,
                            'addressPostal' => $user->bAddressPostal,
                        ];
                        break;
                }
            } else if ($user->studentType == 'Cash / Direct') {
                $billTo = [
                    'name' => '',
                    'regNo' => '',
                    'contactName' => $user->sponFirstName . ' ' . $user->sponLastName,
                    'contactEmail' => $user->sponEmail,
                    'contactPhone' => $user->sponCellCode . ' ' . $user->sponCell,
                ];
                $billAddress = [
                    'address1' => $user->pAddress1,
                    'address2' => $user->pAddress2,
                    'addressCity' => $user->pAddressCity,
                    'addressPostal' => $user->pAddressPostal,
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
            /** @disregard P1009 */
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

            // $randomNumber = rand(1000, 9999);
            // $new_name = $randomNumber . '_invoice.pdf';

            // $content = $pdf->download('test')->getOriginalContent();
            $new_name = $userIdentification . '_invoice.pdf';

            $location = 'public/files/' . $userIdentification . '/' . $new_name;

            \Storage::put($location, $content);

            Log::info("Invoice PDF saved: {$location}");

            return response($content, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $new_name . '"',
                'Content-Length' => strlen($content),
            ]);
        }
    }
}

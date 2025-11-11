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
use Storage;
use App\Models\StudentsInfo;
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

        $user = StudentsInfo::where('userUid', $request->userId)->where('year', $this_year)->first();
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

        $user = StudentsInfo::where('userUid', $request->userId)->where('year', $this_year)->first();
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

        $pdf = PDF::loadView('PDF.invoice', compact('user', 'booking', 'property', 'bookingPayment', 'leaseFee', 'deposit'));
        $content = $pdf->download('test')->getOriginalContent();
        $new_name = $userIdentification . '_invoice.pdf';

        \Storage::put('public/files/' . $userIdentification . '/' . $new_name, $content);
        return $pdf->download($new_name);
    }
}

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

class InvoiceController extends Controller
{
    public function generate(Request $request){

        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        $user = StudentsInfo::where('userUid', $request->userId)->where('year', $this_year)->first();
        $booking = Booking::where('id', $request->bookingId)->first();
        $property = Residence::where('id', $booking->property)->first();
        $bookingPayment = BookingPayment::where('bookingId', $booking->id)->first();

        if($user->userId == ""){
            $userIdentification = $user->userPassport;
        }else{
            $userIdentification = $user->userId;
        }

        $pdf = PDF::loadView('PDF.invoice', compact('user', 'booking', 'property', 'bookingPayment'));
        $content = $pdf->download('test')->getOriginalContent();
        $new_name = $userIdentification . '_invoice.pdf';

        \Storage::put('public/files/'.$userIdentification.'/'.$new_name, $content);
        

    }
}

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

class InvoiceController extends Controller
{
    public function generate(Request $request){

        // $user = User::where('id', $request->userId)->first();
        // $booking = Booking::where('id', $request->bookingId)->first();
        // dd($request->userId);
        // $property = Residence::where('id', $booking->property)->pluck('residence')->first();
        // $roomType = RoomTypes::where('id', $booking->roomType)->pluck('name')->first();

        $pdf = PDF::loadView('PDF.invoice');
        $content = $pdf->download('test')->getOriginalContent();

        \Storage::put('public/test.pdf', $content);
        

    }
}

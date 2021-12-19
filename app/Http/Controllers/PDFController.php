<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Booking;
use App\Models\Property;
use App\Models\Residence;
use App\Models\ActionList;
use PDF;
use Auth;
use App\Models\UserFiles;

class PDFController extends Controller
{


    public function createPDF(Request $request, $studentId) {

        $bookingId = $request->bookingId;
        $property = $request->property;
        $roomType = $request->roomType;

      if(!$request->type){
        

        $oldData = Booking::where('userId', $bookingId)->first();
        $newData = ['property|Property' => $property, 'roomType|Room Type' => $roomType, "floor|Floor" => $request->floor, "unit|Unit" => $request->unit, "room|Room" => $request->room, "bed|Bed" => $request->bed, "bed_code|Bed Code"=> $request->bed_code, "lease_from_date|Lease From Date" => $request->lease_from_date, "lease_from_time|Lease From Time" => $request->lease_from_time, "lease_to_date|Lease To Date" => $request->lease_to_date, "lease_to_time|Lease To Time" => $request->lease_to_time];

        $propertyOld = Property::where('user_id_room', $bookingId)->update(["user_id_room" => null, "room_available" => null]);

        Property::where('residence', $property)->where('room_type', $roomType)->where("floor", $request->floor)->where('unit', $request->unit)->where('room', $request->room)->where('bed', $request->bed)->where('bed_code', $request->bed_code)->update(["room_available" => "1", "user_id_room"=>$bookingId]);

        $propertyData = Property::where('residence', $property)->where('room_type', $roomType)->where("floor", $request->floor)->where('unit', $request->unit)->where('room', $request->room)->where('bed', $request->bed)->where('bed_code', $request->bed_code)->first();
        


        foreach($newData as $key => $new){
            
            $info = explode("|", $key);
            $key = $info[0];
            $changedField = $info[1];

            if($oldData->$key !== $new){
                if($oldData->$key == "" || $oldData->$key == null){
                    $oldData->$key = "Unassigned";
                }
                ActionList::create([
                    "userUid" => $request->bookingId,
                    "action" => "Updated ".$changedField." From ".$oldData->$key." to ".$new,
                    "actionUserUid" => Auth::user()->id,
                    "actionUserDisplayName" => Auth::user()->userFirstName,
                    "actionUserRole" => Auth::user()->userRole,
                ]);
            }
        }

        ActionList::create([
          "userUid" => $request->bookingId,
          "action" => "Generated a Lease",
          "actionUserUid" => Auth::user()->id,
          "actionUserDisplayName" => Auth::user()->userFirstName,
          "actionUserRole" => Auth::user()->userRole,
        ]);

        Booking::where('userId', $bookingId)->update(["property" => $property, "roomType" => $roomType, "floor" => $request->floor, "unit" => $request->unit, "room" => $request->room, "bed" => $request->bed, "bed_code"=> $request->bed_code, "property_data_id" => $propertyData->id, "lease_from_date" => $request->lease_from_date, "lease_from_time"=> $request->lease_from_time, "lease_to_date" => $request->lease_to_date, "lease_to_time" => $request->lease_to_time]);

      }else{

        // retreive all records from db
        $data = User::where('id', $studentId)->first();
        $booking = Booking::where('userId', $studentId)->first();
        $propertyData = Property::where('id', $booking->property_data_id)->first();
        $residence = Residence::where('residence', $propertyData->residence)->first();

        // share data to view
        $pdf = PDF::loadView('PDF.leases', compact('data', 'propertyData', 'residence', 'booking'));
  
        // download PDF file with download method
        // return $pdf->download('lease.pdf');

        $content = $pdf->download('lease.pdf')->getOriginalContent();
        $user = User::where('id', $bookingId)->first();

        \Storage::put('public/files/'.$user->userId.'/Lease.pdf',$content) ;

      }
        

        return 'true';
      }
}

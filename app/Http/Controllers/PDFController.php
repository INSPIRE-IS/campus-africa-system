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
use App\Models\Lookups;
use App\Models\UserLeaseToken;
use App\Models\MailerList;
use Mail;
use Grosv\LaravelPasswordlessLogin\LoginUrl;

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

        if($roomType == "Single"){
          $roomType = "Single Room";
        }

        $propertyData = Property::where('residence', $property)->where('room_type', $roomType)->where("floor", $request->floor)->where('unit', $request->unit)->where('room', $request->room)->where('bed', $request->bed)->where('bed_code', $request->bed_code)->first();

        $leaseAdminFee = Lookups::where('group_id', '8')->first();
        


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

        Booking::where('userId', $bookingId)->update(["property" => $property, "roomType" => $roomType, "floor" => $request->floor, "unit" => $request->unit, "room" => $request->room, "bed" => $request->bed, "bed_code"=> $request->bed_code, "property_data_id" => $propertyData->id, "lease_from_date" => $request->lease_from_date, "lease_from_time"=> $request->lease_from_time, "lease_to_date" => $request->lease_to_date, "lease_to_time" => $request->lease_to_time, "leaseAdminFee" => $leaseAdminFee]);

      }else{

        // retreive all records from db
        $data = User::where('id', $studentId)->first();
        $booking = Booking::where('userId', $studentId)->first();
        $propertyData = Property::where('id', $booking->property_data_id)->first();
        $residence = Residence::where('id', $propertyData->residence)->first();
        $leaseAdminFee = Lookups::where('group_id', '8')->first();
        $owner = storage_path().'/leases/kim_degenaar.jpeg';

        // share data to view
        $pdf = PDF::loadView('PDF.leases', compact('data', 'propertyData', 'residence', 'booking', 'leaseAdminFee', 'owner'));
  
        // download PDF file with download method
        // return $pdf->download('lease.pdf');

        if($data->userId == ""){
            $userIdentification = $data->userPassport;
        }else{
            $userIdentification = $data->userId;
        }

        $bookingYear = substr($booking->year, -2);
        $bookingPrefix = Residence::where('id', $booking->property)->pluck('building_prefix')->first();

        $fileNameToSave = $bookingYear.$bookingPrefix."_".$booking->bed_code."_".$data->userLastname."_".$data->userFirstName.".pdf";
        // $fileNameToSave = 'lease.pdf';

        $content = $pdf->download($fileNameToSave)->getOriginalContent();
        $user = User::where('id', $studentId)->first();

        \Storage::put('public/files/'.$userIdentification.'/'.$fileNameToSave, $content);

        //Check if there is an existing lease to overwrite
        $lease = UserFiles::where('fileId', 'moveIn')->where('userUid', $studentId)->first();        

        $data = array(
            'userUid' => $studentId,
            'fileName' => $fileNameToSave,
            'fileUrl' => 'files/'.$userIdentification.'/'.$fileNameToSave,
            'status' => 'Rejected',
            'uploaded_by' => Auth::user()->name,
            'created_at' => date('Y-m-d h:m:s')
        );

        if (!empty($lease)) {
          UserFiles::where('fileId', 'moveIn')->where('userUid', $studentId)->update($data);
          $action_type = "Regenerated Lease";
          
        } else {
            $data['fileId'] = 'moveIn';    
            UserFiles::insert($data);
            $action_type = "Generated Lease";
        }

        ActionList::create([
          "userUid" => $studentId,
          "action" => $action_type,
          "actionUserUid" => Auth::user()->id,
          "actionUserDisplayName" => Auth::user()->userFirstName,
          "actionUserRole" => Auth::user()->userRole,
      ]);

        User::where('id', $studentId)->update(['rob_55_lease' => 0]);


        // Create Lease Link
        $leaseToken = UserLeaseToken::create(["userUid" => $studentId, "leaseToken" => $this->generateRandomLongString(), "active" => 1, "signed" => 0]);
        $userForLease = User::where('id', $studentId)->first();

        $message_replace['booking_application'] = "#".$booking->booking_code;
        $message_replace['name'] = $userForLease->userFirstName;
        $message_replace['lease_link'] = "<a href='https://".$_SERVER['HTTP_HOST']."/sign-lease/".$leaseToken->leaseToken."' target='_blank'>";
        $message_replace['close_lease_link']="</a>";

        $mailerForMessage = MailerList::where("mailer_name", "Application Approved")->first();

        $mailerMessage = $mailerForMessage->message;

        $newMessage = str_replace("[booking_application]", $message_replace['booking_application'], $mailerMessage);
        $newMessage = str_replace("[name]", $message_replace['name'], $newMessage);
        $newMessage = str_replace("[lease_link]", $message_replace['lease_link'], $newMessage);
        $newMessage = str_replace("[close_lease_link]", $message_replace['close_lease_link'], $newMessage);

        $data['messageForMail'] = $newMessage;
        $data['email'] = $userForLease->userEmail;
        $data['name'] = $userForLease->userFirstName;
        $data['subject'] = str_replace("[application_number]", $message_replace['bookingRef'], $mailerForMessage->subject);

        if(explode("@", $data['email'])[1] == "conurban.co.za"){
            $data['email'] = $userForLease->userEmail;
        }else{
            $data['email'] = "conurbandigital@gmail.com";
        }

        Mail::send('email.green_back', $data, function($message) use ($data) {
            // $message->to($data['email'], $data['name'])->subject('Application Approved');
            $message->to($data['email'])->subject($data['subject']);
            $message->cc(['conurbandigital@gmail.com', 'roger@whitehart.co.za', 'mirichea@whitehart.co.za']);
            $message->from('noreply@campusafrica.co.za','Campus Africa');
            // $message->cc(['campusafricaforms@gmail.com', 'roger@whitehart.co.za']);
        });

      }
        

        return [UserFiles::where('fileId', 'moveIn')->where('userUid', $studentId)->first(), User::where('id', $studentId)->first()];
      }

      public function generateRandomLongString($length = 24) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}

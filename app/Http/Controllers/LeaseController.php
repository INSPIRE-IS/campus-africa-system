<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserLeaseToken;
use App\Models\User;
use App\Models\UserFiles;
use App\Models\Booking;
use App\Models\Property;
use App\Models\Residence;
use App\Models\Lookups;
use PDF;
use Auth;
use Mail;
use App\Models\MailerList;
use Grosv\LaravelPasswordlessLogin\LoginUrl;
use App\Models\StudentsInfo;

class LeaseController extends Controller
{
    public function view_sign_lease(Request $request, $lease_link_url){
        
        $lease_link = UserLeaseToken::where('leaseToken', $lease_link_url)->where('active', '1')->first();

        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        if($lease_link && !empty($lease_link)){
            // $user = User::where('id', $lease_link->userUid)->first();
            $user = StudentsInfo::where('userUid', $lease_link->userUid)->where('year', $this_year)->first();
            $file = UserFiles::where('userUid', $lease_link->userUid)->where('fileId', 'moveIn')->where('year', $this_year)->first();

            // $data = User::where('id', $lease_link->userUid)->first();
            $data = StudentsInfo::where('userUid', $lease_link->userUid)->where('year', $this_year)->first();
            $booking = Booking::where('userId', $lease_link->userUid)->where('year', $this_year)->first();
            $propertyData = Property::where('id', $booking->property_data_id)->first();
            $residence = Residence::where('id', $propertyData->residence)->first();
            $leaseAdminFee = Lookups::where('group_id', '8')->first();
            $owner = storage_path().'/leases/kim_degenaar.jpeg';

            $pdf = view('PDF.leasesForScreen', compact('data', 'propertyData', 'residence', 'booking', 'leaseAdminFee', 'owner'));

            return view('outSession.signLease', ["headerTitle" => "Sign Your Lease", "userCustomId" => $lease_link_url, "user" => $user, "file" => $file, "pdf" => $pdf]);
        }else{
            return redirect('/dashboard');
        }

    }

    public function save_signed_lease(Request $request, $lease_link){

        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        $lease_link = UserLeaseToken::where('leaseToken', $lease_link)->where('active', '1')->first();
        // $user = User::where('id', $lease_link->userUid)->first();
        $user = StudentsInfo::where('userUid', $lease_link->userUid)->where('year', $this_year)->first();
        $booking = Booking::where('userId', $lease_link->userUid)->where('year', $this_year)->first();
        
        // get the base64 string
        $data = $request->signature_string;

        // clean any symbols to decode
        list($type, $data) = explode(';', $data);
        list(,$extension) = explode('/',$type);
        list(,$data)      = explode(',', $data);

        // decode the string to the correct format
        $data = base64_decode($data);

        if($user->userId == ""){
            $userIdentification = $user->userPassport;
        }else{
            $userIdentification = $user->userId;
        }


        // generate a random name
        $new_name = $userIdentification . '_' .strtotime("now").'_'.substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyz', ceil(20/strlen($x)) )),1,20).'.'.$extension;


        file_put_contents(storage_path().'/app/public/files/'.$userIdentification.'/'.$new_name, $data);

        //generate the signed lease

        // retreive all records from db
        // $data = User::where('id', $user->userUid)->first();
        $data = StudentsInfo::where('userUid', $lease_link->userUid)->where('year', $this_year)->first();
        $booking = Booking::where('userId', $user->userUid)->where('year', $this_year)->first();
        $propertyData = Property::where('id', $booking->property_data_id)->first();
        $residence = Residence::where('id', $propertyData->residence)->first();
        $leaseAdminFee = Lookups::where('group_id', '8')->first();
        $signed_at = $request->signed_at;
        $signature = 'storage/files/'.$userIdentification.'/'.$new_name;
        $owner = 'storage/leases/kim_degenaar.jpeg';

        // share data to view
        $pdf = PDF::loadView('PDF.leaseSign', compact('data', 'propertyData', 'residence', 'booking', 'leaseAdminFee', 'signed_at', 'signature', 'owner'));
  
        // download PDF file with download method
        // return $pdf->download('lease.pdf');

        $bookingYear = substr($booking->year, -2);
        $bookingPrefix = Residence::where('id', $booking->property)->pluck('building_prefix')->first();

        $fileNameToSave = $bookingYear.$bookingPrefix."_".$booking->bed_code."_".$user->userLastname."_".$user->userFirstName.".pdf";

        $content = $pdf->download($fileNameToSave)->getOriginalContent();
        // $user = User::where('id', $user->id)->first();
        $user = StudentsInfo::where('userUid', $lease_link->userUid)->where('year', $this_year)->first();

        \Storage::put('public/files/'.$userIdentification.'/'.$fileNameToSave, $content);
      
        //Data to update
        $data = array(
            'fileName' => $fileNameToSave,
            'fileUrl' => 'files/'.$userIdentification.'/'.$fileNameToSave,
            'status' => 'Uploaded',
        );

        UserFiles::where('fileId', 'moveIn')->where('userUid', $user->userUid)->where('year', $this_year)->update($data);

        // User::where('id', $user->id)->update(['rob_55_lease' => 1]);
        StudentsInfo::where('userUid', $lease_link->userUid)->where('year', $this_year)->update(['rob_55_lease' => 1]);

        //Update Token to signed & inactive
        UserLeaseToken::where('leaseToken', $lease_link->leaseToken)->update(['active' => 0, 'signed' => 1, 'signature_url' => $new_name]);

        return 'Completed';

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

    public function resendLease(Request $request){

        $prevLeaseToken = UserLeaseToken::where("userUid", $request->userId)->where("active", 1)->where("signed", 0)->get();

        if(!empty($prevLeaseToken)){
            foreach($prevLeaseToken as $token){
                UserLeaseToken::where('id', $token->id)->update(["active" => 0]);
            }
        }

        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        // Create Lease Link
        $leaseToken = UserLeaseToken::create(["userUid" => $request->userId, "leaseToken" => $this->generateRandomLongString(), "active" => 1, "signed" => 0]);
        $userForLease = StudentsInfo::where('userUid', $request->userId)->where('year', $this_year)->first();
        $booking = Booking::where('userId', $request->userId)->where('year', $this_year)->first();

        $message_replace['booking_application'] = "#".$booking->booking_code;
        $message_replace['name'] = $userForLease->userFirstName;
        $message_replace['lease_link'] = "<a href='https://".$_SERVER['HTTP_HOST']."/sign-lease/".$leaseToken->leaseToken."' target='_blank'>";
        $message_replace['close_lease_link']="</a>";

        $mailerForMessage = MailerList::where("mailer_name", "Application Approved")->first();

        if($mailerForMessage->status == '1'){

            $mailerMessage = $mailerForMessage->message;
    
            $newMessage = str_replace("[booking_application]", $message_replace['booking_application'], $mailerMessage);
            $newMessage = str_replace("[name]", $message_replace['name'], $newMessage);
            $newMessage = str_replace("[lease_link]", $message_replace['lease_link'], $newMessage);
            $newMessage = str_replace("[close_lease_link]", $message_replace['close_lease_link'], $newMessage);
    
            $data['messageForMail'] = $newMessage;
            $data['email'] = $userForLease->userEmail;
            $data['name'] = $userForLease->userFirstName;
            $data['subject'] = str_replace("[application_number]", $booking->booking_code, $mailerForMessage->subject);

           

            $allowedMailsCC = Lookups::where('group_id', '11')->pluck('lookup_name')->first();
            $data['cc'] = explode(";", $allowedMailsCC);

            $allowedMails = Lookups::where('group_id', '10')->pluck('lookup_name')->first();

            if(!empty($allowedMails)){

                $mailArr = explode(";", $allowedMails);
    
                if(in_array(explode("@", $data['email'])[1], $mailArr)){
                    $data['email'] = $userForLease->userEmail;
                }else{
                    $data['email'] = "conurbandigital@gmail.com";
                }
            }else{
                $data['email'] = $userForLease->userEmail;
            }
    
            // if(explode("@", $data['email'])[1] == "conurban.co.za"){
            //     $data['email'] = $userForLease->userEmail;
            // }else{
            //     $data['email'] = "conurbandigital@gmail.com";
            // }
    
            Mail::send('email.green_back', $data, function($message) use ($data) {
                $message->to($data['email'])->subject($data['subject']);
                $message->bcc($data['cc']);
                // $message->cc(['conurbandigital@gmail.com', 'roger@whitehart.co.za']);
                $message->from('noreply@campusafrica.co.za','Campus Africa');
            });
        }

    }
}

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

class LeaseController extends Controller
{
    public function view_sign_lease(Request $request, $lease_link_url){
        
        $lease_link = UserLeaseToken::where('leaseToken', $lease_link_url)->where('active', '1')->first();

        if($lease_link && !empty($lease_link)){
            $user = User::where('id', $lease_link->userUid)->first();
            $file = UserFiles::where('userUid', $lease_link->userUid)->where('fileId', 'moveIn')->first();

            return view('outSession.signLease', ["userCustomId" => $lease_link_url, "user" => $user, "file" => $file]);
        }else{
            return "Finished signing.";
        }

    }

    public function save_signed_lease(Request $request, $lease_link){

        $lease_link = UserLeaseToken::where('leaseToken', $lease_link)->where('active', '1')->first();
        $user = User::where('id', $lease_link->userUid)->first();
        
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
        $data = User::where('id', $user->id)->first();
        $booking = Booking::where('userId', $user->id)->first();
        $propertyData = Property::where('id', $booking->property_data_id)->first();
        $residence = Residence::where('residence', $propertyData->residence)->first();
        $leaseAdminFee = Lookups::where('group_id', '8')->first();
        $signed_at = $request->signed_at;
        $signature = 'storage/files/'.$userIdentification.'/'.$new_name;
        $owner = 'storage/leases/kim_degenaar.jpeg';

        // share data to view
        $pdf = PDF::loadView('PDF.leaseSign', compact('data', 'propertyData', 'residence', 'booking', 'leaseAdminFee', 'signed_at', 'signature', 'owner'));
  
        // download PDF file with download method
        // return $pdf->download('lease.pdf');

        $content = $pdf->download('lease_Signed.pdf')->getOriginalContent();
        $user = User::where('id', $user->id)->first();

        \Storage::put('public/files/'.$userIdentification.'/lease_Signed.pdf', $content);
      
        //Data to update
        $data = array(
            'fileName' => 'lease_Signed.pdf',
            'fileUrl' => 'files/'.$userIdentification.'/lease_Signed.pdf',
            'status' => 'Uploaded',
        );

        UserFiles::where('fileId', 'moveIn')->where('userUid', $user->id)->update($data);

        User::where('id', $user->id)->update(['rob_55_lease' => 1]);

        //Update Token to signed & inactive
        UserLeaseToken::where('leaseToken', $lease_link->leaseToken)->update(['active' => 0, 'signed' => 1, 'signature_url' => $new_name]);

        return 'Completed';

    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lookups;
use App\Models\User;
use App\Models\ApplicationToken;
use App\Models\UserFiles;
use App\Models\ActionList;
use App\Models\Booking;
use Auth;
use Illuminate\Support\Facades\Hash;
use Jenssegers\Agent\Agent;
use Grosv\LaravelPasswordlessLogin\LoginUrl;

class ApplicationController extends Controller
{

    public function step_1(Request $request, $property, $roomType){

        $lookups = Lookups::get();
        $agent = new Agent();

        // dd($agent);

        return view('application.step_1', ["lookups" => $lookups, "property" => $property, "roomType" => $roomType, "agent" => $agent]);
    }

    public function step_verify(Request $request, $applicationToken){

        return view('application.verify_user', ["applicationToken" => $applicationToken]);
    }

    public function veryfiy_user(Request $request){
        $lookups = Lookups::get();

        $application = ApplicationToken::where('applicationToken', $request->applicationToken)->first();

        if($request->security_code == $application->security_token){
            ApplicationToken::where('applicationToken', $request->applicationToken)->update(['verified' => 1]);
            User::where('id', $application->userUid)->update(['email_verified' => 1]);
            $user = User::where('id', $application->userUid)->first();

            $generator = new LoginUrl($user);
            $generator->setRedirectUrl('/verify-password');
            $url = $generator->generate();
            
            return ["success", $url];
        }else{
            return ["failed"];
        }
    }

    public function email_check_idNo(Request $request){
        $email = $request->email;
        $idNumber = $request->idNumber;

        $emailValid = true;
        $idNumberValid = true;

        if($email !== ""){
            $emailCheck = User::where('email', $email)->first();

            if(!empty($emailCheck)){
                $emailValid = false;
            }
        }

        if($idNumber !== ""){
            $idCheck = User::where('userId', $idNumber)->first();

            if(!empty($idCheck)){
                $idNumberValid = false;
            }
        }

        return ["done", $emailValid, $idNumberValid];
    }

    public function step_2(Request $request){
        $lookups = Lookups::get();

        // $application = ApplicationToken::where('applicationToken', $applicationToken)->first();
        $user = User::where('id', Auth::user()->id)->first();

        // if(!empty($user)){
        //     ApplicationToken::where('applicationToken', $applicationToken)->update(['verified' => 1]);
        // }

        $agent = new Agent();

        if($user->studentType == "Cash / Direct")
        {
            return view('application.step_2_spon', ["lookups" => $lookups, "user" => $user, "agent" => $agent]);
        }
        elseif($user->studentType == "Bursary")
        {
            return view('application.step_2_bur', ["lookups" => $lookups, "user" => $user, "agent" => $agent]);
        }
        else
        {
            return view('application.step_3', ["lookups" => $lookups, "user" => $user, "agent" => $agent]);
        } 
    }

    public function step_2_spon(Request $request){
        
        // $application = ApplicationToken::where('applicationToken', $applicationToken)->first();
        $user = User::where('id', Auth::user()->id)->first();

        if($request->app_address_p == 1){
            $request->pAddress1 = $user->userAddress1;
            $request->pAddress2 = $user->userAddress2;
            $request->pAddressCity = $user->userAddressCity;
            $request->pAddressPostal = $user->userAddressPostal;
        }

        User::where('id', $user->id)->update([
            'sponFirstName' => $request->pFirstName,
            'sponLastName' => $request->pLastName,
            'sponCell' => $request->pCell_no,
            'sponEmail' => $request->pEmail,
            'pNationality' => $request->pNationality,
            'sponId' => $request->pIdNumber,
            'pPassportNo' => $request->pPassportNo,
            'pAddress1' => $request->pAddress1,
            'pAddress2' => $request->pAddress2,
            'pAddressCity' => $request->pAddressCity,
            'pAddressPostal' => $request->pAddressPostal,
            'app_address_p' => $request->app_address_p,
        ]);

        return "/step-3/online-application";
    }

    public function step_2_bur(Request $request){

        // $application = ApplicationToken::where('applicationToken', $applicationToken)->first();
        $user = User::where('id', Auth::user()->id)->first();

        User::where('id', $user->id)->update([
            'burCompanyName' => $request->bCompanyName,
            'burRegNo' => $request->bRegistrationNo,
            'burContactPerson' => $request->bContactPerson,
            'burContactNo' => $request->bContactNumber,
            'burEmail' => $request->bEmail,
            'bAddress1' => $request->bAddress1,
            'bAddress2' => $request->bAddress2,
            'bAddressCity' => $request->bAddressCity,
            'bAddressPostal' => $request->bAddressPostal,
            'bPayment' => $request->bPayment,
            'bint' => $request->bint,
            'bIntName' => $request->bIntName,
        ]);

        return "/step-3/online-application";
    }

    public function step_3(Request $request){
        $lookups = Lookups::get();

        // $application = ApplicationToken::where('applicationToken', $applicationToken)->first();
        $user = User::where('id', Auth::user()->id)->first();

        return view('application.step_3', ["lookups" => $lookups, "user" => $user]);
    }

    public function step_3_kin(Request $request){
        // $application = ApplicationToken::where('applicationToken', $applicationToken)->first();
        $user = User::where('id', Auth::user()->id)->first();

        if($request->app_parent_kin == 1){
            $request->kFirstName = $user->sponFirstName;
            $request->kLastName = $user->sponLastName;
            $request->kCellNo = $user->sponCell;
            $request->kEmail = $user->sponEmail;
            $request->kAddress1 = $user->pAddress1;
            $request->kAddress2 = $user->pAddress2;
            $request->kAddressCity = $user->pAddressCity;
            $request->kAddressPostal = $user->pAddressPostal;
        }

        if($request->app_address == 1){
            $request->kAddress1 = $user->userAddress1;
            $request->kAddress2 = $user->userAddress2;
            $request->kAddressCity = $user->userAddressCity;
            $request->kAddressPostal = $user->userAddressPostal;
        }

        User::where('id', $user->id)->update([
            'app_parent_kin' => $request->app_parent_kin,
            'kinFirstName' => $request->kFirstName,
            'kinLastName' => $request->kLastName,
            'kinTitle' => $request->kTitle,
            'kinRelationship' => $request->kRelationship,
            'kinCellNo' => $request->kCellNo,
            'kinEmail' => $request->kEmail,
            'kinWorkNo' => $request->kWorkNo,
            'kinTelNo' => $request->kTeleNo,
            'app_address' => $request->app_address,
            'kinAddressStr' => $request->kAddress1,
            'kinAddressSuburb' => $request->kAddress2,
            'kinAddressCity' => $request->kAddressCity,
            'kinAddressPost' => $request->kAddressPostal,
        ]);

        return "/step-4/online-application";
    }

    public function step_4(Request $request){
        $lookups = Lookups::get();

        // $application = ApplicationToken::where('applicationToken', $applicationToken)->first();
        $user = User::where('id', Auth::user()->id)->first();

        $IdCopy = UserFiles::where("userUid", Auth::user()->id)->where('fileId', 'copyID')->first();
        $StudentCopy = UserFiles::where("userUid", Auth::user()->id)->where('fileId', 'studentCard')->first();
        $BankCopy = UserFiles::where("userUid", Auth::user()->id)->where('fileId', 'bankStatement')->first();
        $BursaryCopy = UserFiles::where("userUid", Auth::user()->id)->where('fileId', 'bursaryApproval')->first();
        $RegistrationCopy = UserFiles::where("userUid", Auth::user()->id)->where('fileId', 'proofRegistration')->first();

        return view('application.step_4', ["lookups" => $lookups, "user" => $user, "IdCopy" => $IdCopy, "StudentCopy" => $StudentCopy, "BankCopy" => $BankCopy, "BursaryCopy" => $BursaryCopy, "RegistrationCopy" => $RegistrationCopy]);
    }

    function get_user_status($fileType, $user, $student) {

        if($fileType == "proof_of_payment"){
            $typeName = "Booking Fee Proof of Payment";
            $field_data = "";
            $field_doc = "rob_10_fee";
            

        } elseif($fileType == "copyID"){
            $typeName = "Copy of ID Document";
            $field_data = "id_data_approved";
            $field_doc = "rob_15_id";            

        }elseif($fileType == "studentCard"){
            $typeName = "Copy of Student Card";
            $field_data = "student_card_approved";
            $field_doc = "rob_20_card";

        }elseif($fileType == "proofRegistration"){
            $typeName = "Proof of Registration";
            $field_data = "registration_approved";
            $field_doc = "rob_25_reg";

        }elseif($fileType == "bursaryApproval"){
            if($student->studentType == "NSFAS"){
                $typeName = "NSFAS Approval";
            }else{
                $typeName = "Bursary Approval";
            }
            $field_data = "bursary_approved";
            $field_doc = "rob_30_funds";

        }elseif($fileType == "bankStatement"){
            $typeName = "Bank Statements";
            $field_data = "bank_approved";
            $field_doc = "rob_30_funds";                

        }elseif($fileType == "moveIn"){
            $typeName = "Lease";
            $field_data = "";
            $field_doc = "rob_55_lease";
        }else{
            $typeName = strtoupper(str_replace("_", " ", str_replace("other", "other |", $fileType)));
            $field_data = $fileType;
            $field_doc = "rob_45_other";
        }

        $data_approved = 0;
        $doc_approved = 0;

        if (!empty($user)) {
            if (!empty($field_data) && isset($user->{$field_data})) {
                $data_approved = $user->{$field_data};
            }

            if (!empty($field_doc) && isset($user->{$field_doc})) {
                $doc_approved = $user->{$field_doc};
            }
        }

        return [
            'typeName' => $typeName, 

            'field_data' => $field_data, 
            'field_doc' => $field_doc,

            'data_approved' => $data_approved,
            'doc_approved' => $doc_approved
        ];

    }

    public function update_user_status($user_id){
        
        $user = User::with('files')->where('id', $user_id)->first();

        if($user->overall_status !== "Cancelled" && $user->overall_status !== "Rejected" && $user->applicationStatus !== "Approved Confirmed"){

            $booking = "Rejected";
            $files_for_booking = [];
            
            //files for booking status === Proof of payment - ID Copy
            foreach($user->files as $file){
                if($file->fileId == "proof_of_payment" || $file->fileId == "copyID"){
                    array_push($files_for_booking, $file->status);
                }
            }
    
            //Default application files that all students have
            $file_types = ["studentCard", "proofRegistration"];
    
            //Add in additional files based on student type.
            if($user->studentType == "Cash / Direct"){
                array_push($file_types, "bankStatement");
            } elseif($user->studentType == "NSFAS" || $user->studentType == "Bursary"){
                array_push($file_types, "bursaryApproval");
            }
    
            $application = "Rejected";
            $files_for_application = [];
    
            //files for application status
            foreach($user->files as $file){
                if(in_array($file->fileId, $file_types)){
                    array_push($files_for_application, $file->status);
                }
            }
    
            //lease
            $lease = "na";
    
            foreach($user->files as $file){
                if($file->fileId == "moveIn"){
                    if($file->status == "Approved"){
                        $lease = "Approved";
                    }else if($file->status == "Uploaded"){
                        $lease = "Action";
                    }else{
                        $lease = "Rejected";
                    }
                }
            }
    
            //Check all status in booking to get final booking status
            if(count($files_for_booking) == "2" && in_array("Approved", $files_for_booking)){
                $booking = "Approved";
            }
            if(in_array("Rejected", $files_for_booking)){
                $booking = "Rejected";
            }
            if(in_array("Uploaded", $files_for_booking)){
                $booking = "Action";
            }
            if($lease !== "na"){
                $booking = "Approved Confirmed";
            }
    
            //Check all status in application to get final booking status
            if(count($files_for_application) == "3" && in_array("Approved", $files_for_application)){
                $application = "Approved";
            }
            if(in_array("Rejected", $files_for_application)){
                $application = "Rejected";
            }
            if(in_array("Uploaded", $files_for_application)){
                $application = "Action";
            }
            if($lease !== "na"){
                $application = "Approved Confirmed";
            }
    
            //overall status
            $overall = "Incomplete";
    
            if($booking == "Action" || $application == "Action" || $lease == "Action"){
                $overall = "Pending";
            }

            if($booking == "Approved" && $application == "Approved"){
                $overall = "Pending";
            }
    
            if($booking == "Approved Confirmed" && $application == "Approved Confirmed" && $lease == "Approved"){
                $overall = "Approved";
            }

            User::where('id', $user_id)->update(['bookingStatus' => $booking, 'applicationStatus' => $application, 'overall_status' => $overall]);
        }

        if($user->applicationStatus == "Approved Confirmed"){
            //lease
            $lease = "na";
    
            foreach($user->files as $file){
                if($file->fileId == "moveIn"){
                    if($file->status == "Approved"){
                        $lease = "Approved";
                    }else if($file->status == "Uploaded"){
                        $lease = "Action";
                    }else{
                        $lease = "Rejected";
                    }
                }
            }

            if($lease == "na" || $lease == "Rejected"){
                $overall = "Incomplete";
            }

            if($lease == "Action"){
                $overall = "Pending";
            }

            if($lease == "Approved"){
                $overall = "Approved";
            }

            User::where('id', $user_id)->update(['overall_status' => $overall]);
        }
 
    }

    public function file_upload(Request $request){
        
        $userUid = $request->userUid;
        $currUser = User::where('id', $userUid)->first();
        $fileType = $request->fileType;

        $fileUrlBack = "";

        if ($request->hasFile('file')) {
            
            if ($request->file('file')->isValid()) {

                $filenameWithExt  = $request->file('file')->getClientOriginalName();
                $fileName = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                $extension = $request->file('file')->extension();
                $fileNameToStore = $fileName. '_'. time().'.'.$extension;

                $request->file('file')->storeAs('public/files/'.$userUid.'/', $fileNameToStore);

                $fileUrl = 'files/'.$userUid.'/'. $fileNameToStore;
                $fileUrlBack = $fileUrl;

                $hasFile = UserFiles::where('userUid', $userUid)->where('fileId', $fileType)->first();

                $user_status = $this->get_user_status($fileType, "", User::where('id', $userUid)->first());
                $field_data = $user_status['field_data'];
                $field_doc = $user_status['field_doc'];                

                UserFiles::create([
                    'fileId' => $fileType,
                    'userUid' => $userUid,
                    'fileName' => $fileNameToStore,
                    'fileUrl' => $fileUrl,
                    'status' => "Uploaded",
                    'uploaded_by' => $currUser->userFirstName,
                ]);

                $text = "Uploaded ".$user_status['typeName']." for review";
                
                $cur_status = User::find($userUid);

                //Update data status back to pending
                if (!empty($field_data)) {
                    //If the data was already approved then unapprove
                    if ($cur_status->{$field_data} == 1) {
                        User::where('id', $userUid)->update([$field_data => '0']);                        
                    }
                }

                //Update Robot status back to pending
                if (!empty($field_doc)) {
                    User::where('id', $userUid)->update([$field_doc => '1']);
                }

                //Get the file
                $file = UserFiles::where('userUid', $userUid)->where('fileId', $fileType)->first();
    
                ActionList::create([
                    "userUid" => $userUid,
                    "action" => $text,
                    "actionUserUid" => $userUid,
                    "actionUserDisplayName" => $currUser->userFirstName,
                    "actionUserRole" => 0,
                ]);

                $this->update_user_status($userUid);

                return [$file->id, User::where('id', $userUid)->first(), $file];

            }else{
                return ["error", "File Not Valid"];
            }
        }else{
            return ["error", "No File Detected"];
        }
    }

    public function step_5(Request $request){
        $lookups = Lookups::get();

        // $application = ApplicationToken::where('applicationToken', $applicationToken)->first();
        $user = User::where('id', Auth::user()->id)->first();
        $booking = Booking::where('userId', Auth::user()->id)->first();
        // $residences = Property::where('id', $booking->property)
        $proofPayment = UserFiles::where("userUid", Auth::user()->id)->where('fileId', 'proof_of_payment')->first();
        $agent = new Agent();

        $bookingRoomtype = $booking->roomType;
        $lookup = Lookups::where('group_id', '7')->first();
        $room_rate = 0;
        $actionFileData['action'] = "";

        if($lookup && $lookup->lookup_name == "1"){
            $room_rate = 0;
            $actionFileData['action'] = "Booking Fee Waived";
        }

        $jsonDataResidence = json_decode(file_get_contents('https://www.campusafrica.co.za/wp-json/wp/v2/residences'));
            $residenceId = "";

            foreach($jsonDataResidence as $residence){
                if($residence->title->rendered == $booking->property){
                    //get room booking fee from residence (WP)
                    if($actionFileData['action'] == ""){
                        $room_rate = $residence->acf->booking_fee;
                    }
                    //get residence ID (WP)
                    $residenceId = $residence->id;
                   
                }
            }
            

        $jsonDataRooms = json_decode(file_get_contents('https://www.campusafrica.co.za/wp-json/wp/v2/rooms?per_page=100'));

            $roomType = "";
            $roomName = "";
            $priced_from = 0;
            $chair = "false"; //436
            $wifi = "false"; //405
            $bath = "false"; //468
            $kitchen = "false"; //413
            $beds = 0;

            foreach($jsonDataRooms as $room){
                if($residenceId == $room->acf->residence){

                    $attributes = $room->acf->attributes;

                    foreach($attributes as $attr){
                        if($attr == "436"){
                            $chair = "true";
                        }
                        if($attr == "405"){
                            $wifi = "true";
                        }
                        if($attr == "413"){
                            $kitchen = "true";
                        }
                        if($attr == "468"){
                            $bath = "true";
                        }
                    }

                    $appliesTo = $room->acf->booking_fee_applies_to;

                    if($room->acf->booking_fee_override !== ""){
                        $room_rate = $room->acf->booking_fee_override;
                    }

                    if($user->studentType == "NSFAS"){
                        if(!in_array("NSFAS Students", $appliesTo)){
                            $room_rate = "0";
                            $actionFileData['action'] = "Booking Fee is R0";
                        }
                    }

                    if($user->studentType == "Bursary"){
                        if(!in_array("Bursary Students", $appliesTo)){
                            $room_rate = "0";
                            $actionFileData['action'] = "Booking Fee is R0";
                        }
                    }

                    if($user->studentType == "Cash / Direct"){
                        if(!in_array("Cash Students", $appliesTo)){
                            $room_rate = "0";
                            $actionFileData['action'] = "Booking Fee is R0";
                        }
                    }

                    if($booking->property == "YMCA"){

                        if($room->slug == "ymca-3-sharing" && $bookingRoomtype == "3 Sharing"){
                            $roomType = "3 Sharing";
                            $roomName = "ymca-3-sharing";
                            $priced_from = $room->acf->price_from;
                            $beds = $room->acf->beds;
                            break;
                        }
                        if($room->slug == "ymca-2-sharing" && $bookingRoomtype == "2 Sharing"){
                            $roomType = "2 Sharing";
                            $roomName = "ymca-2-sharing";
                            $priced_from = $room->acf->price_from;
                            $beds = $room->acf->beds;
                            break;
                        }
                        if($room->slug == "ymca-single-room-large" && $bookingRoomtype == "Single Large"){
                            $roomType = "Single Large";
                            $roomName = "ymca-single-room-large";
                            $priced_from = $room->acf->price_from;
                            $beds = $room->acf->beds;
                            break;
                        }
                        if($room->slug == "ymca-single-room-medium" && $bookingRoomtype == "Single Medium"){
                            $roomType = "Single Medium";
                            $roomName = "ymca-single-room-medium";
                            $priced_from = $room->acf->price_from;
                            $beds = $room->acf->beds;
                            break;
                        }
                        if($room->slug == "ymca-single-room-small" && $bookingRoomtype == "Single Small"){
                            $roomType = "Single Small";
                            $roomName = "ymca-single-room-small";
                            $priced_from = $room->acf->price_from;
                            $beds = $room->acf->beds;
                            break;
                        }
                    }

                    if($booking->property == "126 Siemert"){
                        if($room->slug == "126-siemert-single-room" && $bookingRoomtype == "Single"){
                            $roomType = "Single";
                            $roomName = "126-siemert-single-room";
                            $priced_from = $room->acf->price_from;
                            $beds = $room->acf->beds;
                            break;
                        }
                        if($room->slug == "126-siemert-2-sharing" && $bookingRoomtype == "2 Sharing"){
                            $roomType = "2 Sharing";
                            $roomName = "126-siemert-2-sharing";
                            $priced_from = $room->acf->price_from;
                            $beds = $room->acf->beds;
                            break;
                        }
                    }

                    

                    if($booking->property == "Wynton Joy"){
                        if($room->slug == "wynton-joy-single-room" && $bookingRoomtype == "Single"){
                            $roomType = "Single";
                            $roomName = "wynton-joy-single-room";
                            $priced_from = $room->acf->price_from;
                            $beds = $room->acf->beds;
                            break;
                        }
                        
                        if($room->slug == "wynton-joy-2-sharing" && $bookingRoomtype == "2 Sharing"){
                            $roomType = "2 Sharing";
                            $roomName = "wynton-joy-2-sharing";
                            $priced_from = $room->acf->price_from;
                            $beds = $room->acf->beds;
                            break;
                        }
                    }

                    if($booking->property == "Dunvista Mansions"){
                        if($room->slug == "dunvista-mansions-single-room" && $bookingRoomtype == "Single"){
                            $roomType = "Single";
                            $roomName = "dunvista-mansions-single-room";
                            $priced_from = $room->acf->price_from;
                            $beds = $room->acf->beds;
                            break;
                        }
                        if($room->slug == "dunvista-mansions-2-sharing" && $bookingRoomtype == "2 Sharing"){
                            $roomType = "2 Sharing";
                            $roomName = "dunvista-mansions-2-sharing";
                            $priced_from = $room->acf->price_from;
                            $beds = $room->acf->beds;
                            break;
                        }
                    }

                    if($booking->property == "49 Jorissen"){
                        if($room->slug == "49-jorissen-single-room-2-bedroom-apartment" && $bookingRoomtype == "Single 2 bedroom"){
                            $roomType = "Single 2 bedroom";
                            $roomName = "49-jorissen-single-room-2-bedroom-apartment";
                            $priced_from = $room->acf->price_from;
                            $beds = $room->acf->beds;
                            break;
                        }
                        if($room->slug == "49-jorissen-single-room-3-bedroom-apartment" && $bookingRoomtype == "Single 3 bedroom"){
                            $roomType = "Single 3 bedroom";
                            $roomName = "49-jorissen-single-room-3-bedroom-apartment";
                            $priced_from = $room->acf->price_from;
                            $beds = $room->acf->beds;
                            break;
                        }
                        if($room->slug == "49-jorissen-single-room-4-bedroom-apartment" && $bookingRoomtype == "Single 4 bedroom"){
                            $roomType = "Single 4 bedroom";
                            $roomName = "49-jorissen-single-room-4-bedroom-apartment";
                            $priced_from = $room->acf->price_from;
                            $beds = $room->acf->beds;
                            break;
                        }
                        if($room->slug == "49-jorissen-2-sharing-hotel" && $bookingRoomtype == "2 Sharing 2 Bedroom"){
                            $roomType = "2 Sharing 2 Bedroom | 2 Sharing";
                            $roomName = "49-jorissen-2-sharing-hotel";
                            $priced_from = $room->acf->price_from;
                            $beds = $room->acf->beds;
                            break;
                        }
                    }

                    if($booking->property == "80 Jorissen"){
                        if($room->slug == "80-jorissen-single-room" && $bookingRoomtype == "Single"){
                            $roomType = "Single";
                            $roomName = "80-jorissen-single-room";
                            $priced_from = $room->acf->price_from;
                            $beds = $room->acf->beds;
                            break;
                        }
                        if($room->slug == "80-jorissen-2-sharing" && $bookingRoomtype == "2 Sharing"){
                            $roomType = "2 Sharing";
                            $roomName = "80-jorissen-2-sharing";
                            $priced_from = $room->acf->price_from;
                            $beds = $room->acf->beds;
                            break;
                        }
                    }
                    

                    if($booking->property == "Braamlofts"){
                        if($room->slug == "braamlofts-single-room" && $bookingRoomtype == "Single"){
                            $roomType = "Single";
                            $roomName = "braamlofts-single-room";
                            $priced_from = $room->acf->price_from;
                            $beds = $room->acf->beds;
                            break;
                        }
                        if($room->slug == "braamlofts-2-sharing" && $bookingRoomtype == "2 Sharing"){
                            $roomType = "2 Sharing";
                            $roomName = "braamlofts-2-sharing";
                            $priced_from = $room->acf->price_from;
                            $beds = $room->acf->beds;
                            break;
                        }
                        if($room->slug == "braamlofts-3-sharing" && $bookingRoomtype == "3 Sharing"){
                            $roomType = "3 Sharing";
                            $roomName = "braamlofts-3-sharing";
                            $priced_from = $room->acf->price_from;
                            $beds = $room->acf->beds;
                            break;
                        }
                    }

                    
                }
            }

            if($room_rate == "0"){
    
                $uploaded = UserFiles::where('userUid', $user->id)->where('fileId', 'proof_of_payment')->first();
    
                if(!$uploaded){
                    UserFiles::create([
                        'userUid' => $user->id,
                        'fileId' => 'proof_of_payment',
                        'status' => 'Approved'
                    ]);
                }else{
                    UserFiles::where('userUid', $user->id)->where('fileId', 'proof_of_payment')->update([
                        'status' => 'Approved'
                    ]);
                }
    
                Booking::where('userId', $user->id)->update(['room_rate' => $room_rate]);
    
                $actionFileData['userUid'] = $user->id;
                $actionFileData['action'] = $actionFileData['action'];
                $actionFileData['actionUserUid'] = Auth::user()->id;
                $actionFileData['actionUserDisplayName'] = Auth::user()->userFirstName;
                $actionFileData['actionUserRole'] = Auth::user()->userRole;
            
                ActionList::create($actionFileData);
            }


        return view('application.step_5', ["lookups" => $lookups, "user" => $user, "booking" => $booking, "proofPayment" => $proofPayment, "agent" => $agent, "priced_from" => $priced_from, "chair" => $chair, "wifi" => $wifi, "bath" => $bath, "kitchen" => $kitchen, "beds" => $beds, "room_rate" => $room_rate]);
    }

    public function complete_online_app(Request $request){
        $lookups = Lookups::get();

        // $application = ApplicationToken::where('applicationToken', $applicationToken)->first();
        $user = User::where('id', Auth::user()->id)->first();
        $booking = Booking::where('userId', Auth::user()->id)->first();
        User::where('id', Auth::user()->id)->update(['onboard_complete' => 2]);

        return view('application.complete', ["lookups" => $lookups, "user" => $user, "booking" => $booking]);
    }

    public function verify_password(Request $request){
        // $lookups = Lookups::get();
        // $user = User::where('id', Auth::user()->id)->first();

        // return view('application.password', ["lookups" => $lookups, "user" => $user]);

        User::where('id', Auth::user()->id)->update(['email_verified' => 1]);

        return redirect('/step-2/online-application');
    }

    public function confirm_password(Request $request){
        User::where('id', Auth::user()->id)->update(['password' => Hash::make($request->confirm_password), 'email_verified' => 1]);
    }

    public function change_student_type(Request $request){
        $lookups = Lookups::get();
        $user = User::where('id', Auth::user()->id)->first();

        return view('user.changeType', ["headerTitle" => "Change Student Type", "lookups" => $lookups, "user" => $user]);
    }

    public function update_student_details(Request $request){
        
        $user = User::where('id', Auth::user()->id)->first();

        if($request->studentType !== $user->studentType){
            if($user->studentType == "Cash / Direct"){
                $file = UserFiles::where('fileId', 'bankStatement')->where('userUid', $user->id)->first();
                if(!empty($file)){
                    UserFiles::where('id', $file->id)->update(['status' => 'Rejected']);
                }
            }else{
                $file = UserFiles::where('fileId', 'bursaryApproval')->where('userUid', $user->id)->first();
                if(!empty($file)){
                    UserFiles::where('id', $file->id)->update(['status' => 'Rejected']);
                }
            }
        }

        if($request->app_address_p == 1){
            $request->pAddress1 = $user->userAddress1;
            $request->pAddress2 = $user->userAddress2;
            $request->pAddressCity = $user->userAddressCity;
            $request->pAddressPostal = $user->userAddressPostal;
        }

        User::where('id', $user->id)->update([
            'studentType' => $request->studentType,
            'sponFirstName' => $request->pFirstName,
            'sponLastName' => $request->pLastName,
            'sponCell' => $request->pCell_no,
            'sponEmail' => $request->pEmail,
            'pNationality' => $request->pNationality,
            'sponId' => $request->pIdNumber,
            'pPassportNo' => $request->pPassportNo,
            'pAddress1' => $request->pAddress1,
            'pAddress2' => $request->pAddress2,
            'pAddressCity' => $request->pAddressCity,
            'pAddressPostal' => $request->pAddressPostal,
            'app_address_p' => $request->app_address_p,
            'burCompanyName' => $request->bCompanyName,
            'burRegNo' => $request->bRegistrationNo,
            'burContactPerson' => $request->bContactPerson,
            'burContactNo' => $request->bContactNumber,
            'burEmail' => $request->bEmail,
            'bAddress1' => $request->bAddress1,
            'bAddress2' => $request->bAddress2,
            'bAddressCity' => $request->bAddressCity,
            'bAddressPostal' => $request->bAddressPostal,
            'bPayment' => $request->bPayment,
            'bint' => $request->bint,
            'bIntName' => $request->bIntName,
        ]);

        
    }

    public function verify_email_account(Request $request){

        User::where('id', Auth::user()->id)->update(['onboard_complete' => 2, 'email_verified' => 1]);

        return redirect('/dashboard');
    }
}

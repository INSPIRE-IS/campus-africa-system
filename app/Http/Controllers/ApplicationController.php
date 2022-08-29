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
use App\Models\RoomTypes;
use App\Models\Residence;
use App\Models\MailerList;
use Mail;
use App\Models\Property;
use App\Models\OnlinePayments;
use App\Models\StudentsInfo;
use App\Models\ImportNSFASStudentAuditLog;
use App\Models\ImportNSFASApproval;

class ApplicationController extends Controller
{

    public function step_1(Request $request, $propertyID, $roomType){

        $onlineAppOpen = Lookups::where('group_id', '9')->first();

        if($onlineAppOpen->lookup_name == '1'){
            //
            $lookups = Lookups::get();
            $agent = new Agent();
            $residences = Residence::get();
    
    
            return view('application.step_1', ["lookups" => $lookups, "propertyID" => $propertyID, "roomType" => $roomType, "agent" => $agent, "residences" => $residences]);
        }else{
            return view('errors.onlineAppOffline');
        }

    }

    Public function get_room_maps(Request $request){
        //Get residences from WP
        $jsonDataResidence = json_decode(file_get_contents('https://www.campusafrica.co.za/wp-json/wp/v2/residences'));
        $residenceNameWP = "";
        $roomID = "";

        foreach($jsonDataResidence as $residence){
            if($residence->id == $request->residence){
                //get residence Name from residence (WP)
                $residenceNameWP = $residence->title->rendered;
            }
        }

        $residence = Residence::where('residence', $residenceNameWP)->first();
        $roomMaps = RoomTypes::where('property_id', $residence->id)->get();

        foreach($roomMaps as $room){
            if($room->room_map_slug == $request->roomType){
                $roomID = $room->id;
            }
        }

        return [$residence->id, $roomID];
    }

    public function step_verify(Request $request, $applicationToken){

        return view('application.verify_user', ["applicationToken" => $applicationToken]);
    }

    public function veryfiy_user(Request $request){
        $lookups = Lookups::get();

        $application = ApplicationToken::where('applicationToken', $request->applicationToken)->first();

        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        

        if($request->security_code == $application->security_token){
            ApplicationToken::where('applicationToken', $request->applicationToken)->update(['verified' => 1]);
            // User::where('id', $application->userUid)->update(['email_verified' => 1]);
            StudentsInfo::where('userUid', $application->userUid)->where('year', $this_year)->update(['email_verified' => 1]);
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

    public function passport_check_registration(Request $request){
        $passportNo = $request->passportNo;
        $passportNoValid = true;
        $property = null;

        if($passportNo !== ""){
            $user = $idCheck = StudentsInfo::where('userPassport', $passportNo)->orderby('year', 'desc')->first();

            if(!empty($idCheck)){
                $passportNoValid = false;
                $booking = Booking::where('id', $idCheck->bookingId)->orderBy('year', 'desc')->first();
                $property = Residence::where('id', $booking->property)->pluck('residence')->first();
            }
        }

        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        $booking = (object)[];

        if(!empty($user)){
            $booking = Booking::where('userId', $user->userUid)->orderBy('year', 'desc')->first();
        }

        return ["done", $passportNoValid, $user, $property, $booking, $this_year];
    }

    public function step_2(Request $request){
        $lookups = Lookups::get();

        // $application = ApplicationToken::where('applicationToken', $applicationToken)->first();
        $user = User::where('id', Auth::user()->id)->first();

        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        $userInfo = StudentsInfo::where('userUid', Auth::user()->id)->where('year', $this_year)->first();


        // if(!empty($user)){
        //     ApplicationToken::where('applicationToken', $applicationToken)->update(['verified' => 1]);
        // }

        $agent = new Agent();

        if($userInfo->studentType == "Cash / Direct")
        {
            return view('application.step_2_spon', ["lookups" => $lookups, "user" => $userInfo, "agent" => $agent, "new" => 0, "userNew" => $userInfo]);
        }
        elseif($userInfo->studentType == "Bursary")
        {
            return view('application.step_2_bur', ["lookups" => $lookups, "user" => $userInfo, "agent" => $agent, "new" => 0, "userNew" => $userInfo]);
        }
        else
        {
            return view('application.step_3', ["lookups" => $lookups, "user" => $userInfo, "agent" => $agent, "new" => 0, "userNew" => $userInfo]);
        } 
    }

    public function step_2_spon(Request $request){
        
        // $application = ApplicationToken::where('applicationToken', $applicationToken)->first();
        $user = User::where('id', Auth::user()->id)->first();

        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        $userInfo = StudentsInfo::where('userUid', Auth::user()->id)->where('year', $this_year)->first();

        if($request->app_address_p == 1){
            $request->pAddress1 = $userInfo->userAddress1;
            $request->pAddress2 = $userInfo->userAddress2;
            $request->pAddressCity = $userInfo->userAddressCity;
            $request->pAddressPostal = $userInfo->userAddressPostal;
        }

        StudentsInfo::where('userUid', $user->id)->where('year', $this_year)->update([
            'sponFirstName' => $request->pFirstName,
            'sponLastName' => $request->pLastName,
            'sponCell' => $request->pCell_no,
            'sponCellCode' => $request->sponCellCode,
            'sponEmail' => $request->pEmail,
            'pNationality' => $request->pNationality,
            'sponId' => $request->pIdNumber,
            'pPassportNo' => $request->pPassportNo,
            'pAddress1' => $request->pAddress1,
            'pAddress2' => $request->pAddress2,
            'pAddressCity' => $request->pAddressCity,
            'pAddressPostal' => $request->pAddressPostal,
            'app_address_p' => $request->app_address_p,
            'c_credit_check' => $request->c_credit_check,
            'c_credit_risk' => $request->c_credit_risk,
        ]);

        return "/step-3/online-application";
    }

    public function step_2_bur(Request $request){

        // $application = ApplicationToken::where('applicationToken', $applicationToken)->first();
        $user = User::where('id', Auth::user()->id)->first();

        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        StudentsInfo::where('userUid', $user->id)->where('year', $this_year)->update([
            'burCompanyName' => $request->bCompanyName,
            'burRegNo' => $request->bRegistrationNo,
            'burContactPerson' => $request->bContactPerson,
            'burContactNo' => $request->bContactNumber,
            'burContactCode' => $request->burContactCode,
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

        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        $user = StudentsInfo::where('userUid', Auth::user()->id)->where('year', $this_year)->first();

        // $application = ApplicationToken::where('applicationToken', $applicationToken)->first();
        // $user = User::where('id', Auth::user()->id)->first();

        return view('application.step_3', ["lookups" => $lookups, "user" => $user, "userNew" => $user, "new" => 0]);
    }

    public function step_3_kin(Request $request){
        // $application = ApplicationToken::where('applicationToken', $applicationToken)->first();
        $user = User::where('id', Auth::user()->id)->first();

        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        $userInfo = StudentsInfo::where('userUid', Auth::user()->id)->where('year', $this_year)->first();

        if($request->app_parent_kin == 1){
            $request->kFirstName = $userInfo->sponFirstName;
            $request->kLastName = $userInfo->sponLastName;
            $request->kCellNo = $userInfo->sponCell;
            $request->kinCellCode = $userInfo->sponCellCode;
            $request->kEmail = $userInfo->sponEmail;
            $request->kAddress1 = $userInfo->pAddress1;
            $request->kAddress2 = $userInfo->pAddress2;
            $request->kAddressCity = $userInfo->pAddressCity;
            $request->kAddressPostal = $userInfo->pAddressPostal;
        }

        if($request->app_address == 1){
            $request->kAddress1 = $userInfo->userAddress1;
            $request->kAddress2 = $userInfo->userAddress2;
            $request->kAddressCity = $userInfo->userAddressCity;
            $request->kAddressPostal = $userInfo->userAddressPostal;
        }

        StudentsInfo::where('userUid', $user->id)->where('year', $this_year)->update([
            'app_parent_kin' => $request->app_parent_kin,
            'kinFirstName' => $request->kFirstName,
            'kinLastName' => $request->kLastName,
            'kinTitle' => $request->kTitle,
            'kinRelationship' => $request->kRelationship,
            'kinCellNo' => $request->kCellNo,
            'kinCellCode' => $request->kinCellCode,
            'kinEmail' => $request->kEmail,
            'kinWorkNo' => $request->kWorkNo,
            'kinWorkCode' => $request->kinWorkCode,
            'kinTelNo' => $request->kTeleNo,
            'kinTelCode' => $request->kinTelCode,
            'app_address' => $request->app_address,
            'kinAddressStr' => $request->kAddress1,
            'kinAddressSuburb' => $request->kAddress2,
            'kinAddressCity' => $request->kAddressCity,
            'kinAddressPost' => $request->kAddressPostal,
            'rob_35_info' => 1,
        ]);

        return "/step-4/online-application";
    }

    public function step_4(Request $request){
        $lookups = Lookups::get();

        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        // $application = ApplicationToken::where('applicationToken', $applicationToken)->first();
        $user = User::where('id', Auth::user()->id)->first();
        $userInfo = StudentsInfo::where('userUid', Auth::user()->id)->where('year', $this_year)->first();

        $IdCopy = UserFiles::where("userUid", Auth::user()->id)->where('fileId', 'copyID')->where('year', $this_year)->first();
        $StudentCopy = UserFiles::where("userUid", Auth::user()->id)->where('fileId', 'studentCard')->where('year', $this_year)->first();
        $BankCopy = UserFiles::where("userUid", Auth::user()->id)->where('fileId', 'bankStatement')->where('year', $this_year)->first();
        $BursaryCopy = UserFiles::where("userUid", Auth::user()->id)->where('fileId', 'bursaryApproval')->where('year', $this_year)->first();
        $RegistrationCopy = UserFiles::where("userUid", Auth::user()->id)->where('fileId', 'proofRegistration')->where('year', $this_year)->first();

        return view('application.step_4', ["lookups" => $lookups, "user" => $userInfo, "IdCopy" => $IdCopy, "StudentCopy" => $StudentCopy, "BankCopy" => $BankCopy, "BursaryCopy" => $BursaryCopy, "RegistrationCopy" => $RegistrationCopy]);
    }

    function get_user_status($fileType, $user, $student) {

        if($fileType == "proof_of_payment"){
            $typeName = "Booking Fee Proof of Payment";
            $field_data = "fee_approved";
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
        
        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        $userInfo = StudentsInfo::where('userUid',  $user_id)->where('year', $this_year)->first();
        $userFiles = UserFiles::where('userUid',  $user_id)->where('year', $this_year)->get();
        
        $user = User::with('files')->where('id', $user_id)->first();

        if($userInfo->overall_status !== "Cancelled" && $userInfo->overall_status !== "Rejected" && $userInfo->applicationStatus !== "Approved Confirmed"){

            $booking = "Rejected";
            $files_for_booking = [];
            
            //files for booking status === Proof of payment - ID Copy
            foreach($userFiles as $file){
                if($file->fileId == "proof_of_payment" || $file->fileId == "copyID"){
                    array_push($files_for_booking, $file->status);
                }
            }
    
            //Default application files that all students have
            $file_types = ["studentCard", "proofRegistration"];
    
            //Add in additional files based on student type.
            if($userInfo->studentType == "Cash / Direct"){
                array_push($file_types, "bankStatement");
            } elseif($userInfo->studentType == "NSFAS" || $userInfo->studentType == "Bursary"){
                array_push($file_types, "bursaryApproval");
            }
    
            $application = "Rejected";
            $files_for_application = [];
    
            //files for application status
            foreach($userInfo->files as $file){
                if(in_array($file->fileId, $file_types)){
                    array_push($files_for_application, $file->status);
                }
            }
    
            //lease
            $lease = "na";
    
            foreach($userInfo->files as $file){
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

            // User::where('id', $user_id)->update(['bookingStatus' => $booking, 'applicationStatus' => $application, 'overall_status' => $overall]);
            StudentsInfo::where('userUid',  $user_id)->where('year', $this_year)->update(['bookingStatus' => $booking, 'applicationStatus' => $application, 'overall_status' => $overall]);
        }

        if($userInfo->applicationStatus == "Approved Confirmed"){
            //lease
            $lease = "na";
    
            foreach($userFiles as $file){
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

            // User::where('id', $user_id)->update(['overall_status' => $overall]);
            StudentsInfo::where('userUid',  $user_id)->where('year', $this_year)->update(['overall_status' => $overall]);
        }
 
    }

    public function file_upload(Request $request){
        
        $userUid = $request->userUid;
        $currUser = User::where('id', $userUid)->first();
        $fileType = $request->fileType;

        $fileUrlBack = "";

        if ($request->hasFile('file')) {
            
            if ($request->file('file')->isValid()) {

                $this_year = Lookups::where('group_id', '4')->first();
                $this_year = $this_year->lookup_name;

                $userInfo = StudentsInfo::where('userUid', $userUid)->where('year', $this_year)->first();

                $filenameWithExt  = $request->file('file')->getClientOriginalName();
                $fileName = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                $extension = $request->file('file')->extension();
                $fileNameToStore = $fileName. '_'. time().'.'.$extension;

                $request->file('file')->storeAs('public/files/'.$userUid.'/', $fileNameToStore);

                $fileUrl = 'files/'.$userUid.'/'. $fileNameToStore;
                $fileUrlBack = $fileUrl;

                $hasFile = UserFiles::where('userUid', $userUid)->where('fileId', $fileType)->where('year', $this_year)->first();

                $user_status = $this->get_user_status($fileType, "", StudentsInfo::where('userUid', $userUid)->where('year', $this_year)->first());
                $field_data = $user_status['field_data'];
                $field_doc = $user_status['field_doc'];                

                UserFiles::create([
                    'fileId' => $fileType,
                    'userUid' => $userUid,
                    'fileName' => $fileNameToStore,
                    'fileUrl' => $fileUrl,
                    'status' => "Uploaded",
                    'uploaded_by' => $userInfo->userFirstName,
                    'year' => $this_year,
                ]);

                $text = "Uploaded ".$user_status['typeName']." for review";
                
                // $cur_status = User::find($userUid);
                $cur_status = StudentsInfo::where('userUid', $userUid)->where('year', $this_year)->first();

                //Update data status back to pending
                if (!empty($field_data)) {
                    //If the data was already approved then unapprove
                    if ($cur_status->{$field_data} == 1) {
                        // User::where('id', $userUid)->update([$field_data => '0']);     
                        StudentsInfo::where('userUid', $userUid)->where('year', $this_year)->update([$field_data => '0']);                
                    }
                }

                //Update Robot status back to pending
                if (!empty($field_doc)) {
                    // User::where('id', $userUid)->update([$field_doc => '1']);
                    StudentsInfo::where('userUid', $userUid)->where('year', $this_year)->update([$field_doc => '1']);
                }

                //Get the file
                $file = UserFiles::where('userUid', $userUid)->where('fileId', $fileType)->where('year', $this_year)->first();
    
                ActionList::create([
                    "userUid" => $userUid,
                    "action" => $text,
                    "actionUserUid" => $userUid,
                    "actionUserDisplayName" => $userInfo->userFirstName,
                    "actionUserRole" => 0,
                    "year" => $this_year,
                ]);

                Booking::where('userId', $userUid)->where('year', $this_year)->update(['payment_type' => 'Manual EFT']);

                $this->update_user_status($userUid);

                return [$file->id, StudentsInfo::where('userUid', Auth::user()->id)->where('year', $this_year)->first(), $file];

            }else{
                return ["error", "File Not Valid"];
            }
        }else{
            return ["error", "No File Detected"];
        }
    }

    public function step_5(Request $request){
        $lookups = Lookups::get();

        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        // $application = ApplicationToken::where('applicationToken', $applicationToken)->first();
        $user = User::where('id', Auth::user()->id)->first();
        $userInfo = StudentsInfo::where('userUid', Auth::user()->id)->where('year', $this_year)->first();
        $booking = Booking::where('userId', Auth::user()->id)->where('year', $this_year)->first();
        // $residences = Property::where('id', $booking->property)
        $proofPayment = UserFiles::where("userUid", Auth::user()->id)->where('fileId', 'proof_of_payment')->where('year', $this_year)->first();
        $agent = new Agent();

       
        $lookup = Lookups::where('group_id', '7')->first();
        $room_rate = 0;
        $actionFileData['action'] = "";

        $roomTypeInfo = RoomTypes::where('property_id', $booking->property)->where('id', $booking->roomType)->first();
        $propertyTypeInfo = Residence::where('id', $booking->property)->first();

        $allResidences = Residence::get();
        $allRoomTypes = RoomTypes::get();

        $property = Residence::where('id', $booking->property)->pluck('residence')->first();
        $roomTypeNew = RoomTypes::where('id', $booking->roomType)->pluck('name')->first();

        $roomTypeData = RoomTypes::where('id', $booking->roomType)->first();
        $roomTypeId = $roomTypeData->room_map_slug;

        $bookingRoomtype = $roomTypeInfo->name;
        $booking->property = $propertyTypeInfo->residence;

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

                                        
                    if($room->id == $roomTypeId){
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

                        $roomType = $roomTypeNew;
                        $roomName = $room->slug;
                        $priced_from = $room->acf->price_from;
                        $beds = $room->acf->beds;

                        $appliesTo = $room->acf->booking_fee_applies_to;
    
                        if($room->acf->booking_fee_override !== ""){
                            $room_rate = $room->acf->booking_fee_override;
                        }
    
                        if($userInfo->studentType == "NSFAS"){
                            if(!in_array("NSFAS Students", $appliesTo)){
                                $room_rate = "0";
                                $actionFileData['action'] = "Booking Fee is R0";
                            }
                        }
    
                        if($userInfo->studentType == "Bursary"){
                            if(!in_array("Bursary Students", $appliesTo)){
                                $room_rate = "0";
                                $actionFileData['action'] = "Booking Fee is R0";
                            }
                        }
    
                        if($userInfo->studentType == "Cash / Direct"){
                            if(!in_array("Cash Students", $appliesTo)){
                                $room_rate = "0";
                                $actionFileData['action'] = "Booking Fee is R0";
                            }
                        }
                    }

                }
            }

            if($roomTypeData->pull_price == null){
                $priced_from = $roomTypeData->rent_price;
                $room_rate = $roomTypeData->booking_price;

                if($userInfo->studentType == "NSFAS"){
                    if($roomTypeData->apply_nsfas == null){
                        $room_rate = "0";
                        $actionFileData['action'] = "Booking Fee is R0";
                    }
                }

                if($userInfo->studentType == "Bursary"){
                    if($roomTypeData->apply_bursary == null){
                        $room_rate = "0";
                        $actionFileData['action'] = "Booking Fee is R0";
                    }
                }

                if($userInfo->studentType == "Cash / Direct"){
                    if($roomTypeData->apply_cash == null){
                        $room_rate = "0";
                        $actionFileData['action'] = "Booking Fee is R0";
                    }
                }
            }

            if($room_rate == "0"){
    
                $uploaded = UserFiles::where('userUid', $userInfo->id)->where('fileId', 'proof_of_payment')->where('year', $this_year)->first();
    
                if(!$uploaded){
                    UserFiles::create([
                        'userUid' => $user->id,
                        'fileId' => 'proof_of_payment',
                        'status' => 'Approved',
                        'year' => $this_year,
                    ]);

                    // User::where('id', $user->id)->update([
                    //     'rob_10_fee' => 2
                    // ]);
                    StudentsInfo::where('userUid', Auth::user()->id)->where('year', $this_year)->update([
                        'rob_10_fee' => 2
                    ]);
                }else{
                    UserFiles::where('userUid', $user->id)->where('fileId', 'proof_of_payment')->where('year', $this_year)->update([
                        'status' => 'Approved'
                    ]);

                    // User::where('id', $user->id)->update([
                    //     'rob_10_fee' => 2
                    // ]);
                    StudentsInfo::where('userUid', Auth::user()->id)->where('year', $this_year)->update([
                        'rob_10_fee' => 2
                    ]);
                }
    
                Booking::where('userId', $user->id)->where('year', $this_year)->update(['room_rate' => $room_rate]);
    
                $actionFileData['userUid'] = $user->id;
                $actionFileData['action'] = $actionFileData['action'];
                $actionFileData['actionUserUid'] = Auth::user()->id;
                $actionFileData['actionUserDisplayName'] = Auth::user()->userFirstName;
                $actionFileData['actionUserRole'] = Auth::user()->userRole;
                $actionFileData['year'] = $this_year;
            
                ActionList::create($actionFileData);
            }else{
               Booking::where('userId', $user->id)->where('year', $this_year)->update(['room_rate' => $room_rate]);
            }

            $newBooking = Booking::where('userId', $user->id)->where('year', $this_year)->first();


        return view('application.step_5', ["lookups" => $lookups, "user" => $userInfo, "booking" => $newBooking, "proofPayment" => $proofPayment, "agent" => $agent, "priced_from" => $priced_from, "chair" => $chair, "wifi" => $wifi, "bath" => $bath, "kitchen" => $kitchen, "beds" => $beds, "room_rate" => $room_rate, "roomTypeInfo" => $roomTypeInfo, "propertyTypeInfo" => $propertyTypeInfo]);
    }

    public function complete_online_app(Request $request){
        $lookups = Lookups::get();

        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        // $application = ApplicationToken::where('applicationToken', $applicationToken)->first();
        $user = User::where('id', Auth::user()->id)->first();
        $booking = Booking::where('userId', Auth::user()->id)->where('year', $this_year)->first();
        // User::where('id', Auth::user()->id)->update(['onboard_complete' => 1]);
        StudentsInfo::where('userUid', Auth::user()->id)->where('year', $this_year)->update(['onboard_complete' => 2]);
        $userInfo = StudentsInfo::where('userUid', Auth::user()->id)->where('year', $this_year)->first();

        return view('application.complete', ["lookups" => $lookups, "user" => $userInfo, "booking" => $booking]);
    }

    public function verify_password(Request $request){
        // $lookups = Lookups::get();
        // $user = User::where('id', Auth::user()->id)->first();

        // return view('application.password', ["lookups" => $lookups, "user" => $user]);

        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        // User::where('id', Auth::user()->id)->update(['email_verified' => 1]);
        StudentsInfo::where('userUid', Auth::user()->id)->where('year', $this_year)->update(['email_verified' => 1]);

        return redirect('/step-2/online-application');
    }

    public function confirm_password(Request $request){
        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        User::where('id', Auth::user()->id)->update(['password' => Hash::make($request->confirm_password)]);
        StudentsInfo::where('userUid', Auth::user()->id)->where('year', $this_year)->update(['email_verified' => 1]);
    }

    public function change_student_type(Request $request){
        $lookups = Lookups::get();

        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        // $user = User::where('id', Auth::user()->id)->first();
        $user = StudentsInfo::where('userUid', Auth::user()->id)->where('year', $this_year)->first();
        $agent = new Agent();

        return view('user.changeType', ["headerTitle" => "Change Student Type", "lookups" => $lookups, "user" => $user, "agent" => $agent, "active" => 1]);
    }

    public function update_student_details(Request $request){

        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        $user = User::where('id', Auth::user()->id)->first();
        $userInfo = StudentsInfo::where('userUid', Auth::user()->id)->where('year', $this_year)->first();

        if($request->studentType !== $userInfo->studentType){
            if($userInfo->studentType == "Cash / Direct"){
                $file = UserFiles::where('fileId', 'bankStatement')->where('userUid', $user->id)->where('year', $this_year)->first();
                if(!empty($file)){
                    UserFiles::where('id', $file->id)->update(['status' => 'Rejected']);
                }
            }else{
                $file = UserFiles::where('fileId', 'bursaryApproval')->where('userUid', $user->id)->where('year', $this_year)->first();
                if(!empty($file)){
                    UserFiles::where('id', $file->id)->update(['status' => 'Rejected']);
                }
            }

            ActionList::create([
                "userUid" => Auth::user()->id,
                "action" => "Updated Student Type From ".$userInfo->studentType." to ".$request->studentType,
                "actionUserUid" => Auth::user()->id,
                "actionUserDisplayName" => $userInfo->userFirstName,
                "actionUserRole" => Auth::user()->userRole,
                "year" => $this_year,
            ]);
        }


        if($request->app_address_p == 1){
            $request->pAddress1 = $userInfo->userAddress1;
            $request->pAddress2 = $userInfo->userAddress2;
            $request->pAddressCity = $userInfo->userAddressCity;
            $request->pAddressPostal = $userInfo->userAddressPostal;
        }

        StudentsInfo::where('userUid', Auth::user()->id)->where('year', $this_year)->update([
            'studentType' => $request->studentType,
            'sponFirstName' => $request->pFirstName,
            'sponLastName' => $request->pLastName,
            'sponCell' => $request->pCell_no,
            'sponCellCode' => $request->sponCellCode,
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
            'burContactCode' => $request->burContactCode,
            'burEmail' => $request->bEmail,
            'bAddress1' => $request->bAddress1,
            'bAddress2' => $request->bAddress2,
            'bAddressCity' => $request->bAddressCity,
            'bAddressPostal' => $request->bAddressPostal,
            'bPayment' => $request->bPayment,
            'bint' => $request->bint,
            'bIntName' => $request->bIntName,
            'b_meeting' => $request->b_meeting,
            'c_credit_risk' => $request->c_credit_risk,
            'c_credit_check' => $request->c_credit_check,
        ]);

        
    }

    public function verify_email_account(Request $request){

        // User::where('id', Auth::user()->id)->update(['onboard_complete' => 2, 'email_verified' => 1]);
        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        StudentsInfo::where('userUid', Auth::user()->id)->where('year', $this_year)->update(['onboard_complete' => 2, 'email_verified' => 1]);

        $appOnline = Lookups::where('group_id', '9')->first();

        if($appOnline->lookup_name == '1'){
            return redirect('/dashboard');
        }else{
            return redirect('/verified');
        }
    }

    public function verify_new_email_account(Request $request){
        // User::where('id', Auth::user()->id)->update(['email_verified' => 1]);

        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        StudentsInfo::where('userUid', Auth::user()->id)->where('year', $this_year)->update(['email_verified' => 1]);

        $appOnline = Lookups::where('group_id', '9')->first();

        if($appOnline->lookup_name == '1'){
            return redirect('/dashboard');
        }else{
            return redirect('/verified');
        }
        
    }

    public function verified(){
        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        $info = StudentsInfo::where('userUid', Auth::user()->id)->where('year', $this_year)->first();
        $booking = Booking::where('id', $info->bookingId)->first();
        return view('outSession.verified', compact('info', 'booking'));
    }

    public function loginAsStudent($userid){
        Auth::loginUsingId($userid, true);
        return redirect('/dashboard');
    }

    public function get_room_ids(Request $request){
        $property = $request->residence;

        $residence = Residence::where('id', $property)->first();

        $property = Property::where('residence', $residence->id)->get();
        $this_year = Lookups::where('group_id', '4')->first();
        $searchable_rooms = [];

        foreach($property as $prop){
            if($prop->year_end == 0 || $prop->year_end >= $this_year){
                if(!in_array($prop->room_type, $searchable_rooms)){
                    array_push($searchable_rooms, $prop->room_type);
                }
            }
        }

        $rooms = RoomTypes::where('property_id', $residence->id)->where('name', '!=', 'Building Manager')->whereIn('id', $searchable_rooms)->get();

        return $rooms;
    }

    public function re_verify_email(Request $request){

        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        $userInfo = StudentsInfo::where('userUid', $request->userUid)->where('year', $this_year)->first();
        $user = User::where('id', $request->userUid)->first();
        $booking = Booking::where('userId', $request->userUid)->where('year', $this_year)->first();

        $generator = new LoginUrl($user);
        $generator->setRedirectUrl('/verify-new-email-account');
        $url = $generator->generate();

        $message_replace['bookingRef'] = $booking->booking_code;
        $message_replace['name'] = $userInfo->userFirstName;
        $message_replace['application_link'] = "<a href='".$url."' target='_blank'>";
        $message_replace['close_application_link']="</a>";

        $mailerForMessage = MailerList::where("mailer_name", "Password Validation From Admin")->first();
        $mailerMessage = $mailerForMessage->message;

        $newMessage = str_replace("[application_number]", "#".$message_replace['bookingRef'], $mailerMessage);
        $newMessage = str_replace("[name]", $message_replace['name'], $newMessage);
        $newMessage = str_replace("[application_link]", $message_replace['application_link'], $newMessage);
        $newMessage = str_replace("[close_application_link]", $message_replace['close_application_link'], $newMessage);

        $data['name'] = $userInfo->userFirstName;
        $data['email'] = $userInfo->userEmail;
        $data['messageForMail'] = $newMessage;
        $data['subject'] = str_replace("[application_number]", $message_replace['bookingRef'], $mailerForMessage->subject);

        $allowedMailsCC = Lookups::where('group_id', '11')->pluck('lookup_name')->first();
        $data['cc'] = explode(";", $allowedMailsCC);

        $allowedMails = Lookups::where('group_id', '10')->pluck('lookup_name')->first();

        if(!empty($allowedMails)){

            $mailArr = explode(";", $allowedMails);
    
            if(in_array(explode("@", $data['email'])[1], $mailArr)){
                $data['email'] = $userInfo->userEmail;
            }else{
                $data['email'] = "conurbandigital@gmail.com";
            }
        }else{
            $data['email'] = $userInfo->userEmail;
        }


        Mail::send('email.yellow_back', $data, function($message) use ($data) {
            // $message->to($data['email'], $data['name'])->subject
            //     ('Campus Africa User Application');
            $message->to($data['email'])->subject($data['subject']);
            $message->bcc($data['cc']);
            $message->from('noreply@campusafrica.co.za','Campus Africa');
        });
    }

    public function retry_payment(Request $request){
        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        $lookups = Lookups::get();
        $user = User::where('id', Auth::user()->id)->first();
        $userInfo = StudentsInfo::where('userUid', Auth::user()->id)->where('year', $this_year)->first();
        $agent = new Agent();
        $transaction = OnlinePayments::where("userUid", Auth::user()->id)->orderBy("created_at", "desc")->first();

        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        $booking = Booking::where('userId', Auth::user()->id)->where('year', $this_year)->first();
        $propertyTypeInfo = Residence::where('id', $booking->property)->first();

        return view('application.retry', ["lookups" => $lookups, "user" => $userInfo, "agent" => $agent, "transaction" => $transaction, "booking" => $booking, "propertyTypeInfo" => $propertyTypeInfo]);

    }

    public function new_booking_step_1(Request $request){

        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        $lookups = Lookups::get();
        $residences = Residence::get();
        $user = User::where('id', Auth::user()->id)->first();
        $userInfo = StudentsInfo::where('userUid', Auth::user()->id)->orderBy('year', 'desc')->first();
        $cancelled = 0;

        if($userInfo->rob_60_cancelled == 1 || $userInfo->year < $this_year - 1){
            $cancelled = 1;
        }
        return view('user.new_booking.step_1', ["user" => $userInfo, "cancelled" => $cancelled, "lookups" => $lookups, "residences" => $residences]);
    }

    public function new_booking_step_1_save(Request $request){

        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;
        
        $updateData = [
            'First Name|userFirstName' => $request->firstName,
            'Last Name|userLastname' => $request->lastName,
            'Title|userTitle' => $request->title,
            'Cellphone Number|userCell' => $request->cell_no,
            'Cellphone Country Code|userCellCode' => $request->userCellCode,
            'institution|institutionStudy' => $request->institStudy,
            'Student Number|studentNo' => $request->studNumber,
            'Faculty|faculty' => $request->faculty,
            'Year|year' => $request->year,
            'Street Address|userAddress1' => $request->userAddress1,
            'Suburb|userAddress2' => $request->userAddress2,
            'City|userAddressCity' => $request->userAddressCity,
            'Postal Code|userAddressPostal' => $request->userAddressPostal,
            'Student Type|studentType' => $request->studentType,
            'Year of Study|yearOfStudy' => $request->yearOfStudy,
        ];

        // $userInfo = User::where('id', Auth::user()->id)->first();
        $userInfo = StudentsInfo::where('userUid', Auth::user()->id)->orderBy('year', 'desc')->first();

        foreach($updateData as $key => $new){
            $info = explode("|", $key);
            $key = $info[1];
            $changedField = $info[0];

            if($userInfo->$key !== $new){
                ActionList::create([
                    "userUid" => Auth::user()->id,
                    "action" => "Updated ".$changedField." From ".$userInfo->$key." to ".$new,
                    "actionUserUid" => Auth::user()->id,
                    "actionUserDisplayName" => $request->firstName,
                    "actionUserRole" => Auth::user()->userRole,
                    "year" => $this_year,
                ]);
            }
        }

        $newStudentData = [
            'userUid' => Auth::user()->id,
            'userDob' => $userInfo->userDob,
            'userEmail' => $userInfo->userEmail,
            'userId' => $userInfo->userId,
            'userNationality' => $userInfo->userNationality,
            'userPassport' => $userInfo->userPassport,
            'userFirstName' => $request->firstName,
            'userLastname' => $request->lastName,
            'userTitle' => $request->title,
            'userCell' => $request->cell_no,
            'userCellCode' => $request->userCellCode,
            'institutionStudy' => $request->institStudy,
            'studentNo' => $request->studNumber,
            'faculty' => $request->faculty,
            'year' => $request->year,
            'userAddress1' => $request->userAddress1,
            'userAddress2' => $request->userAddress2,
            'userAddressCity' => $request->userAddressCity,
            'userAddressPostal' => $request->userAddressPostal,
            'studentType' => $request->studentType,
            'yearOfStudy' => $request->yearOfStudy,
            'rob_10_fee' => 0,
            'rob_15_id' => 0,
            'rob_20_card' => 0,
            'rob_25_reg' => 0,
            'rob_30_funds' => 0,
            'rob_35_info' => 0,
            'rob_40_bed' => 1,
            'rob_50_approve' => 0,
            'rob_55_lease' => 3,
            'rob_45_other' => 3,
            'rob_60_cancelled' => 0,
            'email_data_approved' => 0,
            'onboard_complete' => 0,
            'email_verified' => 0,
            'fee_approved' => 0,
            'id_data_approved' => 0,
            'student_card_approved' => 0,
            'registration_approved' => 0,
            'bursary_approved' => 0,
            'bank_approved' => 0,
            'address_approved' => 0,
            'kin_approved' => 0,
            'parent_approved' => 0,
            'burser_approved' => 0,
            'email_info' => 0,
            'app_source' => 1,
        ];

        if($userInfo->year !== $request->year){
            $user = StudentsInfo::create($newStudentData);

            ActionList::create([
                "userUid" => $request->userUid,
                "action" => "Created a new Booking ",
                "actionUserUid" => Auth::user()->id,
                "actionUserDisplayName" => Auth::user()->userFirstName,
                "actionUserRole" => Auth::user()->userRole,
                "year" => $request->year,
            ]);
        }else{
            $user = StudentsInfo::where('userUid', $request->userUid)->where('year', $request->year)->update($newStudentData);
        }

        User::where('id', $request->userUid)->update([
            'name' => $request->firstName,
            'email' => $request->userEmail,
            'userId' => $request->userId,
            'userNationality' => $request->userNationality,
            'userPassport' => $request->userPassport,
            'year' => $request->year,
        ]);


        //Booking data        
        $booking_data = array(
            "userId" => Auth::user()->id, 
            "property" => $request->property, 
            "roomType" => $request->roomType,
            "year" => $request->year
        );

        $lookup = Lookups::where('group_id', '7')->first();

        $oldPropReq = $request->property;
        $roomId = $request->roomType;

        $request->property = Residence::where('id', $request->property)->pluck('residence')->first();
        $request->roomType = RoomTypes::where('id', $request->roomType)->pluck('name')->first();

        $roomTypeData = RoomTypes::where('id', $roomId)->first();
        $roomTypeId = $roomTypeData->room_map_slug;

        $allResidences = Residence::get();
        $allRoomTypes = RoomTypes::get();


        if($lookup && $lookup->lookup_name == "1"){
            $booking_data['room_rate'] = "0";
            $actionFileData['action'] = "Booking Fee Waived";
        }else{
            //Get residences from WP
            $jsonDataResidence = json_decode(file_get_contents('https://www.campusafrica.co.za/wp-json/wp/v2/residences'));
            $residenceId = "";

            foreach($jsonDataResidence as $residence){
                if($residence->title->rendered == $request->property){
                    //get room booking fee from residence (WP)
                    $booking_data['room_rate'] = $residence->acf->booking_fee;
                    //get residence ID (WP)
                    $residenceId = $residence->id;
                }
            }

            $jsonDataRooms = json_decode(file_get_contents('https://www.campusafrica.co.za/wp-json/wp/v2/rooms?per_page=100'));

            $roomType = "";
            $roomName = "";

            foreach($jsonDataRooms as $room){
                if($residenceId == $room->acf->residence){

                    if($room->id == $roomTypeId){
                        $roomType = $request->roomType;
                        $roomName = $room->slug;
    
                        $appliesTo = $room->acf->booking_fee_applies_to;
    
                        if($room->slug == $roomName && $roomType == $request->roomType){
    
                            if($room->acf->booking_fee_override !== ""){
                                $booking_data['room_rate'] = $room->acf->booking_fee_override;
                            }
    
                            if($request->studentType === "NSFAS"){
                                if(!in_array("NSFAS Students", $appliesTo)){
                                    $booking_data['room_rate'] = "0";
                                    $actionFileData['action'] = "Booking Fee is R0";
                                }
                            }
    
                            if($request->studentType === "Bursary"){
                                if(!in_array("Bursary Students", $appliesTo)){
                                    $booking_data['room_rate'] = "0";
                                    $actionFileData['action'] = "Booking Fee is R0";
                                }
                            }
    
                            if($request->studentType === "Cash / Direct"){
                                if(!in_array("Cash Students", $appliesTo)){
                                    $booking_data['room_rate'] = "0";
                                    $actionFileData['action'] = "Booking Fee is R0";
                                }
                            }
                        } 
                    }

                }
            }
        }

        $user = StudentsInfo::where('userUid', Auth::user()->id)->where('year', $this_year)->first();

        if($roomTypeData && $roomTypeData->pull_price == null){
            $booking_data['room_rate'] = $roomTypeData->booking_price;

            if($user->studentType == "NSFAS"){
                if($roomTypeData->apply_nsfas == null){
                    $booking_data['room_rate'] = "0";
                    $actionFileData['action'] = "Booking Fee is R0";
                }
            }

            if($user->studentType == "Bursary"){
                if($roomTypeData->apply_bursary == null){
                    $booking_data['room_rate'] = "0";
                    $actionFileData['action'] = "Booking Fee is R0";
                }
            }

            if($user->studentType == "Cash / Direct"){
                if($roomTypeData->apply_cash == null){
                    $booking_data['room_rate'] = "0";
                    $actionFileData['action'] = "Booking Fee is R0";
                }
            }
        }
        


        //Get default lease data for the booking
        $lookup_lease_dates = Lookups::where(['group_id' => '4', 'lookup_name' => $request->year])->first();

        $booking_data['lease_from_date'] = $lookup_lease_dates['lease_from_date'];
        $booking_data['lease_from_time'] = $lookup_lease_dates['lease_from_time'];
        $booking_data['lease_to_date'] = $lookup_lease_dates['lease_to_date'];
        $booking_data['lease_to_time'] = $lookup_lease_dates['lease_to_time'];

        // $booking = Booking::create($booking_data);
        $booking_search = Booking::where('userId', $request->userUid)->where('year', $request->year)->first();

        if(empty($booking_search)){
            $booking = Booking::create($booking_data);
        }else{
            Booking::where('userId', $request->userUid)->where('year', $request->year)->update($booking_data);
            $booking = $booking_search;
        }

        $bookingYear = substr($request->year, -2);
        $bookingPrefix = Residence::where('id', $oldPropReq)->pluck('building_prefix')->first();
        $id_booking = $booking->id;
        $placeholder = "";
        for($i = 0; $i < 5 - strlen($id_booking); $i++){
            $placeholder .= "0";
        }
        $bookingID = $placeholder.$id_booking;
        Booking::where('id', $booking->id)->update(['booking_code' => $bookingYear.$bookingPrefix.$bookingID]);

        //Link booking to User
        // User::where('id', Auth::user()->id)->update(["bookingRef" => $booking->id]);
        StudentsInfo::where('userUid', Auth::user()->id)->where('year', $this_year)->update(["bookingRef" => $booking->id, "bookingId" => $booking->id]);

        $residence = Residence::where('residence', $request->property)->first();

        if($booking_data['room_rate'] == "0"){

            // User::where('id', Auth::user()->id)->update(["rob_10_fee" => 2]);
            StudentsInfo::where('userUid', Auth::user()->id)->where('year', $this_year)->update(["rob_10_fee" => 2]);

            $this_year = Lookups::where('group_id', '4')->first();
            $this_year = $this_year->lookup_name;

            $findPop = UserFiles::where('userUid', Auth::user()->id)->where('fileId', 'proof_of_payment')->where('year', $this_year)->first();

            if(empty($findPop)){
                UserFiles::create([
                    'userUid'=> Auth::user()->id,
                    'fileId' => 'proof_of_payment',
                    'status' => 'Approved',
                    'year' => $this_year
                ]);
            }else{
                UserFiles::where('userUid', Auth::user()->id)->where('fileId', 'proof_of_payment')->where('year', $this_year)->update([
                    'status' => 'Approved',
                ]);
            }

            $actionFileData['userUid'] = Auth::user()->id;
            $actionFileData['actionUserUid'] = Auth::user()->id;
            $actionFileData['actionUserDisplayName'] = $user->userFirstName;
            $actionFileData['actionUserRole'] = Auth::user()->userRole;
            $actionFileData['year'] = $this_year;
        
            ActionList::create($actionFileData);
        }

        if($request->studentType == "NSFAS"){
            
            $nsfasStudentsApproval = ImportNSFASStudentAuditLog::where('student_number', $request->studNumber)->orderBy('created_at', 'desc')->first();

            if(!empty($nsfasStudentsApproval)){

                $yearOfImport = ImportNSFASApproval::where('id', $nsfasStudentsApproval->importId)->first();
                $this_year = Lookups::where('group_id', '4')->first();
    
                if(!empty($nsfasStudentsApproval) && $yearOfImport->year == $this_year->lookup_name){

                    $findBa = UserFiles::where('userUid', Auth::user()->id)->where('fileId', 'bursaryApproval')->where('year', $this_year->lookup_name)->first();

                    if(empty($findBa)){
                        UserFiles::create([
                            'userUid'=> Auth::user()->id,
                            'fileId' => 'bursaryApproval',
                            'status' => 'Approved',
                            'year' => $this_year->lookup_name
                        ]);
                    }else{
                        UserFiles::where('userUid', Auth::user()->id)->where('fileId', 'bursaryApproval')->where('year', $this_year->lookup_name)->update([
                            'status' => 'Approved',
                        ]);
                    }
    
                    // User::where('id', Auth::user()->id)->update(["rob_30_funds" => 2]);
                    StudentsInfo::where('userUid', Auth::user()->id)->where('year', $this_year->lookup_name)->update(["rob_30_funds" => 2]);
    
                    ActionList::create([
                        "userUid" => Auth::user()->id,
                        // "action" => "NSFAS / Bursary has been Approved via import",
                        "action" => "NSFAS Approval has been Approved via import",
                        "actionUserUid" => Auth::user()->id,
                        "actionUserDisplayName" => $user->userFirstName,
                        "actionUserRole" => Auth::user()->userRole,
                        "year" => $this_year->lookup_name,
                    ]);
                }
            }
        }

        $user = User::where('id', Auth::user()->id)->first();
        $userInfo = StudentsInfo::where('userUid', Auth::user()->id)->where('year', $this_year)->first();

        $generator = new LoginUrl($user);
        $generator->setRedirectUrl('/verify-email-account');
        $url = $generator->generate();

        $message_replace['bookingRef'] = $bookingYear.$bookingPrefix.$bookingID;
        $message_replace['name'] = $request->firstName;
        $message_replace['application_link'] = "<a href='".$url."' target='_blank'>";
        $message_replace['close_application_link']="</a>";

        $mailerForMessage = MailerList::where("mailer_name", "New Booking")->first();

        if($mailerForMessage->status == '1'){

            $mailerMessage = $mailerForMessage->message;
    
            $newMessage = str_replace("[application_number]", "#".$message_replace['bookingRef'], $mailerMessage);
            $newMessage = str_replace("[name]", $message_replace['name'], $newMessage);
            $newMessage = str_replace("[application_link]", $message_replace['application_link'], $newMessage);
            $newMessage = str_replace("[close_application_link]", $message_replace['close_application_link'], $newMessage);
    
            $data['name'] = $request->firstName;
            $data['email'] = $user->email;
            $data['messageForMail'] = $newMessage;
            $data['subject'] = str_replace("[application_number]", $message_replace['bookingRef'], $mailerForMessage->subject);

            $allowedMailsCC = Lookups::where('group_id', '11')->pluck('lookup_name')->first();
            $data['cc'] = explode(";", $allowedMailsCC);


            $allowedMails = Lookups::where('group_id', '10')->pluck('lookup_name')->first();

            if(!empty($allowedMails)){
                $mailArr = explode(";", $allowedMails);
    
                if(in_array(explode("@", $data['email'])[1], $mailArr)){
                    $data['email'] = $user->email;
                }else{
                    $data['email'] = "conurbandigital@gmail.com";
                }
            }else{
                $data['email'] = $user->email;
            }
    
            Mail::send('email.yellow_back', $data, function($message) use ($data) {
                $message->to($data['email'])->subject($data['subject']);
                $message->bcc($data['cc']);
                $message->from('noreply@campusafrica.co.za','Campus Africa');
            });
        }


        return redirect('/new-booking-step-2');

    }

    public function new_booking_step_2(Request $request){
        $lookups = Lookups::get();

        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        // $user = User::where('id', Auth::user()->id)->first();
        
        $user = StudentsInfo::where('userUid', Auth::user()->id)->orderBy('year', 'desc')->where('year', '!=', $this_year)->first();
        $userNew = StudentsInfo::where('userUid', Auth::user()->id)->where('year', $this_year)->first();

        $agent = new Agent();

        if($userNew->studentType == "Cash / Direct")
        {
            return view('application.step_2_spon', ["lookups" => $lookups, "user" => $user, "agent" => $agent, "new" => 1, "userNew" => $userNew]);
        }
        elseif($userNew->studentType == "Bursary")
        {
            return view('application.step_2_bur', ["lookups" => $lookups, "user" => $user, "agent" => $agent, "new" => 1, "userNew" => $userNew]);
        }
        else
        {
            return view('application.step_3', ["lookups" => $lookups, "user" => $user, "agent" => $agent, "new" => 1, "userNew" => $userNew]);
        } 
    }

    public function new_booking_step_2_spon_save(Request $request){
        $user = User::where('id', Auth::user()->id)->first();

        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        $userInfo = StudentsInfo::where('userUid', Auth::user()->id)->where('year', $this_year)->first();

        if($request->app_address_p == 1){
            $request->pAddress1 = $userInfo->userAddress1;
            $request->pAddress2 = $userInfo->userAddress2;
            $request->pAddressCity = $userInfo->userAddressCity;
            $request->pAddressPostal = $userInfo->userAddressPostal;
        }

        StudentsInfo::where('userUid', $user->id)->where('year', $this_year)->update([
            'sponFirstName' => $request->pFirstName,
            'sponLastName' => $request->pLastName,
            'sponCell' => $request->pCell_no,
            'sponCellCode' => $request->sponCellCode,
            'sponEmail' => $request->pEmail,
            'pNationality' => $request->pNationality,
            'sponId' => $request->pIdNumber,
            'pPassportNo' => $request->pPassportNo,
            'pAddress1' => $request->pAddress1,
            'pAddress2' => $request->pAddress2,
            'pAddressCity' => $request->pAddressCity,
            'pAddressPostal' => $request->pAddressPostal,
            'app_address_p' => $request->app_address_p,
            'c_credit_check' => $request->c_credit_check,
            'c_credit_risk' => $request->c_credit_risk,
        ]);

        return "/new-booking-step-3";
    }

    public function new_booking_step_2_bur_save(Request $request){
        $user = User::where('id', Auth::user()->id)->first();

        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        StudentsInfo::where('userUid', $user->id)->where('year', $this_year)->update([
            'burCompanyName' => $request->bCompanyName,
            'burRegNo' => $request->bRegistrationNo,
            'burContactPerson' => $request->bContactPerson,
            'burContactNo' => $request->bContactNumber,
            'burContactCode' => $request->burContactCode,
            'burEmail' => $request->bEmail,
            'bAddress1' => $request->bAddress1,
            'bAddress2' => $request->bAddress2,
            'bAddressCity' => $request->bAddressCity,
            'bAddressPostal' => $request->bAddressPostal,
            'bPayment' => $request->bPayment,
            'bint' => $request->bint,
            'bIntName' => $request->bIntName,
        ]);

        return "/new-booking-step-3";
    }

    public function new_booking_step_3(Request $request){
        $lookups = Lookups::get();
        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;
        $user = StudentsInfo::where('userUid', Auth::user()->id)->orderBy('year', 'desc')->where('year', '!=', $this_year)->first();
        $userNew = StudentsInfo::where('userUid', Auth::user()->id)->where('year', $this_year)->first();
        $agent = new Agent();

        return view('application.step_3', ["lookups" => $lookups, "user" => $user, "agent" => $agent, "new" => 1, "userNew" => $userNew]);
        
    }

    public function new_booking_step_3_save(Request $request){
        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        $userInfo = StudentsInfo::where('userUid', Auth::user()->id)->where('year', $this_year)->first();

        if($request->app_parent_kin == 1){
            $request->kFirstName = $userInfo->sponFirstName;
            $request->kLastName = $userInfo->sponLastName;
            $request->kCellNo = $userInfo->sponCell;
            $request->kinCellCode = $userInfo->sponCellCode;
            $request->kEmail = $userInfo->sponEmail;
            $request->kAddress1 = $userInfo->pAddress1;
            $request->kAddress2 = $userInfo->pAddress2;
            $request->kAddressCity = $userInfo->pAddressCity;
            $request->kAddressPostal = $userInfo->pAddressPostal;
        }

        if($request->app_address == 1){
            $request->kAddress1 = $userInfo->userAddress1;
            $request->kAddress2 = $userInfo->userAddress2;
            $request->kAddressCity = $userInfo->userAddressCity;
            $request->kAddressPostal = $userInfo->userAddressPostal;
        }

        StudentsInfo::where('userUid', Auth::user()->id)->where('year', $this_year)->update([
            'app_parent_kin' => $request->app_parent_kin,
            'kinFirstName' => $request->kFirstName,
            'kinLastName' => $request->kLastName,
            'kinTitle' => $request->kTitle,
            'kinRelationship' => $request->kRelationship,
            'kinCellNo' => $request->kCellNo,
            'kinCellCode' => $request->kinCellCode,
            'kinEmail' => $request->kEmail,
            'kinWorkNo' => $request->kWorkNo,
            'kinWorkCode' => $request->kinWorkCode,
            'kinTelNo' => $request->kTeleNo,
            'kinTelCode' => $request->kinTelCode,
            'app_address' => $request->app_address,
            'kinAddressStr' => $request->kAddress1,
            'kinAddressSuburb' => $request->kAddress2,
            'kinAddressCity' => $request->kAddressCity,
            'kinAddressPost' => $request->kAddressPostal,
        ]);

        return "/new-booking-step-4";
    }

    public function new_booking_step_4(Request $request){
        $lookups = Lookups::get();
        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        $userInfo = StudentsInfo::where('userUid', Auth::user()->id)->orderBy('year', 'desc')->first();

        $IdCopy = UserFiles::where("userUid", Auth::user()->id)->where('fileId', 'copyID')->where('year', $this_year)->first();
        $StudentCopy = UserFiles::where("userUid", Auth::user()->id)->where('fileId', 'studentCard')->where('year', $this_year)->first();
        $BankCopy = UserFiles::where("userUid", Auth::user()->id)->where('fileId', 'bankStatement')->where('year', $this_year)->first();
        $BursaryCopy = UserFiles::where("userUid", Auth::user()->id)->where('fileId', 'bursaryApproval')->where('year', $this_year)->first();
        $RegistrationCopy = UserFiles::where("userUid", Auth::user()->id)->where('fileId', 'proofRegistration')->where('year', $this_year)->first();

        return view('application.step_4', ["lookups" => $lookups, "user" => $userInfo, "IdCopy" => $IdCopy, "StudentCopy" => $StudentCopy, "BankCopy" => $BankCopy, "BursaryCopy" => $BursaryCopy, "RegistrationCopy" => $RegistrationCopy, "new" => 1]);
    }

}

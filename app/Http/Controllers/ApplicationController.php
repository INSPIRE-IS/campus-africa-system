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

        User::where('id', $user->id)->update([
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
            $request->kinCellCode = $user->sponCellCode;
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

                Booking::where('userId', $userUid)->update(['payment_type' => 'Manual EFT']);

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

       
        $lookup = Lookups::where('group_id', '7')->first();
        $room_rate = 0;
        $actionFileData['action'] = "";

        $roomTypeInfo = RoomTypes::where('property_id', $booking->property)->where('id', $booking->roomType)->first();
        $propertyTypeInfo = Residence::where('id', $booking->property)->first();

        $allResidences = Residence::get();
        $allRoomTypes = RoomTypes::get();

        $property = Residence::where('id', $booking->property)->pluck('residence')->first();
        $roomTypeNew = RoomTypes::where('id', $booking->roomType)->pluck('name')->first();

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

                    $slugString = str_replace("-", " ", $room->slug);
                    $roomType = "";
                    $roomName = "";

                    foreach($allResidences as $residence){
                        if(strtoupper($property) == strtoupper($residence->residence)){
                            $postSlugStringName = trim(str_replace("room", "", str_replace(strtolower($residence->residence), "",$slugString)))."\r\n";
                            $requiredName = $roomTypeNew;

                            foreach($jsonDataRooms as $roomInfo){
                                if($residenceId == $roomInfo->acf->residence){
                                    if(strtolower(trim(str_replace("room", "", str_replace(strtolower($residence->residence), "",str_replace("-", " ", $roomInfo->slug))))) == strtolower($roomTypeNew)){
                                        $roomType = $roomTypeNew;
                                        $roomName = $roomInfo->slug;
                                        $priced_from = $roomInfo->acf->price_from;
                                        $beds = $roomInfo->acf->beds;
                                        break;
                                    }
                                }
                            }
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

                    User::where('id', $user->id)->update([
                        'rob_10_fee' => 2
                    ]);
                }else{
                    UserFiles::where('userUid', $user->id)->where('fileId', 'proof_of_payment')->update([
                        'status' => 'Approved'
                    ]);

                    User::where('id', $user->id)->update([
                        'rob_10_fee' => 2
                    ]);
                }
    
                Booking::where('userId', $user->id)->update(['room_rate' => $room_rate]);
    
                $actionFileData['userUid'] = $user->id;
                $actionFileData['action'] = $actionFileData['action'];
                $actionFileData['actionUserUid'] = Auth::user()->id;
                $actionFileData['actionUserDisplayName'] = Auth::user()->userFirstName;
                $actionFileData['actionUserRole'] = Auth::user()->userRole;
            
                ActionList::create($actionFileData);
            }else{
               Booking::where('userId', $user->id)->update(['room_rate' => $room_rate]);
            }

            $newBooking = Booking::where('userId', $user->id)->first();


        return view('application.step_5', ["lookups" => $lookups, "user" => $user, "booking" => $newBooking, "proofPayment" => $proofPayment, "agent" => $agent, "priced_from" => $priced_from, "chair" => $chair, "wifi" => $wifi, "bath" => $bath, "kitchen" => $kitchen, "beds" => $beds, "room_rate" => $room_rate, "roomTypeInfo" => $roomTypeInfo, "propertyTypeInfo" => $propertyTypeInfo]);
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
        $agent = new Agent();

        return view('user.changeType', ["headerTitle" => "Change Student Type", "lookups" => $lookups, "user" => $user, "agent" => $agent]);
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

        User::where('id', Auth::user()->id)->update(['onboard_complete' => 2, 'email_verified' => 1]);

        return redirect('/dashboard');
    }

    public function verify_new_email_account(Request $request){
        User::where('id', Auth::user()->id)->update(['email_verified' => 1]);

        return redirect('/dashboard');
    }

    public function loginAsStudent($userid){
        Auth::loginUsingId($userid, true);
        return redirect('/dashboard');
    }

    public function get_room_ids(Request $request){
        $property = $request->residence;

        $residence = Residence::where('id', $property)->first();

        $rooms = RoomTypes::where('property_id', $residence->id)->where('name', '!=', 'Building Manager')->get();

        return $rooms;
    }

    public function re_verify_email(Request $request){

        $user = User::where('id', $request->userUid)->first();
        $booking = Booking::where('userId', $request->userUid)->first();

        $generator = new LoginUrl($user);
        $generator->setRedirectUrl('/verify-new-email-account');
        $url = $generator->generate();

        $message_replace['bookingRef'] = $booking->booking_code;
        $message_replace['name'] = $user->userFirstName;
        $message_replace['application_link'] = "<a href='".$url."' target='_blank'>";
        $message_replace['close_application_link']="</a>";

        $mailerForMessage = MailerList::where("mailer_name", "Password Validation From Admin")->first();
        $mailerMessage = $mailerForMessage->message;

        $newMessage = str_replace("[application_number]", "#".$message_replace['bookingRef'], $mailerMessage);
        $newMessage = str_replace("[name]", $message_replace['name'], $newMessage);
        $newMessage = str_replace("[application_link]", $message_replace['application_link'], $newMessage);
        $newMessage = str_replace("[close_application_link]", $message_replace['close_application_link'], $newMessage);

        $data['name'] = $user->userFirstName;
        $data['email'] = $user->userEmail;
        $data['messageForMail'] = $newMessage;
        $data['subject'] = $mailerForMessage->subject;

        if(explode("@", $data['email'])[1] == "conurban.co.za"){
            $data['email'] = $user->userEmail;
        }else{
            $data['email'] = "conurbandigital@gmail.com";
        }

        Mail::send('email.yellow_back', $data, function($message) use ($data) {
            // $message->to($data['email'], $data['name'])->subject
            //     ('Campus Africa User Application');
            $message->to($data['email'])->subject($data['subject']);
            $message->cc(['conurbandigital@gmail.com', 'roger@whitehart.co.za', 'mirichea@whitehart.co.za']);
            $message->from('noreply@campusafrica.co.za','Campus Africa');
        });
    }

}

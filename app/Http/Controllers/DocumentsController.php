<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserFiles;
use Auth;
use App\Models\ActionList;
use App\Models\Lookups;
use App\Models\Booking;
use App\Models\Residence;

class DocumentsController extends Controller {

    function get_user_status($fileType, $user) {

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
            $typeName = "NSFAS / Bursary Approval";
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
    
    public function view_document($id) {        
        $file = UserFiles::find($id);
        $user = User::where('id', $file->userUid)->first();
        $booking = Booking::where('userId', $file->userUid)->first();

        $property = Residence::where('residence', $booking->property)->first();

        $field = "None";
        $popupTitle = "";
        $doc_approved = 0;

        $user_status = $this->get_user_status($file->fileId, $user);

        $lookups = Lookups::get();

        $data = [
            'user' => $user, 
            'file' => $file, 
            'lookups' => $lookups, 
            'popupTitle' => $user_status['typeName'], 
            'data_approved' => $user_status['data_approved'], 
            'doc_approved' => $user_status['doc_approved'], 
            'field_data' => $user_status['field_data'], 
            'booking' => $booking, 
            'property' => $property
        ];

        return view('application.document.view_document', $data);
    }




    public function edit_data_form($id, $type) {
        $user = User::find($id);

        $lookups = Lookups::get();

        return view('application.document.edit_data', ['user' => $user, 'lookups' => $lookups, 'type' => $type]);
    }


    
    public function edit_data_save(Request $request) {
        $oldData = User::where("id", $request->userUid)->first();

        if ($request->type == "copyID") {
            $newData = [
                'userFirstName|First Name' => $request->userFirstName,
                'userLastname|Last Name' => $request->userLastname,
                'userDob|Date of Birth' => $request->userDob,
                'userNationality|Nationality' => $request->nationality,
                'userPassport|Passport Number' => $request->userPassport,
                'userId|I.D Number' => $request->userId
            ];

            $updateData = [
                'userFirstName' => $request->userFirstName,
                'userLastname' => $request->userLastname,
                'userDob' => $request->userDob,
                'userId' => $request->userId,
                'userNationality' => $request->nationality,
                'userPassport' => $request->userPassport
            ];

        } elseif ($request->type == "studentCard") {
            $newData = [
                'studentNo|Student Number' => $request->studentNo
            ];

            $updateData = [
                'studentNo' => $request->studentNo
            ];

        } elseif ($request->type == "proofRegistration") {
            $newData = [
                'institutionStudy|Institution of Study' => $request->institStudy,
                'faculty|Faculty' => $request->faculty,
                'yearOfStudy|Year of Study' => $request->yearOfStudy,
            ];

            $updateData = [
                'institutionStudy' => $request->institStudy,
                'faculty' => $request->faculty,        
                'yearOfStudy' => $request->yearOfStudy,        
            ];

        } elseif ($request->type == "bursaryApproval") {
            $newData = [
                'studentType|Student Type' => $request->studentType,
                'burCompanyName|Burser Company Name' => $request->burCompanyName,
                'burRegNo|Burser Registration Number' => $request->burRegNo,
                'burContactPerson|Burser Contact Person' => $request->burContactPerson,
                'burContactNo|Burser Contact Number' => $request->burContactNo,
                'burEmail|Burser Email' => $request->burEmail,                
                'nsfas_bursar|NSFAS Bursar' => $request->nsfas_bursar,
            ];

            $updateData = [
                'studentType' => $request->studentType, 
                'burCompanyName' => $request->burCompanyName,  
                'burRegNo' => $request->burRegNo,
                'burContactPerson' => $request->burContactPerson,
                'burContactNo' => $request->burContactNo,
                'burEmail' => $request->burEmail, 
                'nsfas_bursar' => $request->nsfas_bursar,
            ];

        } elseif ($request->type == "bankStatement") {
            $newData = [
                'studentType|Student Type' => $request->studentType,
                'sponFirstName|Parent / Sponser First Name' => $request->sponFirstName,
                'sponLastName| Parent / Sponser Last Name' => $request->sponLastName,
                'pNationality| Sponsor / Parent Nationality' => $request->pNationality,
                'sponId| Sponsor / Parent I.D Number' => $request->sponId,
                'pPassportNo| Sponsor / Parent Passport' => $request->pPassportNo,
            ];

            $updateData = [
                'studentType' => $request->studentType, 
                'sponFirstName' => $request->sponFirstName,
                'sponLastName' => $request->sponLastName,                
                'pNationality' => $request->pNationality,
                'sponId' => $request->sponId,
                'pPassportNo' => $request->pPassportNo,
            ];
        }

        //$newData = ['userTitle|Title' => $request->title,'userCell|Cellphone Number' => $request->cell_no,'userEmail|Email' => $request->email,'year|Year' => "2022",'sponCell|Parent / Sponser Cellphone Number' => $request->pCell_no,'sponEmail|Parent / Sponser Email' => $request->pEmail,'kinFirstName|Next of Kin First Name' => $request->kFirstName,'kinLastName|Next of Kin Last Name' => $request->kLastName,'kinTitle|Next of Kin Title' => $request->kTitle,'kinId|Next of Kin I.D Number' => $request->kIdNumber,'kinAddressStr|Next of Kin Street Number' => $request->kAddress1,'kinAddressSuburb|Next of Kin Suburb' => $request->kAddress2,'kinAddressCity|Next of Kin City' => $request->kAddressCity,'kinAddressPost|Next of Kin Postal Code' => $request->kAddressPostal,'kinRelationship|Next of Kin Relationship' => $request->kRelationship,'kinTelNo|Next of Kin Telephone Number' => $request->kTeleNo,'kinCellNo|Next of Kin Cellphone Number' => $request->kCellNo,'kinWorkNo|Next of Kin Work Number' => $request->kWorkNo,'kinEmail|Next of Kin Email' => $request->kEmail,'kinNationality|Next of Kin Nationality' => $request->kinNationality,'kinPassportNo|Next of Kin Passport Number' => $request->kinPassportNo,'pAddress1|Parent / Sponser Street Number' => $request->pAddress1,'pAddress2|Parent / Sponser Suburb' => $request->pAddress2,'pAddressCity|Parent / Sponser City' => $request->pAddressCity,'pAddressPostal|Parent / Sponser Postal Code' => $request->pAddressPostal,'userAddress1|Street Number' => $request->userAddress1,'userAddress2|Suburb' => $request->userAddress2,'userAddressCity|City' => $request->userAddressCity,'userAddressPostal|PostalCode' => $request->userAddressPostal, 'bAddress1|Burser Street Number' => $request->bAddress1,'bAddress2|Burser Suburb' => $request->bAddress2,'bAddressCity|Burser City' => $request->bAddressCity,'bAddressPostal|Burser Postal Code' => $request->bAddressPostal,'bPayment|Burser Payment' => $request->bPayment,'bint|Burser Intermediary' => $request->bint,'bIntName|Burser Intermediary Name' => $request->bIntName,];

        

        if (isset($newData)) {
            foreach($newData as $key => $new){
                
                $info = explode("|", $key);
                $key = $info[0];
                $changedField = $info[1];

                if($changedField == "NSFAS Bursar"){
                    
                    if($oldData->$key !== 1){
                        $oldData->$key = "No";
                    }else{
                        $oldData->$key = "Yes";
                    }

                    if($new == 1){
                        $new = "Yes";
                    }else{
                        $new = "No";
                    }

                }


                if($oldData->$key !== $new){
                    ActionList::create([
                        "userUid" => $request->userUid,
                        "action" => "Updated ".$changedField." From ".$oldData->$key." to ".$new,
                        "actionUserUid" => Auth::user()->id,
                        "actionUserDisplayName" => Auth::user()->userFirstName,
                        "actionUserRole" => Auth::user()->userRole,
                    ]);
                }
            }

            //$updateData

            User::where('id', $request->userUid)->update($updateData);
        }

        // User::where('id', $request->userUid)->update([
            
            // 'kinNationality' => $request->kinNationality,
            //     'kinPassportNo' => $request->kinPassportNo,
        //     'userTitle' => $request->title,            
        //     'userCell' => $request->cell_no,
        //     'userEmail' => $request->email,
        
        //     // 'institutionCampus' => $request->institCampus,
        //     'year' => "2022",
        
        //     'sponCell' => $request->pCell_no,
        //     'sponEmail' => $request->pEmail,
        
        
        
        //     'kinFirstName' => $request->kFirstName,
        //     'kinLastName' => $request->kLastName,
        //     'kinTitle' => $request->kTitle,
        //     'kinId' => $request->kIdNumber,
        //     'kinAddressStr' => $request->kAddress1,
        //     'kinAddressSuburb' => $request->kAddress2,
        //     'kinAddressCity' => $request->kAddressCity,
        //     'kinAddressPost' => $request->kAddressPostal,
        //     'kinRelationship' => $request->kRelationship,
        //     'kinTelNo' => $request->kTeleNo,
        //     'kinCellNo' => $request->kCellNo,
        //     'kinWorkNo' => $request->kWorkNo,
        //     'kinEmail' => $request->kEmail,
        
        //     'pAddress1' => $request->pAddress1,
        //     'pAddress2' => $request->pAddress2,
        //     'pAddressCity' => $request->pAddressCity,
        //     'pAddressPostal' => $request->pAddressPostal,
        //     'userAddress1' => $request->userAddress1,
        //     'userAddress2' => $request->userAddress2,
        //     'userAddressCity' => $request->userAddressCity,
        //     'userAddressPostal' => $request->userAddressPostal,
        //     'app_address' => $request->app_address,
        //     'app_address_p' => $request->app_address_p,
        //     'app_parent_kin' => $request->app_parent_kin,
        //     'bAddress1' => $request->bAddress1,
        //     'bAddress2' => $request->bAddress2,
        //     'bAddressCity' => $request->bAddressCity,
        //     'bAddressPostal' => $request->bAddressPostal,
        //     'bPayment' => $request->bPayment,
        //     'bint' => $request->bint,
        //     'bIntName' => $request->bIntName,
        //     // 'nsfas_bursar' => $request->nsfas_bursar,
        // ]);

        return "done";
    }




    public function adminFileUpload(Request $request){
        $userUid = $request->userUid;
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

                $user_status = $this->get_user_status($fileType, "");
                $field_data = $user_status['field_data'];
                $field_doc = $user_status['field_doc'];                

                if(!$hasFile){
                    UserFiles::create([
                        'fileId' => $fileType,
                        'userUid' => $userUid,
                        'fileName' => $fileNameToStore,
                        'fileUrl' => $fileUrl,
                        'status' => "Uploaded",
                        'uploaded_by' => Auth::user()->userFirstName,
                    ]);

                    $text = "Uploaded ".$user_status['typeName']." for review";
                }else{
                    UserFiles::where('userUid', $userUid)->where('fileId', $fileType)->update([
                        'fileId' => $fileType,
                        'userUid' => $userUid,
                        'fileName' => $fileNameToStore,
                        'fileUrl' => $fileUrl,
                        'status' => "Uploaded",
                        'uploaded_by' => Auth::user()->userFirstName,
                    ]);
                    $text = "Re-uploaded ".$user_status['typeName']." for review";                    
                }

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
                    "actionUserUid" => Auth::user()->id,
                    "actionUserDisplayName" => Auth::user()->userFirstName,
                    "actionUserRole" => Auth::user()->userRole,
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

}

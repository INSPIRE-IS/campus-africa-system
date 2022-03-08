<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserFiles;
use Auth;
use App\Models\ActionList;
use App\Models\Lookups;

class DocumentsController extends Controller {
    
    public function view_document($id) {        
        $file = UserFiles::find($id);
        $user = User::where('id', $file->userUid)->first();

        $field = "None";
        $popupTitle = "";
        $doc_approved = 0;

        $user_status = get_user_status($file->fileId, $user);

        // if($file->fileId == "copyID") {            
        //     $doc_approved = $user->id_data_approved;

        // }elseif($file->fileId == "studentCard"){
        //     $doc_approved = $user->student_card_approved;

        // }elseif($file->fileId == "bankStatement"){
        //     $doc_approved = $user->bank_approved;
            
        // }elseif($file->fileId == "proofRegistration"){
        //     $doc_approved = $user->registration_approved;

        //     //TODO: What is this fileId?

        // }elseif($file->fileId == "proof_of_payment"){
        //     $popupTitle = "Proof of payment";
        //     $doc_approved = 1;

        // }elseif($file->fileId == "moveIn"){
        //     // $typeName = "Lease";
        //     // User::where('id', $userUid)->update(['overall_status' => 'Pending']);
        //     $popupTitle = "Lease";
        //     $doc_approved = 1;

        // }elseif($file->fileId == "bursaryApproval"){
        //     $field = "bursary_approved";
        //     $popupTitle = "NSFAS / Bursary Approval";
        //     //TODO: Split this up based on student type

        //     $doc_approved = $user->bursary_approved;
        // }

        $lookups = Lookups::get();

        return view('application.document.view_document', ['user' => $user, 'file' => $file, 'lookups' => $lookups, 'popupTitle' => $user_status['typeName'], 'data_approved' => $user_status['data_approved'], 'doc_approved' => $user_status['doc_approved'], 'field_data' => $user_status['field_data']]);
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
                'userFirstName|First Name' => $request->firstName,
                'userLastname|Last Name' => $request->lastName,
                'userDob|Date of Birth' => $request->dob,
                'userNationality|Nationality' => $request->nationality,
                'userPassport|Passport Number' => $request->passportNo,
                'userId|I.D Number' => $request->idNumber
            ];

            $updateData = [
                'userFirstName' => $request->firstName,
                'userLastname' => $request->lastName,
                'userDob' => $request->dob,
                'userId' => $request->idNumber,
                'userNationality' => $request->nationality,
                'userPassport' => $request->passportNo
            ];

        } elseif ($request->type == "studentCard") {
            $newData = [
                'studentNo|Student Number' => $request->studentNo
            ];

            $updateData = [
                'studentNo' => $request->studentNo
            ];
        }

        //$newData = ['userTitle|Title' => $request->title,'userCell|Cellphone Number' => $request->cell_no,'userEmail|Email' => $request->email,'institutionStudy|Institution of Study' => $request->institStudy,'studentType|Student Type' => $request->studentType,'faculty|Faculty' => $request->faculty,'year|Year' => "2022",'sponFirstName|Parent / Sponser First Name' => $request->pFirstName,'sponLastName| Parent / Sponser Last Name' => $request->pLastName,'sponCell|Parent / Sponser Cellphone Number' => $request->pCell_no,'sponEmail|Parent / Sponser Email' => $request->pEmail,'sponId|Parent / Sponser I.D Number' => $request->pIdNumber,'burCompanyName|Burser Company Name' => $request->bCompanyName,'burRegNo|Burser Registration Number' => $request->bRegistrationNo,'burContactPerson|Burser Contact Person' => $request->bContactPerson,'burContactNo|Burser Contact Number' => $request->bContactNumber,'burEmail|Burser Email' => $request->bEmail,'kinFirstName|Next of Kin First Name' => $request->kFirstName,'kinLastName|Next of Kin Last Name' => $request->kLastName,'kinTitle|Next of Kin Title' => $request->kTitle,'kinId|Next of Kin I.D Number' => $request->kIdNumber,'kinAddressStr|Next of Kin Street Number' => $request->kAddress1,'kinAddressSuburb|Next of Kin Suburb' => $request->kAddress2,'kinAddressCity|Next of Kin City' => $request->kAddressCity,'kinAddressPost|Next of Kin Postal Code' => $request->kAddressPostal,'kinRelationship|Next of Kin Relationship' => $request->kRelationship,'kinTelNo|Next of Kin Telephone Number' => $request->kTeleNo,'kinCellNo|Next of Kin Cellphone Number' => $request->kCellNo,'kinWorkNo|Next of Kin Work Number' => $request->kWorkNo,'kinEmail|Next of Kin Email' => $request->kEmail,'kinNationality|Next of Kin Nationality' => $request->kinNationality,'kinPassportNo|Next of Kin Passport Number' => $request->kinPassportNo,'pNationality|Parent / Sponser Nationality' => $request->pNationality,'pPassportNo|Parent / Sponser Passport Number' => $request->pPassportNo,'pAddress1|Parent / Sponser Street Number' => $request->pAddress1,'pAddress2|Parent / Sponser Suburb' => $request->pAddress2,'pAddressCity|Parent / Sponser City' => $request->pAddressCity,'pAddressPostal|Parent / Sponser Postal Code' => $request->pAddressPostal,'userAddress1|Street Number' => $request->userAddress1,'userAddress2|Suburb' => $request->userAddress2,'userAddressCity|City' => $request->userAddressCity,'userAddressPostal|PostalCode' => $request->userAddressPostal, 'bAddress1|Burser Street Number' => $request->bAddress1,'bAddress2|Burser Suburb' => $request->bAddress2,'bAddressCity|Burser City' => $request->bAddressCity,'bAddressPostal|Burser Postal Code' => $request->bAddressPostal,'bPayment|Burser Payment' => $request->bPayment,'bint|Burser Intermediary' => $request->bint,'bIntName|Burser Intermediary Name' => $request->bIntName,];

        

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
            
        //     'userTitle' => $request->title,
            
        //     'userCell' => $request->cell_no,
        //     'userEmail' => $request->email,
            
        //     'institutionStudy' => $request->institStudy,        
        //     'studentType' => $request->studentType,
        //     // 'institutionCampus' => $request->institCampus,
        //     'faculty' => $request->faculty,
        //     'year' => "2022",
        //     'sponFirstName' => $request->pFirstName,
        //     'sponLastName' => $request->pLastName,
        //     'sponCell' => $request->pCell_no,
        //     'sponEmail' => $request->pEmail,
        //     'sponId' => $request->pIdNumber,
        //     'burCompanyName' => $request->bCompanyName,
        //     'burRegNo' => $request->bRegistrationNo,
        //     'burContactPerson' => $request->bContactPerson,
        //     'burContactNo' => $request->bContactNumber,
        //     'burEmail' => $request->bEmail,
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
        //     'kinNationality' => $request->kinNationality,
        //     'kinPassportNo' => $request->kinPassportNo,
        //     'pNationality' => $request->pNationality,
        //     'pPassportNo' => $request->pPassportNo,
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

                $user_status = get_user_status($fileType, "");
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

                return $file->id;

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

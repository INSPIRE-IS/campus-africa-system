<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserFiles;
use Auth;
use App\Models\ActionList;

class DocumentsController extends Controller {
    
    public function view_document($id) {        
        $file = UserFiles::find($id);
        $user = User::where('id', $file->userUid)->first();

        $field = "None";
        $popupTitle = "";
        $doc_approved = 0;

        if($file->fileId == "copyID"){
            $field = "id_data_approved";
            $popupTitle = "Copy Of ID document";

            $doc_approved = $user->id_data_approved;

        }elseif($file->fileId == "studentCard"){
            $field = "student_card_approved";
            $popupTitle = "Student card / Proof of student number";

            $doc_approved = $user->student_card_approved;

        }elseif($file->fileId == "bankStatement"){
            $field = "bank_approved";
            $popupTitle = "Bank statement";

            $doc_approved = $user->bank_approved;
            
        }elseif($file->fileId == "proofRegistration"){
            $field = "registration_approved";
            $popupTitle = "Proof of registration";

            $doc_approved = $user->registration_approved;

            //TODO: What is this fileId?

        }elseif($file->fileId == "proof_of_payment"){
            // $typeName = "Booking Fee Proof of Payment";
            $popupTitle = "Proof of payment";
            $doc_approved = 1;

        }elseif($file->fileId == "moveIn"){
            // $typeName = "Lease";
            // User::where('id', $userUid)->update(['overall_status' => 'Pending']);
            $popupTitle = "Lease";
            $doc_approved = 1;

        }elseif($file->fileId == "bursaryApproval"){
            $field = "bursary_approved";
            $popupTitle = "NSFAS / Bursary Approval";
            //TODO: Split this up based on student type

            $doc_approved = $user->bursary_approved;
        }

        

        $user->dataField = $field;

        return view('application.document.view_document', ['user' => $user, 'file' => $file, 'popupTitle' => $popupTitle, 'doc_approved' => $doc_approved]);
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

                if($fileType == "copyID"){
                    $typeName = "Copy of ID Document";
                    $field = "id_data_approved";
                }elseif($fileType == "studentCard"){
                    $typeName = "Copy of Student Card";
                    $field = "student_card_approved";
                }elseif($fileType == "bankStatement"){
                    $typeName = "Bank Statements";
                    $field = "bank_approved";
                }elseif($fileType == "proofRegistration"){
                    $typeName = "Proof of Registration";
                    $field = "registration_approved";
                }elseif($fileType == "proof_of_payment"){
                    $typeName = "Booking Fee Proof of Payment";
                    $field = "";
                }elseif($fileType == "moveIn"){
                    $typeName = "Lease";
                    User::where('id', $userUid)->update(['overall_status' => 'Pending']);
                    $field = "";
                }elseif($fileType == "bursaryApproval"){
                    $typeName = "NSFAS / Bursary Approval";
                    $field = "bursary_approved";
                }

                // if($typeName == "Lease"){
                //     $statusAction = "Uploaded";
                // }else{
                //     $statusAction = "Uploaded";
                // }
                

                if(!$hasFile){
                    UserFiles::create([
                        'fileId' => $fileType,
                        'userUid' => $userUid,
                        'fileName' => $fileNameToStore,
                        'fileUrl' => $fileUrl,
                        'status' => "Uploaded",
                        'uploaded_by' => Auth::user()->userFirstName,
                    ]);

                    $text = "Uploaded ".$typeName." for review";
                }else{
                    UserFiles::where('userUid', $userUid)->where('fileId', $fileType)->update([
                        'fileId' => $fileType,
                        'userUid' => $userUid,
                        'fileName' => $fileNameToStore,
                        'fileUrl' => $fileUrl,
                        'status' => "Uploaded",
                        'uploaded_by' => Auth::user()->userFirstName,
                    ]);
                    $text = "Re-uploaded ".$typeName." for review";

                    //Update robot status back to pending
                    if (!empty($field)) {
                        User::where('id', $userUid)->update([$field => '1']);
                    }
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

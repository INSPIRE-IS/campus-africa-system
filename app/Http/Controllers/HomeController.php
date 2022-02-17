<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserFiles;
use Auth;
use Mail;
use App\Models\ActionList;
use App\Models\MailerList;
use App\Models\Lookups;
use Illuminate\Support\Facades\Hash;
use App\Models\Booking;
use Illuminate\Support\Facades\DB;
use App\Models\Property;
use App\Models\Residence;
use App\Models\RoomTypes;
use App\Models\ImportNSFASApproval;
use App\Models\ImportNSFASStudentAuditLog;
use ZipStream\ZipStream;
use ZipStream\Option\Archive as ArchiveOptions;
use App\Models\Comments;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */

     public function index(){
        if (Auth::check()) {
            return redirect()->route('home');
        } else {
            return redirect()->route('login');
        }
     }
    public function dashboard()
    {

        if(Auth::user()->userRole == "0"){

            $allApprovedFiles = UserFiles::where("userUid", Auth::user()->id)->where('status', 'Approved')->get();
            $allPendingFiles = UserFiles::where("userUid", Auth::user()->id)->where('status', 'Uploaded')->get();
            $allRejectedFiles = UserFiles::where("userUid", Auth::user()->id)->where('status', 'Rejected')->get();

            $allFiles = UserFiles::where("userUid", Auth::user()->id)->get();
            $user = User::where('id', Auth::user()->id)->first();

            $uploaded = [];
            // $all = ['copyID', 'studentCard', 'bankStatement', 'proofRegistration', 'proof_of_payment', 'moveIn'];
            $all = ['copyID', 'studentCard', 'proofRegistration', 'proof_of_payment'];

            if($user->applicationStatus == "Approved Confirmed"){
                array_push($all, "moveIn");
            }
            if($user->studentType == "Cash / Direct"){
                array_push($all, "bankStatement");
            }
            if($user->studentType !== "Cash / Direct"){
                array_push($all, "bursaryApproval");
            }

            foreach($allFiles as $file){
                array_push($uploaded, $file->fileId);
            }

            $outstanding = array_diff($all, $uploaded);

            return view('user.dashboard', ["headerTitle" => "Your Dashboard", "approvedFiles" => $allApprovedFiles, "pendingFiles" => $allPendingFiles, "rejectedFiles" => $allRejectedFiles, "outstanding" => $outstanding]);
        }else{

            return view('admin.dashboard', ["headerTitle" => "Admin Dashboard", "title" => "Dashboard"]);
        }

    }

    public function room_booking(){

        $proof_of_payment = UserFiles::where('userUid', Auth::user()->id)->where('fileId', 'proof_of_payment')->first();
        $lease = UserFiles::where('userUid', Auth::user()->id)->where('fileId', 'moveIn')->first();

        return view('user.room_booking', ["headerTitle" => "Room Booking", "proof_of_payment" => $proof_of_payment, "lease" => $lease]);

    }

    public function lease(){

        return view('user.lease', ["headerTitle" => "Sign Lease"]);

    }

    public function move_in(){

        return view('user.move_in', ["headerTitle" => "Move in Resources"]);

    }

    protected function fileUpload(Request $request){
        $userUid = $request->userUid;
        $fileType = $request->fileType;

        if ($request->hasFile('file')) {
            
            if ($request->file('file')->isValid()) {

                $filenameWithExt  = $request->file('file')->getClientOriginalName();
                $fileName = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                $extension = $request->file('file')->extension();
                $fileNameToStore = $fileName. '_'. time().'.'.$extension;

                $request->file('file')->storeAs('public/files/'.$userUid.'/', $fileNameToStore);

                $fileUrl = 'files/'.$userUid.'/'. $fileNameToStore;

                $hasFile = UserFiles::where('userUid', $userUid)->where('fileId', $fileType)->first();

                if($fileType == "copyID"){
                    $typeName = "Copy of ID Document";
                }elseif($fileType == "studentCard"){
                    $typeName = "Copy of Student Card";
                }elseif($fileType == "bankStatement"){
                    $typeName = "Bank Statements";
                }elseif($fileType == "proofRegistration"){
                    $typeName = "Proof of Registration";
                }elseif($fileType == "proof_of_payment"){
                    $typeName = "Booking Fee Proof of Payment";
                }elseif($fileType == "moveIn"){
                    $typeName = "Lease";
                }

                if(!$hasFile){
                    UserFiles::create([
                        'fileId' => $fileType,
                        'userUid' => $userUid,
                        'fileName' => $fileNameToStore,
                        'fileUrl' => $fileUrl,
                        'status' => 'Uploaded',
                        'uploaded_by' => Auth::user()->userFirstName,
                    ]);

                    $text = "Uploaded ".$typeName." for review";
                }else{
                    UserFiles::where('userUid', $userUid)->where('fileId', $fileType)->update([
                        'fileId' => $fileType,
                        'userUid' => $userUid,
                        'fileName' => $fileNameToStore,
                        'fileUrl' => $fileUrl,
                        'status' => 'Uploaded',
                        'uploaded_by' => Auth::user()->userFirstName,
                    ]);
                    $text = "Re-uploaded ".$typeName." for review";
                }
    
                ActionList::create([
                    "userUid" => $userUid,
                    "action" => $text,
                    "actionUserUid" => Auth::user()->id,
                    "actionUserDisplayName" => Auth::user()->userFirstName,
                    "actionUserRole" => Auth::user()->userRole,
                ]);

                $uploaded = [];
                // $all = [0 => 'moveIn', 1 => 'proof_of_payment', 2 => 'copyID', 3 => 'studentCard', 4 => 'proofRegistration', 5 => 'bursaryApproval', 6 => "bankStatement" ];
                $user = User::where('id', $userUid)->first();
                $all = ['copyID', 'studentCard', 'proofRegistration', 'proof_of_payment'];

                if($user->applicationStatus == "Approved Confirmed"){
                    array_push($all, "moveIn");
                }
                if($user->studentType == "Cash / Direct"){
                    array_push($all, "bankStatement");
                }
                if($user->studentType !== "Cash / Direct"){
                    array_push($all, "bursaryApproval");
                }
                $data = [];

                $phase1 = "Empty";
                $phase2 = "Empty";

                $user = User::with('files')->where('id', $userUid)->first();

                foreach($user->files as $file){
                    array_push($uploaded, $file->fileId);
                    array_push($data, ["field" => $file->fileId, "status" => $file->status]);
                }

                $left = array_diff($all, $uploaded);

                foreach($left as $dataLeft){
                    if($user->studentType == "NSFAS" || $user->studentType == "Bursary"){
                        if($dataLeft == "bankStatement"){
                            array_push($data, ["field" => $dataLeft, "status" => "na"]);
                        }else{
                            array_push($data, ["field" => $dataLeft, "status" => ""]);
                        }
                    }else{
                        if($dataLeft == "bursaryApproval"){
                            array_push($data, ["field" => $dataLeft, "status" => "na"]);
                        }else{
                            array_push($data, ["field" => $dataLeft, "status" => ""]);
                        }
                    }
                }

                foreach($data as $item){
                    if($item['field'] == "proof_of_payment" || $item['field'] == "copyID"){
                        if($phase1 == "Empty" && $item['status'] == "Uploaded"){
                            $phase1 = "Action";
                        }else if($phase1 == "Empty" && $item['status'] == "Approved"){
                            $phase1 = "Approved";
                        }else if($phase1 == "Empty" && $item['status'] == "Rejected"){
                            $phase1 = "Rejected";
                        }

                        if($phase1 == "Action" && $item['status'] == "Uploaded"){
                            $phase1 = "Action";
                        }else if($phase1 == "Action" && $item['status'] == "Approved"){
                            $phase1 = "Action";
                        }else if($phase1 == "Action" && $item['status'] == "Rejected"){
                            $phase1 = "Action";
                        }

                        if($phase1 == "Approved" && $item['status'] == "Uploaded"){
                            $phase1 = "Action";
                        }else if($phase1 == "Approved" && $item['status'] == "Approved"){
                            $phase1 = "Approved";
                        }else if($phase1 == "Approved" && $item['status'] == "Rejected"){
                            $phase1 = "Rejected";
                        }

                        if($phase1 == "Rejected" && $item['status'] == "Uploaded"){
                            $phase1 = "Action";
                        }else if($phase1 == "Rejected" && $item['status'] == "Approved"){
                            $phase1 = "Rejected";
                        }else if($phase1 == "Rejected" && $item['status'] == "Rejected"){
                            $phase1 = "Rejected";
                        }

                    }

                    if($item['field'] == "proof_of_payment" || $item['field'] == "copyID"){

                        if($item['status'] == "Approved"){
                            array_push($phase1Arr, 2);
                        }
    
                        if($item['status'] == "Uploaded"){
                            array_push($phase1Arr, 1);
                        }
    
                        if($item['status'] == "Rejected" || $item['status'] == ""){
                            array_push($phase1Arr, 0);
                        }
    
                    }
                    

                    if($item['field'] == "studentCard" || $item['field'] == "proofRegistration" || $item['field'] == "bursaryApproval" || $item['field'] == "bankStatement"){
                        if($phase2 == "Empty" && $item['status'] == "Uploaded"){
                            $phase2 = "Action";
                        }else if($phase2 == "Empty" && $item['status'] == "Approved"){
                            $phase2 = "Approved";
                        }else if($phase2 == "Empty" && $item['status'] == "Rejected"){
                            $phase2 = "Rejected";
                        }

                        if($phase2 == "Action" && $item['status'] == "Uploaded"){
                            $phase2 = "Action";
                        }else if($phase2 == "Action" && $item['status'] == "Approved"){
                            $phase2 = "Action";
                        }else if($phase2 == "Action" && $item['status'] == "Rejected"){
                            $phase2 = "Action";
                        }

                        if($phase2 == "Approved" && $item['status'] == "Uploaded"){
                            $phase2 = "Action";
                        }else if($phase2 == "Approved" && $item['status'] == "Approved"){
                            $phase2 = "Approved";
                        }else if($phase2 == "Approved" && $item['status'] == "Rejected" || $phase2 == "Approved" && $item['status'] == ""){
                            $phase2 = "Rejected";
                        }

                        if($phase2 == "Rejected" && $item['status'] == "Uploaded"){
                            $phase2 = "Action";
                        }else if($phase2 == "Rejected" && $item['status'] == "Approved"){
                            $phase2 = "Rejected";
                        }else if($phase2 == "Rejected" && $item['status'] == "Rejected"){
                            $phase2 = "Rejected";
                        }

                    }

                }

                User::where('id', $userUid)->update(["bookingStatus" => $phase1, "applicationStatus" => $phase2]);

            }
        }


        return "done";
    }

    public function complete_reservation(Request $request){
        
        return view('user.complete_res', ["headerTitle" => "Congratulations!"]);

    }

    public function applications(Request $request){
        
        if(Auth::user()->userRole == "1" || Auth::user()->userRole == "2" || Auth::user()->userRole == "3" ) {
            return view('admin.applications', ["headerTitle" => "Applications", "title" => "Applications"]);
        }
    }

    public function application_profile(Request $request, $userId){

        if(Auth::user()->userRole == "1" || Auth::user()->userRole == "2" || Auth::user()->userRole == "3"){

            $user = User::where('id', $userId)->first();

            $name = $user['userFirstName']." ".$user['userLastname'];

            $user = User::with('files')->where('id', $userId)->first();
            $booking = Booking::where('userId', $user->id)->first();
            $userData = [];
            $uploaded = [];
            // $all = [0 => 'copyID', 1 => 'studentCard', 2 => 'bankStatement', 3 => 'proofRegistration', 4 => 'proof_of_payment', 5 => 'moveIn', 6 => "bursaryApproval" ];

            $all = ['copyID', 'studentCard', 'proofRegistration', 'proof_of_payment'];

            if($user->applicationStatus == "Approved Confirmed" && $booking->lease_from_date !== null && $booking->lease_from_time !== null && $booking->lease_to_date !== null && $booking->lease_to_time !== null){
                array_push($all, "moveIn");
            }
            if($user->studentType == "Cash / Direct"){
                array_push($all, "bankStatement");
            }
            if($user->studentType !== "Cash / Direct"){
                array_push($all, "bursaryApproval");
            }
        
            $data = [];

            foreach($user->files as $file){
                array_push($uploaded, $file->fileId);
                if($file->status == "Rejected"){
                    $file->status = "Rejected";
                }
                if($file->status == "Approved"){
                    $file->status = "XApproved";
                }
                if($file->status == "Uploaded"){
                    $file->status = "AUploaded";
                }
                array_push($data, ["field" => $file->fileId, "status" => $file->status, "url" => $file->fileUrl, "uploaded_by" => $file->uploaded_by, "created_at" => $file->created_at, "id" => $file->id]);
            }

            $left = array_diff($all, $uploaded);

            foreach($left as $dataLeft){
                array_push($data, ["field" => $dataLeft, "status" => "Rejected"]);
            }
            
            usort($data, function($a, $b) {
                return $a['status'] <=> $b['status'];
            });

            array_push($userData, ["files" => $data, "userInfo" => $user]);

            $actions = ActionList::with('users')->where('userUid', $userId)->orderBy('created_at', 'DESC')->get();

            // dd($actions[0]->users->userFirstName);

            $completed = "false";
            $completedDocs = [];

            foreach($data as $file){
                if($file['status'] == "XApproved"){
                    array_push($completedDocs, $file['status']);
                }
            }

            // dd($completedDocs);

            if(count($completedDocs) == 5){
                $completed = "true";
            }

            $lookup = Lookups::get();

            $residence = Residence::where('residence', $booking->property)->first();

            //Get booking Meta
            $bookingPropData = Property::where('id', $booking->property_data_id)->first();

            $files_to_download = 'false';
            $booking_paid = 'false';
            $booking_waived = 'false';

            foreach($user['files'] as $file){
                if($file->status == "XApproved" && $file->fileUrl !== null || $file->status == "Approved" && $file->fileUrl !== null){
                    $files_to_download = 'true';
                }

                if($file->fileId == "proof_of_payment"){
                    
                    if($file->fileUrl !== null && $file->fileUrl !== '' && $file->status !== "Rejected" && $file->status !== "Uploaded" && $file->status !== "AUploaded"){
                        $booking_paid = "true";
                        
                    }else{
                        if($file->status == "XApproved" || $file->status == "Approved"){

                            $actions = ActionList::where('userUid', $user->id)->orderBy('id', 'DESC')->get();

                            foreach($actions as $action){
                                if (str_contains($action->action, 'Waived')) {
                                    $booking_waived = 'true';
                                    break;
                                }

                                if(str_contains($action->action, 'R0')){
                                    $booking_waived = '0';
                                    break;
                                }
                            }

                        }
                    }
                }
            }

            $comments = Comments::with('users')->where('user_uid', $userId)->orderBy('created_at', 'desc')->get();


            return view('admin.app_profile', ["headerTitle" => $name, "appProfile" => "true", "user" => $userData, "actions" => $actions, "completed" => $completed, "lookups" => $lookup, "title" => "Student Profile", "booking" => $booking, "residence" => $residence, "bookingPropData" => $bookingPropData, "files_to_download" => $files_to_download, "booking_paid" => $booking_paid, 'booking_waived' => $booking_waived, 'comments' => $comments]);
        }
    }

    public function action_file(Request $request){

        if(Auth::user()->userRole == "1" || Auth::user()->userRole == "2" || Auth::user()->userRole == "3"){
            
            $fileId = $request->file;
            $action = $request->type;
            $reason = $request->reason;

            $userFile = UserFiles::where('id', $fileId)->update([
                'status' => $action,
                'rejectReason' => $reason,
                'approved_by' => $request->userName,
            ]);

            $userFile = UserFiles::where('id', $fileId)->first();

            if($userFile['fileId'] == "copyID"){
                $typeName = "Copy of ID Document";
                $dataField = "id_data_approved";
                $dataField2 = "";
            }elseif($userFile['fileId'] == "studentCard"){
                $typeName = "Copy of Student Card";
                $dataField = "student_card_approved";
                $dataField2 = "";
            }elseif($userFile['fileId'] == "bankStatement"){
                $typeName = "Bank Statements";
                $dataField = "bank_approved";
                $dataField2 = "parent_approved";
            }elseif($userFile['fileId'] == "proofRegistration"){
                $typeName = "Proof of Registration";
                $dataField = "registration_approved";
                $dataField2 = "";
            }elseif($userFile['fileId'] == "proof_of_payment"){
                $typeName = "Booking Fee Proof of Payment";
                $dataField = "";
            }elseif($userFile['fileId'] == "moveIn"){
                $typeName = "Lease";
                $dataField = "";
            }elseif($userFile['fileId'] == "bursaryApproval"){
                $typeName = "NSFAS Approval";
                $dataField = "bursary_approved";
                $dataField2 = "burser_approved";
            }

            if($action == "Rejected"){
                $text = "Rejected ".$typeName." with reason: ".$reason ;
            }else{
                $text = "Approved ".$typeName;

                if($dataField !== ""){
                    User::where('id', $userFile->userUid)->update([$dataField => "2"]);
                    
                    if($dataField2 !== ""){
                        User::where('id', $userFile->userUid)->update([$dataField2 => "2"]);
                    }
                }
            }

            if($request->userName !== "" && !empty($request->userName)){
                $name = $request->userName;
            }else{
                $name = Auth::user()->userFirstName;
            }

            ActionList::create([
                "userUid" => $userFile->userUid,
                "action" => $text,
                "actionUserUid" => Auth::user()->id,
                "actionUserDisplayName" => $name,
                "actionUserRole" => Auth::user()->userRole,
            ]);

            $uploaded = [];
            // $all = [0 => 'moveIn', 1 => 'proof_of_payment', 2 => 'copyID', 3 => 'studentCard', 4 => 'proofRegistration', 5 => 'bursaryApproval', 6 => "bankStatement" ];
            $user = User::where('id', $userFile->userUid)->first();
            $all = ['copyID', 'studentCard', 'proofRegistration', 'proof_of_payment'];

            if($user->applicationStatus == "Approved Confirmed"){
                array_push($all, "moveIn");
            }
            if($user->studentType == "Cash / Direct"){
                array_push($all, "bankStatement");
            }
            if($user->studentType !== "Cash / Direct"){
                array_push($all, "bursaryApproval");
            }
            $data = [];

            $phase1Arr = [];
            $phase2 = "Empty";
            $phase2Arr = [];

            $user = User::with('files')->where('id', $userFile->userUid)->first();

            foreach($user->files as $file){
                array_push($uploaded, $file->fileId);
                array_push($data, ["field" => $file->fileId, "status" => $file->status]);
            }

            $left = array_diff($all, $uploaded);

            foreach($left as $dataLeft){
                if($user->studentType == "NSFAS" || $user->studentType == "Bursary"){
                    if($dataLeft == "bankStatement"){
                        array_push($data, ["field" => $dataLeft, "status" => "na"]);
                    }else{
                        array_push($data, ["field" => $dataLeft, "status" => ""]);
                    }
                }else{
                    if($dataLeft == "bursaryApproval"){
                        array_push($data, ["field" => $dataLeft, "status" => "na"]);
                    }else{
                        array_push($data, ["field" => $dataLeft, "status" => ""]);
                    }
                }
            }

            foreach($data as $item){
                if($item['field'] == "proof_of_payment" || $item['field'] == "copyID"){

                    if($item['status'] == "Approved"){
                        array_push($phase1Arr, 2);
                    }

                    if($item['status'] == "Uploaded"){
                        array_push($phase1Arr, 1);
                    }

                    if($item['status'] == "Rejected" || $item['status'] == ""){
                        array_push($phase1Arr, 0);
                    }

                }

                

                if($item['field'] == "studentCard" || $item['field'] == "proofRegistration" || $item['field'] == "bursaryApproval" || $item['field'] == "bankStatement"){
                    
                    if($phase2 == "Empty" && $item['status'] == "Uploaded"){
                        $phase2 = "Action";
                    }else if($phase2 == "Empty" && $item['status'] == "Approved"){
                        $phase2 = "Approved";
                    }else if($phase2 == "Empty" && $item['status'] == "Rejected"){
                        $phase2 = "Rejected";
                    }

                    if($phase2 == "Action" && $item['status'] == "Uploaded"){
                        $phase2 = "Action";
                    }else if($phase2 == "Action" && $item['status'] == "Approved"){
                        $phase2 = "Action";
                    }else if($phase2 == "Action" && $item['status'] == "Rejected"){
                        $phase2 = "Action";
                    }

                    if($phase2 == "Approved" && $item['status'] == "Uploaded"){
                        $phase2 = "Action";
                    }else if($phase2 == "Approved" && $item['status'] == "Approved"){
                        $phase2 = "Approved";
                    }else if($phase2 == "Approved" && $item['status'] == "Rejected" || $phase2 == "Approved" && $item['status'] == ""){
                        $phase2 = "Rejected";
                    }

                    if($phase2 == "Rejected" && $item['status'] == "Uploaded"){
                        $phase2 = "Action";
                    }else if($phase2 == "Rejected" && $item['status'] == "Approved"){
                        $phase2 = "Rejected";
                    }else if($phase2 == "Rejected" && $item['status'] == "Rejected"){
                        $phase2 = "Rejected";
                    }

                }

                //For lease mark application as completed
                if ($item['field'] == "moveIn") {
                    $phase2 = "Approved Confirmed";
                }

            }

            $booking = "Rejected";

            if (!in_array(0, $phase1Arr)) {
                $booking =  "Approved";
            }

            if (in_array(1, $phase1Arr)) {
                $booking = "Action";
            }


            $overall = "Incomplete";

            if($booking == "Approved Confirmed" || $phase2 == "Approved Confirmed"){
                $overall = "Approved";
            }

            if($booking == "Rejected" || $booking == "Empty" || $phase2 == "Rejected" || $phase2 == "Empty"){
                $overall = "Incomplete";
            }

            if($booking == "Action" || $phase2 == "Action"|| $phase2 == "Approved"){
                $overall = "Pending";
            }

            if($userFile['fileId'] == "moveIn"){
                if($request->type == "Approved"){
                    $overall = "Approved";
                }else{
                    $overall = "Incomplete";
                }
            }

            User::where('id', $userFile->userUid)->update(["bookingStatus" => $booking, "applicationStatus" => $phase2, 'overall_status' => $overall]);

            return $userFile;


        }
    }

    public function action_list(Request $request, $userId, $filter){

        if($filter == "all"){
            $list = ActionList::with('users')->where('userUid', $userId)->orderBy('created_at', 'DESC')->get();
        }elseif($filter == "admin"){
            $list = ActionList::with('users')->where('userUid', $userId)->where('actionUserRole', '1')->orderBy('created_at', 'DESC')->get();
        }elseif($filter == "applicant"){
            $list = ActionList::with('users')->where('userUid', $userId)->where('actionUserRole', '0')->orderBy('created_at', 'DESC')->get();
        }

        return $list;
    }

    public function document_list(Request $request, $userId, $filter){

        $user = User::with('files')->where('id', $userId)->first();
        $userData = [];
        $uploaded = [];
        // $all = [0 => 'copyID', 1 => 'studentCard', 2 => 'bankStatement', 3 => 'proofRegistration', 4 => 'proof_of_payment', 5 => 'moveIn', 6 => "bursaryApproval" ];

        $all = ['copyID', 'studentCard', 'proofRegistration', 'proof_of_payment'];

        if($user->applicationStatus == "Approved Confirmed"){
            array_push($all, "moveIn");
        }
        if($user->studentType == "Cash / Direct"){
            array_push($all, "bankStatement");
        }
        if($user->studentType !== "Cash / Direct"){
            array_push($all, "bursaryApproval");
        }
        $data = [];
        
        if($filter == "all"){
            foreach($user->files as $file){
                array_push($uploaded, $file->fileId);
                array_push($data, ["field" => $file->fileId, "status" => $file->status, "url" => $file->fileUrl, "uploaded_by" => $file->uploaded_by, "created_at" => $file->created_at, "id" => $file->id]);
            }
    
            $left = array_diff($all, $uploaded);
            foreach($left as $dataLeft){
                array_push($data, ["field" => $dataLeft, "status" => ""]);
            }
        }elseif($filter == "review"){
            foreach($user->files as $file){
                if($file->status == "Uploaded"){
                    array_push($uploaded, $file->fileId);
                    array_push($data, ["field" => $file->fileId, "status" => $file->status, "url" => $file->fileUrl, "uploaded_by" => $file->uploaded_by, "created_at" => $file->created_at, "id" => $file->id]);
                }
            }
        }elseif($filter == "approved"){
            foreach($user->files as $file){
                if($file->status == "Approved"){
                    array_push($uploaded, $file->fileId);
                    array_push($data, ["field" => $file->fileId, "status" => $file->status, "url" => $file->fileUrl, "uploaded_by" => $file->uploaded_by, "created_at" => $file->created_at, "id" => $file->id]);
                }
            }
        }elseif($filter == "noDoc"){
            foreach($user->files as $file){
                array_push($uploaded, $file->fileId);                
            }

            $left = array_diff($all, $uploaded);
            foreach($left as $dataLeft){
                array_push($data, ["field" => $dataLeft, "status" => ""]);
            }
        }

        return $data;
    }

    protected function approve_application(Request $request){
        $userId = $request->userId;

        $user = User::where('id', $userId)->first();

        $data['name'] = $user->userFirstName;
        $data['email'] = $user->userEmail;
        $data['roomNo'] = $user->roomNo;
        $data['bookingRef'] = $user->bookingRef;

        // Mail::send('email.application_approved', $data, function($message) use ($data) {
        //     $message->to($data['email'], $data['name'])->subject
        //         ('Thank you Application');
        //     $message->cc('test@whitehart.co.za','Name');
        //     $message->from('test@test.com','test@test.com');
        // });

        User::where('id', $userId)->update(['applicationStatus' => "Approved Confirmed", 'overall_status' => "Incomplete"]);

        ActionList::create([
            "userUid" => $userId,
            "action" => "Approved application and sent confirmation email",
            "actionUserUid" => Auth::user()->id,
            "actionUserDisplayName" => Auth::user()->userFirstName,
            "actionUserRole" => Auth::user()->userRole,
        ]);

    }

    protected function reject_application(Request $request){
        $userId = $request->userId;

        $user = User::where('id', $userId)->first();

        $data['name'] = $user->userFirstName;
        $data['email'] = $user->userEmail;
        $data['roomNo'] = $user->roomNo;
        $data['bookingRef'] = $user->bookingRef;

        // Mail::send('email.application_approved', $data, function($message) use ($data) {
        //     $message->to($data['email'], $data['name'])->subject
        //         ('Thank you Application');
        //     $message->cc('test@whitehart.co.za','Name');
        //     $message->from('test@test.com','test@test.com');
        // });

        User::where('id', $userId)->update(['bookingStatus' => "Rejected Confirmed",'applicationStatus' => 'rejected', 'overall_status' => 'Rejected']);
        Booking::where('userId', $userId)->update(['property_data_id' => null, 'floor' => null, 'unit' => null, 'room' => null, 'bed' => null, 'bed_code' => null, 'room_rate' => null]);

        ActionList::create([
            "userUid" => $userId,
            "action" => "Rejected application and sent rejectment email",
            "actionUserUid" => Auth::user()->id,
            "actionUserDisplayName" => Auth::user()->userFirstName,
            "actionUserRole" => Auth::user()->userRole,
        ]);
    }

    public function documents(Request $request){
            $allApprovedFiles = UserFiles::where("userUid", Auth::user()->id)->where('status', 'Approved')->get();
            $allPendingFiles = UserFiles::where("userUid", Auth::user()->id)->where('status', 'Uploaded')->get();
            $allRejectedFiles = UserFiles::where("userUid", Auth::user()->id)->where('status', 'Rejected')->get();

            $allFiles = UserFiles::where("userUid", Auth::user()->id)->get();

            $uploaded = [];
            // $all = ['copyID', 'studentCard', 'bankStatement', 'proofRegistration', 'proof_of_payment', 'moveIn'];
            $user = User::where('id', Auth::user()->id)->first();

            $all = ['copyID', 'studentCard', 'proofRegistration', 'proof_of_payment'];

            if($user->applicationStatus == "Approved Confirmed"){
                array_push($all, "moveIn");
            }
            if($user->studentType == "Cash / Direct"){
                array_push($all, "bankStatement");
            }
            if($user->studentType !== "Cash / Direct"){
                array_push($all, "bursaryApproval");
            }

            foreach($allFiles as $file){
                array_push($uploaded, $file->fileId);
            }

            $outstanding = array_diff($all, $uploaded);

            return view('user.documents', ["headerTitle" => "My Documents", "approvedFiles" => $allApprovedFiles, "pendingFiles" => $allPendingFiles, "rejectedFiles" => $allRejectedFiles, "outstanding" => $outstanding]);
    }

    public function leases(Request $request){
        
        $leases = UserFiles::with('users')->where('fileId', 'moveIn')->get();

        return view('admin.leases', ["headerTitle" => "Leases", "leases" => $leases, "title" => "Leases"]);
    }

    public function mailing(Request $request){

        $thankYou = view('email.application_thank_you');

        $MailerList = MailerList::all();

        return view('admin.mailing', ["headerTitle" => "Mailers", "bookingRef" => "00000", "name" => "Johan", "roomNo" => "000", "MailerList" => $MailerList, "title" => "Mailers"]);
    }

    public function invoices(Request $request){
        return view('admin.invoices', ["headerTitle" => "Invoices", "title" => "Invoices"]);
    }

    public function thanks(Request $request){

        // $data['test'] = "";

        // Mail::send('email.application_thank_you', $data, function($message) use ($data) {
        //     $message->to("mirichea@whitehart.co.za", "Mirichea")->subject
        //         ('Thank you Application');
        //     $message->cc('test@whitehart.co.za','Name');
        //     $message->from('test@test.com','test@test.com');
        // });

        return view('email.application_rejected');
    }

    public function activate_mailer(Request $request){
        $mailerId = $request->mailerId;

        MailerList::where('id', $mailerId)->update(['status' => '1']);

        return $mailerId;
    }

    public function inactivate_mailer(Request $request){
        $mailerId = $request->mailerId;

        MailerList::where('id', $mailerId)->update(['status' => '0']);

        return $mailerId;
    }

    public function saveMailTy(Request $request){
        
        MailerList::where('id', '6')->update(['message' => $request->text]);

        return 'true';
        
    }

    public function saveMailAa(Request $request){
        
        MailerList::where('id', '1')->update(['message' => $request->text]);

        return 'true';
        
    }

    public function saveMailAav(Request $request){
        
        MailerList::where('id', '2')->update(['message' => $request->text]);

        return 'true';
        
    }
    public function saveMailAD(Request $request){
        
        MailerList::where('id', '5')->update(['message' => $request->text]);

        return 'true';
        
    }
    public function saveMailADd(Request $request){
        
        MailerList::where('id', '3')->update(['message' => $request->text]);

        return 'true';
        
    }
    public function saveMailAR(Request $request){
        
        MailerList::where('id', '4')->update(['message' => $request->text]);

        return 'true';
        
    }

    public function users(Request $request){

        $systemUsers = User::where("userRole", "!=", "0")->get();
        $students = User::where("userRole", "0")->get();

        if(Auth::user()->role_manage_users == "1") {
            return view('admin.user_management', ["headerTitle" => "User management", "systemUsers" => $systemUsers, "students" => $students, "title" => "User Management"]);
        } else {
            return "404 Error";
        }

    }

    public function user_edit(Request $request, $userId){
        
        $user = User::where('id', $userId)->first();
        
        if(Auth::user()->role_manage_users == "1") {
            return view('admin.userEdit', ["headerTitle" => "User Edit", "user" => $user, "title" => "Edit User"]);
        } else {
            return "404 Error";
        }
    }

    public function edit_admin(Request $request){
        

        User::where('id', $request->userUid)->update([
            "userFirstName" => $request->firstName,
            "userLastname" => $request->lastName,
            // "userTitle" => $request->title,
            // "userDob" => $request->dob,
            // "userCell" => $request->cell_no,
            "userEmail" => $request->email,
            "userRole" => "1",
            "role_create_app" => $request->create_app,
            "role_edit_app" => $request->edit_app,
            "role_upload_docs" => $request->upload_docs,
            "role_approve_docs" => $request->approve_docs,
            "role_delete_booking" => $request->delete_booking,
            "role_delete_uploaded" => $request->delete_uploaded,
            "role_braamlofts" => $request->role_braamlofts,
            "role_80_jorissen" => $request->role_80_jorissen,
            "role_49_jorissen" => $request->role_49_jorissen,
            "role_dunvista_mansions" => $request->role_dunvista_mansions,
            "role_wynton_joy" => $request->role_wynton_joy,
            "role_126_siemert" => $request->role_126_siemert,
            "role_ymca" => $request->role_ymca,
            "role_change_date" => $request->role_change_date,
            "role_change_bed" => $request->role_change_bed,
            "role_manage_users" => $request->role_manage_users,
            "role_nsfas_approval_upload" => $request->role_nsfas_approval_upload,
            "role_cancel_booking" => $request->role_cancel_booking,
            "role_update_booking_fee" => $request->role_update_booking_fee,
            "role_lookup" => $request->role_lookup,
            "role_comment" => $request->role_comment,
            "user_role_name" => $request->user_role_name,
        ]);

        return redirect()->back();
    }

    public function delete_admin(Request $request){
        

        User::where('id', $request->userUid)->delete();

        return redirect('/users');
    }

    public function student_edit(Request $request, $studentId){
        $user = User::where('id', $studentId)->first();
        $booking = Booking::where('userId', $studentId)->first();
        $lookup = Lookups::get();

        return view('admin.studentEdit', ["headerTitle" => "Student Edit", "user" => $user, "lookups" => $lookup, "booking" => $booking, "title" => "Edit Student"]);
    }

    public function edit_student(Request $request){

        $oldData = User::where("id", $request->userUid)->first();
        $newData = ['userFirstName|First Name' => $request->firstName,'userLastname|Last Name' => $request->lastName,'userTitle|Title' => $request->title,'userDob|Date of Birth' => $request->dob,'userCell|Cellphone Number' => $request->cell_no,'userEmail|Email' => $request->email,'userId|I.D Number' => $request->idNumber,'userNationality|Nationality' => $request->nationality,'userPassport|Passport Number' => $request->passportNo,'institutionStudy|Institution of Study' => $request->institStudy,'studentNo|Student Number' => $request->studNumber,'studentType|Student Type' => $request->studentType,'faculty|Faculty' => $request->faculty,'year|Year' => "2022",'sponFirstName|Parent / Sponser First Name' => $request->pFirstName,'sponLastName| Parent / Sponser Last Name' => $request->pLastName,'sponCell|Parent / Sponser Cellphone Number' => $request->pCell_no,'sponEmail|Parent / Sponser Email' => $request->pEmail,'sponId|Parent / Sponser I.D Number' => $request->pIdNumber,'burCompanyName|Burser Company Name' => $request->bCompanyName,'burRegNo|Burser Registration Number' => $request->bRegistrationNo,'burContactPerson|Burser Contact Person' => $request->bContactPerson,'burContactNo|Burser Contact Number' => $request->bContactNumber,'burEmail|Burser Email' => $request->bEmail,'kinFirstName|Next of Kin First Name' => $request->kFirstName,'kinLastName|Next of Kin Last Name' => $request->kLastName,'kinTitle|Next of Kin Title' => $request->kTitle,'kinId|Next of Kin I.D Number' => $request->kIdNumber,'kinAddressStr|Next of Kin Street Number' => $request->kAddress1,'kinAddressSuburb|Next of Kin Suburb' => $request->kAddress2,'kinAddressCity|Next of Kin City' => $request->kAddressCity,'kinAddressPost|Next of Kin Postal Code' => $request->kAddressPostal,'kinRelationship|Next of Kin Relationship' => $request->kRelationship,'kinTelNo|Next of Kin Telephone Number' => $request->kTeleNo,'kinCellNo|Next of Kin Cellphone Number' => $request->kCellNo,'kinWorkNo|Next of Kin Work Number' => $request->kWorkNo,'kinEmail|Next of Kin Email' => $request->kEmail,'kinNationality|Next of Kin Nationality' => $request->kinNationality,'kinPassportNo|Next of Kin Passport Number' => $request->kinPassportNo,'pNationality|Parent / Sponser Nationality' => $request->pNationality,'pPassportNo|Parent / Sponser Passport Number' => $request->pPassportNo,'pAddress1|Parent / Sponser Street Number' => $request->pAddress1,'pAddress2|Parent / Sponser Suburb' => $request->pAddress2,'pAddressCity|Parent / Sponser City' => $request->pAddressCity,'pAddressPostal|Parent / Sponser Postal Code' => $request->pAddressPostal,'userAddress1|Street Number' => $request->userAddress1,'userAddress2|Suburb' => $request->userAddress2,'userAddressCity|City' => $request->userAddressCity,'userAddressPostal|PostalCode' => $request->userAddressPostal, 'bAddress1|Burser Street Number' => $request->bAddress1,'bAddress2|Burser Suburb' => $request->bAddress2,'bAddressCity|Burser City' => $request->bAddressCity,'bAddressPostal|Burser Postal Code' => $request->bAddressPostal,'bPayment|Burser Payment' => $request->bPayment,'bint|Burser Intermediary' => $request->bint,'bIntName|Burser Intermediary Name' => $request->bIntName, 'nsfas_bursar|NSFAS Bursar' => $request->nsfas_bursar,];

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

        User::where('id', $request->userUid)->update([
            'userFirstName' => $request->firstName,
            'userLastname' => $request->lastName,
            'userTitle' => $request->title,
            'userDob' => $request->dob,
            'userCell' => $request->cell_no,
            'userEmail' => $request->email,
            'userId' => $request->idNumber,
            'userNationality' => $request->nationality,
            'userPassport' => $request->passportNo,
            'institutionStudy' => $request->institStudy,
            'studentNo' => $request->studNumber,
            'studentType' => $request->studentType,
            // 'institutionCampus' => $request->institCampus,
            'faculty' => $request->faculty,
            'year' => "2022",
            'sponFirstName' => $request->pFirstName,
            'sponLastName' => $request->pLastName,
            'sponCell' => $request->pCell_no,
            'sponEmail' => $request->pEmail,
            'sponId' => $request->pIdNumber,
            'burCompanyName' => $request->bCompanyName,
            'burRegNo' => $request->bRegistrationNo,
            'burContactPerson' => $request->bContactPerson,
            'burContactNo' => $request->bContactNumber,
            'burEmail' => $request->bEmail,
            'kinFirstName' => $request->kFirstName,
            'kinLastName' => $request->kLastName,
            'kinTitle' => $request->kTitle,
            'kinId' => $request->kIdNumber,
            'kinAddressStr' => $request->kAddress1,
            'kinAddressSuburb' => $request->kAddress2,
            'kinAddressCity' => $request->kAddressCity,
            'kinAddressPost' => $request->kAddressPostal,
            'kinRelationship' => $request->kRelationship,
            'kinTelNo' => $request->kTeleNo,
            'kinCellNo' => $request->kCellNo,
            'kinWorkNo' => $request->kWorkNo,
            'kinEmail' => $request->kEmail,
            'kinNationality' => $request->kinNationality,
            'kinPassportNo' => $request->kinPassportNo,
            'pNationality' => $request->pNationality,
            'pPassportNo' => $request->pPassportNo,
            'pAddress1' => $request->pAddress1,
            'pAddress2' => $request->pAddress2,
            'pAddressCity' => $request->pAddressCity,
            'pAddressPostal' => $request->pAddressPostal,
            'userAddress1' => $request->userAddress1,
            'userAddress2' => $request->userAddress2,
            'userAddressCity' => $request->userAddressCity,
            'userAddressPostal' => $request->userAddressPostal,
            'app_address' => $request->app_address,
            'app_address_p' => $request->app_address_p,
            'app_parent_kin' => $request->app_parent_kin,
            'bAddress1' => $request->bAddress1,
            'bAddress2' => $request->bAddress2,
            'bAddressCity' => $request->bAddressCity,
            'bAddressPostal' => $request->bAddressPostal,
            'bPayment' => $request->bPayment,
            'bint' => $request->bint,
            'bIntName' => $request->bIntName,
            'nsfas_bursar' => $request->nsfas_bursar,
        ]);

        // ActionList::create([
        //     "userUid" => $request->userUid,
        //     "action" => "Updated Profile",
        //     "actionUserUid" => Auth::user()->id,
        //     "actionUserDisplayName" => Auth::user()->userFirstName,
        //     "actionUserRole" => Auth::user()->userRole,
        // ]);

        // return redirect()->back();
        return redirect("/application-profile/".$request->userUid);
    }

    public function student_upload(Request $request, $studentId){
        $user = User::where('id', $studentId)->first();

        $allApprovedFiles = UserFiles::where("userUid", $studentId)->where('status', 'Approved')->get();
        $allPendingFiles = UserFiles::where("userUid", $studentId)->where('status', 'Uploaded')->get();
        $allRejectedFiles = UserFiles::where("userUid", $studentId)->where('status', 'Rejected')->get();

        $allFiles = UserFiles::where("userUid", $studentId)->get();

        $uploaded = [];
        $all = ['copyID', 'studentCard', 'proofRegistration', 'proof_of_payment'];

        if($user->applicationStatus == "Approved Confirmed"){
            array_push($all, "moveIn");
        }
        if($user->studentType == "Cash / Direct"){
            array_push($all, "bankStatement");
        }
        if($user->studentType !== "Cash / Direct"){
            array_push($all, "bursaryApproval");
        }

        foreach($allFiles as $file){
            array_push($uploaded, $file->fileId);
        }

        $outstanding = array_diff($all, $uploaded);

        return view('admin.studentUpload', ["headerTitle" => "Student File Upload", "user" => $user, "approvedFiles" => $allApprovedFiles, "pendingFiles" => $allPendingFiles, "rejectedFiles" => $allRejectedFiles, "outstanding" => $outstanding, "title" => "Student Upload"]);
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
                }elseif($fileType == "studentCard"){
                    $typeName = "Copy of Student Card";
                }elseif($fileType == "bankStatement"){
                    $typeName = "Bank Statements";
                }elseif($fileType == "proofRegistration"){
                    $typeName = "Proof of Registration";
                }elseif($fileType == "proof_of_payment"){
                    $typeName = "Booking Fee Proof of Payment";
                }elseif($fileType == "moveIn"){
                    $typeName = "Lease";
                    User::where('id', $userUid)->update(['overall_status' => 'Pending']);
                }elseif($fileType == "bursaryApproval"){
                    $typeName = "NSFAS / Bursary Approval";
                }

                // if($typeName == "Lease"){
                //     $statusAction = "Uploaded";
                // }else{
                //     $statusAction = "Uploaded";
                // }
                

                if(!$hasFile){
                    $file = UserFiles::create([
                        'fileId' => $fileType,
                        'userUid' => $userUid,
                        'fileName' => $fileNameToStore,
                        'fileUrl' => $fileUrl,
                        'status' => "Uploaded",
                        'uploaded_by' => Auth::user()->userFirstName,
                    ]);

                    $text = "Uploaded ".$typeName." for review";
                }else{
                    $file = UserFiles::where('userUid', $userUid)->where('fileId', $fileType)->update([
                        'fileId' => $fileType,
                        'userUid' => $userUid,
                        'fileName' => $fileNameToStore,
                        'fileUrl' => $fileUrl,
                        'status' => "Uploaded",
                        'uploaded_by' => Auth::user()->userFirstName,
                    ]);
                    $text = "Re-uploaded ".$typeName." for review";
                }

                
    
                ActionList::create([
                    "userUid" => $userUid,
                    "action" => $text,
                    "actionUserUid" => Auth::user()->id,
                    "actionUserDisplayName" => Auth::user()->userFirstName,
                    "actionUserRole" => Auth::user()->userRole,
                ]);

                $uploaded = [];
                // $all = [0 => 'moveIn', 1 => 'proof_of_payment', 2 => 'copyID', 3 => 'studentCard', 4 => 'proofRegistration', 5 => 'bursaryApproval', 6 => "bankStatement" ];

                $user = User::where('id', $userUid)->first();
                $all = ['copyID', 'studentCard', 'proofRegistration', 'proof_of_payment'];

                // if($user->applicationStatus == "Approved Confirmed"){
                //     array_push($all, "moveIn");
                // }
                if($user->studentType == "Cash / Direct"){
                    array_push($all, "bankStatement");
                }
                if($user->studentType !== "Cash / Direct"){
                    array_push($all, "bursaryApproval");
                }
                $data = [];

                $phase1 = "Empty";
                $phase2 = "Empty";

                $user = User::with('files')->where('id', $userUid)->first();

                foreach($user->files as $file){
                    array_push($uploaded, $file->fileId);
                    array_push($data, ["field" => $file->fileId, "status" => $file->status]);
                }

                $left = array_diff($all, $uploaded);

                foreach($left as $dataLeft){
                    if($user->studentType == "NSFAS" || $user->studentType == "Bursary"){
                        if($dataLeft == "bankStatement"){
                            array_push($data, ["field" => $dataLeft, "status" => "na"]);
                        }else{
                            array_push($data, ["field" => $dataLeft, "status" => ""]);
                        }
                    }else{
                        if($dataLeft == "bursaryApproval"){
                            array_push($data, ["field" => $dataLeft, "status" => "na"]);
                        }else{
                            array_push($data, ["field" => $dataLeft, "status" => ""]);
                        }
                    }
                }

                foreach($data as $item){
                    if($item['field'] == "proof_of_payment" || $item['field'] == "copyID"){
                        // array_push($phase1, ["field" => $item->field, "status" => $itme->status]);
                        if($phase1 == "Empty" && $item['status'] == "Uploaded"){
                            $phase1 = "Action";
                        }else if($phase1 == "Empty" && $item['status'] == "Approved"){
                            $phase1 = "Approved";
                        }else if($phase1 == "Empty" && $item['status'] == "Rejected"){
                            $phase1 = "Rejected";
                        }

                        if($phase1 == "Action" && $item['status'] == "Uploaded"){
                            $phase1 = "Action";
                        }else if($phase1 == "Action" && $item['status'] == "Approved"){
                            $phase1 = "Action";
                        }else if($phase1 == "Action" && $item['status'] == "Rejected"){
                            $phase1 = "Action";
                        }

                        if($phase1 == "Approved" && $item['status'] == "Uploaded"){
                            $phase1 = "Action";
                        }else if($phase1 == "Approved" && $item['status'] == "Approved"){
                            $phase1 = "Approved";
                        }else if($phase1 == "Action" && $item['status'] == "Rejected"){
                            $phase1 = "Rejected";
                        }

                        if($phase1 == "Rejected" && $item['status'] == "Uploaded"){
                            $phase1 = "Action";
                        }else if($phase1 == "Rejected" && $item['status'] == "Approved"){
                            $phase1 = "Rejected";
                        }else if($phase1 == "Rejected" && $item['status'] == "Rejected"){
                            $phase1 = "Rejected";
                        }

                    }
                    

                    if($item['field'] == "studentCard" || $item['field'] == "proofRegistration" || $item['field'] == "bursaryApproval" || $item['field'] == "bankStatement"){
                        if($phase2 == "Empty" && $item['status'] == "Uploaded"){
                            $phase2 = "Action";
                        }else if($phase2 == "Empty" && $item['status'] == "Approved"){
                            $phase2 = "Approved";
                        }else if($phase2 == "Empty" && $item['status'] == "Rejected"){
                            $phase2 = "Rejected";
                        }

                        if($phase2 == "Action" && $item['status'] == "Uploaded"){
                            $phase2 = "Action";
                        }else if($phase2 == "Action" && $item['status'] == "Approved"){
                            $phase2 = "Action";
                        }else if($phase2 == "Action" && $item['status'] == "Rejected"){
                            $phase2 = "Action";
                        }

                        if($phase2 == "Approved" && $item['status'] == "Uploaded"){
                            $phase2 = "Action";
                        }else if($phase2 == "Approved" && $item['status'] == "Approved"){
                            $phase2 = "Approved";
                        }else if($phase2 == "Approved" && $item['status'] == "Rejected" || $phase2 == "Approved" && $item['status'] == ""){
                            $phase2 = "Rejected";
                        }

                        if($phase2 == "Rejected" && $item['status'] == "Uploaded"){
                            $phase2 = "Action";
                        }else if($phase2 == "Rejected" && $item['status'] == "Approved"){
                            $phase2 = "Rejected";
                        }else if($phase2 == "Rejected" && $item['status'] == "Rejected"){
                            $phase2 = "Rejected";
                        }

                    }

                    //For lease mark application as completed
                    if ($item['field'] == "moveIn") {
                        $phase2 = "Approved Confirmed";
                    }

                }

                $overall = "Incomplete";

                if($phase1 == "Action" || $phase2 == "Action"){
                    $overall = "Pending";
                }

                if($fileType == "moveIn"){
                    $overall = "Pending";
                }

                User::where('id', $userUid)->update(["bookingStatus" => $phase1, "applicationStatus" => $phase2, "overall_status" => $overall]);

            }
        }


        return ["done", $fileUrlBack, $file];
    }

    public function lookups(Request $request){

        $lookups = Lookups::with('users')->get();

        return view('admin.lookups', ["headerTitle" => "Lookups", "lookups" => $lookups, "title" => "Lookups"]);
    }

    public function add_lookups(Request $request){

        return view('admin.lookups_new', ["headerTitle" => "Add Lookup", "title" => "Add Lookup"]);
    }

    public function new_lookups(Request $request){

        if($request->group_id == "7"){
            $data['group_id'] = $request->group_id;
            $data['lookup_name'] = $request->booking_waiver;
            $data['waiver_date_from'] = $request->date_from;
            $data['waiver_date_to'] = $request->date_to;
            $data['actionUserUid'] = Auth::user()->id;
        }else{
            $data['group_id'] = $request->group_id;
            $data['lookup_name'] = $request->value;
            $data['actionUserUid'] = Auth::user()->id;
        }

        Lookups::create($data);

        return redirect()->route('lookups');
    }

    public function edit_lookups(Request $request, $lookupId){

        $lookup = Lookups::where('id', $lookupId)->first();

        return view('admin.lookups_edit', ["headerTitle" => "Edit Lookup", "lookup" => $lookup, "title" => "Edit Lookup"]);
    }

    public function save_lookups(Request $request){

        if($request->group_id == "4"){
            $data = [
                "group_id" => $request->group_id,
                "lookup_name" => $request->value,
                "lease_from_date" => $request->lease_from_date,
                "lease_from_time" => $request->lease_from_time,
                "lease_to_date" => $request->lease_to_date,
                "lease_to_time" => $request->lease_to_time,
                "actionUserUid" => Auth::user()->id,
            ];
        }else if($request->group_id == "7"){
            $data = [
                "group_id" => $request->group_id,
                "lookup_name" => $request->booking_waiver,
                "waiver_date_from" => $request->date_from,
                "waiver_date_to" => $request->date_to,
                "actionUserUid" => Auth::user()->id,
            ];
        }else{
            $data = [
                "group_id" => $request->group_id,
                "lookup_name" => $request->value,
                "actionUserUid" => Auth::user()->id,
            ];
        }

        Lookups::where('id', $request->lookupId)->update($data);

        return redirect()->route('lookups');
    }

    public function new_student(Request $request){

        $lookup = Lookups::get();
        
        return view('admin.new_student', ["headerTitle" => "Add New Student", "lookups" => $lookup, "title" => "Add Student"]);
    }

    public function add_student(Request $request){
        $user = User::create([
            'name' => $request->firstName,
            'userFirstName' => $request->firstName,
            'userLastname' => $request->lastName,
            'userTitle' => $request->title,
            'userDob' => $request->dob,
            'userCell' => $request->cell_no,
            'userEmail' => $request->email,
            'email' => $request->email,
            'password' => Hash::make("!Placeholder#"),
            'userId' => $request->idNumber,
            'userNationality' => $request->nationality,
            'userPassport' => $request->passportNo,
            'institutionStudy' => $request->institStudy,
            'studentNo' => $request->studNumber,
            // 'institutionCampus' => $request->institCampus,
            'faculty' => $request->faculty,
            'year' => $request->year,
            'sponFirstName' => $request->pFirstName,
            'sponLastName' => $request->pLastName,
            'sponCell' => $request->pCell_no,
            'sponEmail' => $request->pEmail,
            'sponId' => $request->pIdNumber,
            'burCompanyName' => $request->bCompanyName,
            'burRegNo' => $request->bRegistrationNo,
            'burContactPerson' => $request->bContactPerson,
            'burContactNo' => $request->bContactNumber,
            'burEmail' => $request->bEmail,
            'kinFirstName' => $request->kFirstName,
            'kinLastName' => $request->kLastName,
            'kinTitle' => $request->kTitle,
            //'kinId' => $request->kIdNumber,
            'kinAddressStr' => $request->kAddress1,
            'kinAddressSuburb' => $request->kAddress2,
            'kinAddressCity' => $request->kAddressCity,
            'kinAddressPost' => $request->kAddressPostal,
            'kinRelationship' => $request->kRelationship,
            'kinTelNo' => $request->kTeleNo,
            'kinCellNo' => $request->kCellNo,
            'kinWorkNo' => $request->kWorkNo,
            'kinEmail' => $request->kEmail,
            'studentType' => $request->studentType,
            'bookingStatus' => "Empty",
            'applicationStatus' => "Empty",
            'yearOfStudy' => $request->yearOfStudy,
            //'kinNationality' => $request->kinNationality,
            //'kinPassportNo' => $request->kinPassportNo,
            'pNationality' => $request->pNationality,
            'pPassportNo' => $request->pPassportNo,
            'pAddress1' => $request->pAddress1,
            'pAddress2' => $request->pAddress2,
            'pAddressCity' => $request->pAddressCity,
            'pAddressPostal' => $request->pAddressPostal,
            'userAddress1' => $request->userAddress1,
            'userAddress2' => $request->userAddress2,
            'userAddressCity' => $request->userAddressCity,
            'userAddressPostal' => $request->userAddressPostal,
            'app_address' => $request->app_address,
            'app_address_p' => $request->app_address_p,
            'app_parent_kin' => $request->app_parent_kin,
            'bAddress1' => $request->bAddress1,
            'bAddress2' => $request->bAddress2,
            'bAddressCity' => $request->bAddressCity,
            'bAddressPostal' => $request->bAddressPostal,
            'bPayment' => $request->bPayment,
            'bint' => $request->bint,
            'bIntName' => $request->bIntName,
            'nsfas_bursar' => $request->nsfas_bursar,
        ]);

        

        //Booking data        
        $booking_data = array(
            "userId" => $user->id, 
            "property" => $request->property, 
            "roomType" => $request->roomType
        );

        $lookup = Lookups::where('group_id', '7')->first();


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
            // dd($jsonDataRooms);

            $roomType = "";
            $roomName = "";

            foreach($jsonDataRooms as $room){
                if($residenceId == $room->acf->residence){

                    if($request->property == "YMCA"){
                        if($room->slug == "ymca-3-sharing"){
                            $roomType = "3 Sharing";
                            $roomName = "ymca-3-sharing";
                        }
                        if($room->slug == "ymca-2-sharing"){
                            $roomType = "2 Sharing";
                            $roomName = "ymca-2-sharing";
                        }
                        if($room->slug == "ymca-single-room-large"){
                            $roomType = "Single Large";
                            $roomName = "ymca-single-room-large";
                        }
                        if($room->slug == "ymca-single-room-medium"){
                            $roomType = "Single Medium";
                            $roomName = "ymca-single-room-medium";
                        }
                        if($room->slug == "ymca-single-room-small"){
                            $roomType = "Single Small";
                            $roomName = "ymca-single-room-small";
                        }
                    }

                    if($request->property == "126 Siemert"){
                        if($room->slug == "126-siemert-single-room"){
                            $roomType = "Single";
                            $roomName = "126-siemert-single-room";
                        }
                        if($room->slug == "126-siemert-2-sharing"){
                            $roomType = "2 Sharing";
                            $roomName = "126-siemert-2-sharing";
                        }
                    }

                    if($request->property == "Wynton Joy"){
                        if($room->slug == "wynton-joy-single-room"){
                            $roomType = "Single";
                            $roomName = "wynton-joy-single-room";
                        }
                        if($room->slug == "wynton-joy-2-sharing"){
                            $roomType = "2 Sharing";
                            $roomName = "wynton-joy-2-sharing";
                        }
                    }

                    if($request->property == "Dunvista Mansions"){
                        if($room->slug == "dunvista-mansions-single-room"){
                            $roomType = "Single";
                            $roomName = "dunvista-mansions-single-room";
                        }
                        if($room->slug == "dunvista-mansions-2-sharing"){
                            $roomType = "2 Sharing";
                            $roomName = "dunvista-mansions-2-sharing";
                        }
                    }

                    if($request->property == "49 Jorissen"){
                        if($room->slug == "49-jorissen-single-room-2-bedroom-apartment"){
                            $roomType = "Single 2 bedroom";
                            $roomName = "49-jorissen-single-room-2-bedroom-apartment";
                        }
                        if($room->slug == "49-jorissen-single-room-3-bedroom-apartment"){
                            $roomType = "Single 3 bedroom";
                            $roomName = "49-jorissen-single-room-3-bedroom-apartment";
                        }
                        if($room->slug == "49-jorissen-single-room-4-bedroom-apartment"){
                            $roomType = "Single 4 bedroom";
                            $roomName = "49-jorissen-single-room-4-bedroom-apartment";
                        }
                        if($room->slug == "49-jorissen-2-sharing-hotel"){
                            $roomType = "2 Sharing 2 Bedroom | 2 Sharing";
                            $roomName = "49-jorissen-2-sharing-hotel";
                        }
                    }

                    if($request->property == "80 Jorissen"){
                        if($room->slug == "80-jorissen-single-room"){
                            $roomType = "Single";
                            $roomName = "80-jorissen-single-room";
                        }
                        if($room->slug == "80-jorissen-2-sharing"){
                            $roomType = "2 Sharing";
                            $roomName = "80-jorissen-2-sharing";
                        }
                    }

                    if($request->property == "Braamlofts"){
                        if($room->slug == "braamlofts-single-room"){
                            $roomType = "Single";
                            $roomName = "braamlofts-single-room";
                        }
                        if($room->slug == "braamlofts-2-sharing"){
                            $roomType = "2 Sharing";
                            $roomName = "braamlofts-2-sharing";
                        }
                        if($room->slug == "braamlofts-3-sharing"){
                            $roomType = "3 Sharing";
                            $roomName = "braamlofts-3-sharing";
                        }
                    }

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
        


        //Get default lease data for the booking
        $lookup_lease_dates = Lookups::where(['group_id' => '4', 'lookup_name' => $request->year])->first();

        $booking_data['lease_from_date'] = $lookup_lease_dates['lease_from_date'];
        $booking_data['lease_from_time'] = $lookup_lease_dates['lease_from_time'];
        $booking_data['lease_to_date'] = $lookup_lease_dates['lease_to_date'];
        $booking_data['lease_to_time'] = $lookup_lease_dates['lease_to_time'];

        $booking = Booking::create($booking_data);

        //Link booking to User
        User::where('id', $user->id)->update(["bookingRef" => $booking->id]);

        ActionList::create([
            "userUid" => $user->id,
            "action" => "Registered ".$request->firstName." as a new user",
            "actionUserUid" => Auth::user()->id,
            "actionUserDisplayName" => Auth::user()->userFirstName,
            "actionUserRole" => Auth::user()->userRole,
        ]);

        $residence = Residence::where('residence', $request->property)->first();

        if($booking_data['room_rate'] == "0"){
            UserFiles::create([
                'userUid'=> $user->id,
                'fileId' => 'proof_of_payment',
                'status' => 'Approved'
            ]);

            $actionFileData['userUid'] = $user->id;
            $actionFileData['actionUserUid'] = Auth::user()->id;
            $actionFileData['actionUserDisplayName'] = Auth::user()->userFirstName;
            $actionFileData['actionUserRole'] = Auth::user()->userRole;
        
            ActionList::create($actionFileData);
        }

        // if(($request->studentType == "NSFAS" && ($residence->nsfas_booking_rate == "0" || $residence->nsfas_booking_rate == "")) || ($request->studentType == "Bursary" && ($residence->bursary_booking_rate == "0" || $residence->bursary_booking_rate == "")) || ($request->studentType == "Cash / Direct" && ($residence->booking_rate == "0" || $residence->booking_rate == ""))){
            
        //     UserFiles::create([
        //         'userUid'=> $user->id,
        //         'fileId' => 'proof_of_payment',
        //         'status' => 'Approved'
        //     ]);
        
        //     ActionList::create([
        //         "userUid" => $user->id,
        //         "action" => "Booking Fee Waived",
        //         "actionUserUid" => Auth::user()->id,
        //         "actionUserDisplayName" => Auth::user()->userFirstName,
        //         "actionUserRole" => Auth::user()->userRole,
        //     ]);
        // }

        // return redirect()->route('users');
        return redirect('/application-profile/'.$user->id);
    }

    public function new_admin(Request $request){

        return view('admin.userAdd', ["headerTitle" => "Add New System User", "title" => "Add User"]);
    }

    public function generateRandomString($length = 8) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function add_admin(Request $request){

        // if($request->password == ""){
            $password = $this->generateRandomString();
        // }else{
        //     $password = $request->password;
        // }

        User::create([
            "name" => $request->firstName,
            "userFirstName" => $request->firstName,
            "userLastname" => $request->lastName,
            // "userTitle" => $request->title,
            // "userDob" => $request->dob,
            // "userCell" => $request->cell_no,
            "userEmail" => $request->email,
            "email" => $request->email,
            "password" => Hash::make($password),
            "userRole" => "1",
            "role_create_app" => $request->create_app,
            "role_edit_app" => $request->edit_app,
            "role_upload_docs" => $request->upload_docs,
            "role_approve_docs" => $request->approve_docs,
            "role_delete_booking" => $request->delete_booking,
            "role_delete_uploaded" => $request->delete_uploaded,
            "role_braamlofts" => $request->role_braamlofts,
            "role_80_jorissen" => $request->role_80_jorissen,
            "role_49_jorissen" => $request->role_49_jorissen,
            "role_dunvista_mansions" => $request->role_dunvista_mansions,
            "role_wynton_joy" => $request->role_wynton_joy,
            "role_126_siemert" => $request->role_126_siemert,
            "role_ymca" => $request->role_ymca,
            "role_change_date" => $request->role_change_date,
            "role_change_bed" => $request->role_change_bed,
            "role_manage_users" => $request->role_manage_users,
            "email_info" => $request->email_details,
            "role_nsfas_approval_upload" => $request->role_nsfas_approval_upload,
            "role_cancel_booking" => $request->role_cancel_booking,
            "role_update_booking_fee" => $request->role_update_booking_fee,
            "role_lookup" => $request->role_lookup,
            "role_comment" => $request->role_comment,
            "user_role_name" => $request->user_role_name,
        ]);

        if($request->email_details == "1"){

            $data['name'] = $request->firstName;
            $data['email'] = $request->email;
            $data['password'] = $password;

            Mail::send('email.system_user_application', $data, function($message) use ($data) {
                $message->to($data['email'], $data['name'])->subject
                    ('Campus Africa User Registered');
                $message->from('hello@campusafrica.co.za','Campus Africa');
            });

        }
        

        return redirect()->route('users');
    }

    public function delete_lookup(Request $request){

        Lookups::where('id', $request->lookupId)->delete();

        return 'true';
    }

    public function app_filter(Request $request){
        
        $search_value = [];

        if($request->fieldStatus !== "" && !empty($request->fieldStatus)) {
            if($request->fieldStatus !== "All"){

                if($request->fieldStatus == "bookingStatus"){
                    if(in_array("Incomplete", $request->status)){
                        $bookingStatus = '(u.bookingStatus LIKE "%Empty%" OR u.bookingStatus LIKE "%Rejected%")';
                        array_push($search_value, $bookingStatus);
                    }
                    
                    if(in_array("Valid", $request->status)){
                        $bookingStatus = 'u.bookingStatus LIKE "%Approved%"';
                        array_push($search_value, $bookingStatus);
                    }
                    
                    if(in_array("Pending", $request->status)){
                        $bookingStatus = 'u.bookingStatus LIKE "%Action%"';
                        array_push($search_value, $bookingStatus);
                    }

                    if(in_array("NA", $request->status)){
                        $bookingStatus = 'u.overall_status LIKE "%Cancelled%"';
                        array_push($search_value, $bookingStatus);
                    }

                }elseif($request->fieldStatus == "applicationStatus"){
                    if(in_array("Incomplete", $request->status)){
                        $applicationStatus = '(u.applicationStatus LIKE "%Empty%" OR u.applicationStatus LIKE "%Rejected%")';
                        array_push($search_value, $applicationStatus);
                    }
                    
                    if(in_array("Valid", $request->status)){
                        $applicationStatus = 'u.applicationStatus = "Approved Confirmed"';
                        array_push($search_value, $applicationStatus);
                    }
                    
                    if(in_array("Pending", $request->status)){
                        $applicationStatus = 'u.applicationStatus LIKE "%Action%"';
                        array_push($search_value, $applicationStatus);
                    }
                    // if(in_array("Pending", $request->status)){
                    //     $applicationStatus = 'u.applicationStatus = "Approved"';
                    //     array_push($search_value, $applicationStatus);
                    // }

                    if(in_array("NA", $request->status)){
                        $applicationStatus = 'u.overall_status LIKE "%Cancelled%"';
                        array_push($search_value, $applicationStatus);
                    }

                }

            }
        }


        if($request->studentNo !== "" && !empty($request->studentNo)){
            $studentNo = 'u.studentNo LIKE "%'.$request->studentNo.'%"';
            array_push($search_value, $studentNo);
        }

        if($request->idPassport !== "" && !empty($request->idPassport)){
            $idNo = 'u.userId LIKE "%'.$request->idPassport.'%"';
            array_push($search_value, $idNo);

            $passport = 'u.userPassport LIKE "%'.$request->idPassport.'%"';
            array_push($search_value, $passport);
        }

        if($request->bookingRef !== "" && !empty($request->bookingRef)){
            $bookingRef = 'u.bookingRef LIKE "%'.$request->bookingRef.'%"';
            array_push($search_value, $bookingRef);
        }

        if($request->property !== "" && !empty($request->property)){
            if($request->property !== "All"){
                $property = 'bookings_data.property LIKE "%'.$request->property.'%"';
                array_push($search_value, $property);
            }
        }

        if($request->room_type !== "" && !empty($request->room_type)){
            if($request->room_type !== "All"){
                $room_type = 'bookings_data.roomType LIKE "%'.$request->room_type.'%"';
                array_push($search_value, $room_type);
            }
        }

        if($request->applicant !== "" && !empty($request->applicant)){
            $name = $request->applicant;
            $explodeName = explode(" ", $name);
            if(count($explodeName) > 1){
                $applicant = 'u.userFirstName LIKE "%'.$explodeName[0].'%"';
                array_push($search_value, $applicant);

                $applicant = 'u.userLastname LIKE "%'.$explodeName[1].'%"';
                array_push($search_value, $applicant);
            }else{
                $applicant = 'u.userFirstName LIKE "%'.$request->applicant.'%"';
                array_push($search_value, $applicant);
            }
            
        }

        if($request->student_type !== "" && !empty($request->student_type)){
            if($request->student_type !== "All"){
                $student_type = 'u.studentType LIKE "%'.$request->student_type.'%"';
                array_push($search_value, $student_type);
            }
        }

        if($request->date !== "" && !empty($request->date)){
            $date = 'u.created_at LIKE "%'.$request->date.'%"';
            array_push($search_value, $date);
        }

        $query = [];
        

        foreach($search_value as $value) {
            $statement = " and " . $value;
            array_push($query, $statement);
        }

        //Default Query
        $sql = 'SELECT
                (select GROUP_CONCAT(uf1.`status`) from user_files uf1 where uf1.userUid = u.id and uf1.fileId = "copyID") as copyID,
                (select GROUP_CONCAT(uf2.`status`) from user_files uf2 where uf2.userUid = u.id and uf2.fileId = "proof_of_payment") as proof_of_payment,
                (select GROUP_CONCAT(uf3.`status`) from user_files uf3 where uf3.userUid = u.id and uf3.fileId = "studentCard") as studentCard,
                (select GROUP_CONCAT(uf4.`status`) from user_files uf4 where uf4.userUid = u.id and uf4.fileId = "proofRegistration") as proofRegistration,
                (select GROUP_CONCAT(uf5.`status`) from user_files uf5 where uf5.userUid = u.id and uf5.fileId = "bursaryApproval") as bursaryApproval,
                (select GROUP_CONCAT(uf6.`status`) from user_files uf6 where uf6.userUid = u.id and uf6.fileId = "bankStatement") as bankStatement,
                (select GROUP_CONCAT(uf7.`status`) from user_files uf7 where uf7.userUid = u.id and uf7.fileId = "moveIn") as moveIn,
                (select GROUP_CONCAT(uf8.`status`) from user_files uf8 where uf8.userUid = u.id and uf8.fileId = "leaseStatus") as leaseStatus,
                u.*, bookings_data.*
            FROM
                users u
                LEFT OUTER JOIN bookings_data on u.id = bookings_data.userId
            WHERE u.userRole = 0 ' . implode($query);

        //Get all of our students
        $searched_students = DB::select($sql);

        $userData = [];

        foreach($searched_students as $student) {

            $addUser = 1;
            $counter = 0;

            if($student->applicationStatus !== "Approved Confirmed" && $student->overall_status !== "Cancelled"){
                $counter++;
            }  
            
            if($student->overall_status === "Cancelled"){
                $counter = -30;
            }  

            //Default files that all students have
            $file_types = ["proof_of_payment", "copyID", "studentCard", "proofRegistration", "leaseStatus", "bankStatement", "bursaryApproval"];

            //Add in additional files based on student type. Also block out file types based on student type
            if($student->studentType == "Cash / Direct"){
                // array_push($file_types, "bankStatement");
                $student->bursaryApproval = "na";
            } elseif($student->studentType == "NSFAS" || $student->studentType == "Bursary"){
                // array_push($file_types, "bursaryApproval");
                $student->bankStatement = "na";
            }
            
            //If we are filtering a document type don't auto add students
            if (!empty($request->fieldStatus) && !empty($request->status)) {
                if ($request->fieldStatus != "bookingStatus" && $request->fieldStatus != "applicationStatus") {                
                    $addUser = 0;
                }
            }

            // if ($student->bookingRef == 143) {
            //     print_r($student);
            //     die();
            // }

            if ($student->overall_status != "Cancelled" && empty($student->moveIn) && $student->applicationStatus == "Approved Confirmed") {
                $student->moveIn = "Incomplete";
            }

            //Loop over file types and check if we add users or not. Also adjust the students sort count based on what they have
            foreach($file_types as $ft) {
                if ($addUser == 0) {
                    if ($request->fieldStatus == $ft) {
                        if(in_array("Incomplete", $request->status) && $student->{$ft} === "Rejected"){
                            $addUser = 1;
                        }                        
        
                        if(in_array("Valid", $request->status) && $student->{$ft} == "Approved"){
                            $addUser = 1;
                        }
        
                        if(in_array("Pending", $request->status) && $student->{$ft} == "Uploaded"){
                            $addUser = 1;
                        }   

                        if ($student->overall_status != "Cancelled") {
                            if(in_array("Incomplete", $request->status) && $ft == "bursaryApproval" && empty($student->{$ft})){
                                $addUser = 1;
                            }

                            if(in_array("NA", $request->status) && $ft == "bursaryApproval" && $student->{$ft} == "na"){
                                $addUser = 1;
                            }

                            if(in_array("NA", $request->status) && $ft == "bankStatement" && $student->{$ft} == "na"){
                                $addUser = 1;
                            }                            
                        }
                        
                    }
                }                

                if ($student->{$ft} == "Uploaded") {
                    $counter++;
                    $counter++;
                }

                if($student->{$ft} == "" || $student->{$ft} == "Rejected"){
                    $counter--;
                }
    
                if($student->{$ft} == "Approved"){
                    $counter--;
                    $counter--;
                    $counter--;
                }
    
                // if($student->{$ft} == "na" || empty($student->{$ft})){
                //     $counter--;
                //     $counter--;
                //     $counter--;
                // }
            } 
            

            //Only add users that pass the above checks
            if ($addUser == 1) {
                $student->counter = $counter;

                //Filter data based on the users roles
                if(
                    (Auth::user()->role_braamlofts == "1" && $student->property == "Braamlofts") ||
                    (Auth::user()->role_80_jorissen == "1" && $student->property == "80 Jorissen") ||
                    (Auth::user()->role_49_jorissen == "1" && $student->property == "49 Jorissen") ||
                    (Auth::user()->role_dunvista_mansions == "1" && $student->property == "Dunvista Mansions") ||
                    (Auth::user()->role_wynton_joy == "1" && $student->property == "Wynton Joy") ||
                    (Auth::user()->role_126_siemert == "1" && $student->property == "126 Siemert") ||
                    (Auth::user()->role_ymca == "1" && $student->property == "YMCA")
                ) {
                    array_push($userData, (array) $student);
                }
            }

        }   

        //Sort our data based on our weighted counter
        usort($userData, function($a, $b) {
            return $b['counter'] - $a['counter'];
        });            

        return json_encode($userData);
    }

    public function delete_booking(Request $request){

        Booking::where('id', $request->bookingId)->delete();
        User::where('id', $request->userId)->delete();

        return 'true';
    }

    public function cancel_booking(Request $request){
        User::where('id', $request->userId)->update(['overall_status' => 'Cancelled', 'bookingStatus' => 'Cancelled', 'applicationStatus' => 'Cancelled']);

        Booking::where('userId', $request->userId)->update(['property_data_id' => null, 'floor' => null, 'unit' => null, 'room' => null, 'bed' => null, 'bed_code' => null, 'room_rate' => null]);

        ActionList::create([
            "userUid" => $request->userId,
            "action" => "Application Cancelled",
            "actionUserUid" => Auth::user()->id,
            "actionUserDisplayName" => Auth::user()->userFirstName,
            "actionUserRole" => Auth::user()->userRole,
        ]);

        return 'true';
    }

    public function reports(Request $request){
        return view('admin.reports', ["headerTitle" => "Reports", "title" => "Reports"]);
    }

    public function download_report(Request $request){
        $search_value = [];

        if($request->bookingStatus !== "" && !empty($request->bookingStatus)){

            if($request->bookingStatus !== "All"){
            
                if($request->bookingStatus == "Empty"){
                    $bookingStatus = '(users.bookingStatus LIKE "%Empty%" OR users.bookingStatus LIKE "%Rejected%")';
                    array_push($search_value, $bookingStatus);
                }else{
                    $bookingStatus = 'users.bookingStatus LIKE "%'.$request->bookingStatus.'%"';
                    array_push($search_value, $bookingStatus);
                }
            }
        }

        if($request->applicationStatus !== "" && !empty($request->applicationStatus)){

            if($request->applicationStatus !== "All"){
            
                if($request->applicationStatus == "Empty"){
                    $applicationStatus = '(users.applicationStatus LIKE "%Empty%" OR users.applicationStatus LIKE "%Rejected%")';
                    array_push($search_value, $applicationStatus);
                }else{
                    $applicationStatus = 'users.applicationStatus LIKE "%'.$request->applicationStatus.'%"';
                    array_push($search_value, $applicationStatus);
                }
            }
        }

        if($request->property !== "" && !empty($request->property)){
            if($request->property !== "All"){
                $property = 'bookings_data.property LIKE "%'.$request->property.'%"';
                array_push($search_value, $property);
            }
        }

        if($request->room_type !== "" && !empty($request->room_type)){
            if($request->room_type !== "All"){
                $room_type = 'bookings_data.roomType LIKE "%'.$request->room_type.'%"';
                array_push($search_value, $room_type);
            }
        }

        if($request->applicant !== "" && !empty($request->applicant)){
            $name = $request->applicant;
            $explodeName = explode(" ", $name);
            if(count($explodeName) > 1){
                $applicant = 'users.userFirstName LIKE "%'.$explodeName[0].'%"';
                array_push($search_value, $applicant);

                $applicant = 'users.userLastname LIKE "%'.$explodeName[1].'%"';
                array_push($search_value, $applicant);
            }else{
                $applicant = 'users.userFirstName LIKE "%'.$request->applicant.'%"';
                array_push($search_value, $applicant);
            }
            
        }

        if($request->student_type !== "" && !empty($request->student_type)){
            if($request->student_type !== "All"){
                $student_type = 'users.studentType LIKE "%'.$request->student_type.'%"';
                array_push($search_value, $student_type);
            }
        }

        if($request->date !== "" && !empty($request->date)){
            $date = 'users.created_at LIKE "%'.$request->date.'%"';
            array_push($search_value, $date);
        }

        $query = [];
        $i = 0;
        foreach($search_value as $value){
            if($i == 0){
                $statement = "where ".$value;
                array_push($query, $statement);
            }else{
                $statement = " and ".$value;
                array_push($query, $statement);
            }
            $i ++;
        }

        if(count($query) > 0){
            $searched_students = DB::select('SELECT * FROM users LEFT OUTER JOIN bookings_data on users.id = bookings_data.userId '.implode($query).' AND users.userRole = "0"');
        }else{
            $searched_students = DB::select('SELECT * FROM users LEFT OUTER JOIN bookings_data on users.id = bookings_data.userId WHERE users.userRole = "0"');
        }

        $users = [
			['<b>First Name</b>', '<b>Last Name</b>', '<b>Title</b>', '<b>DOB</b>', '<b>Cell</b>', '<b>Email</b>', '<b>Nationality</b>', '<b>I.D Number</b>', '<b>Passport</b>',  '<b>Student Type</b>', '<b>Year</b>', '<b>Institution of Study</b>', '<b>Student Number</b>', '<b>Year of Study</b>', '<b>Faculty</b>',

            '<b>Processing Status</b>',
            '<b>Booking Status</b>',
            '<b>Application Status</b>',
            '<b>Lease Status</b>',

            '<b>Application Number</b>',
            '<b>Application Date</b>',
            
            '<b>Residence</b>', '<b>Room Type</b>', '<b>Floor</b>', '<b>Unit</b>', '<b>Room</b>', '<b>Bed Code</b>', '<b>Burser Company Name</b>', '<b>Burser Registeration Number</b>', '<b>Burser Contact Person</b>', '<b>Burser Contact Number</b>', '<b>Burser Email</b>', '<b>Parent First Name</b>', '<b>Parent Last Name</b>', '<b>Parent Cellphone Number</b>', '<b>Parent Email</b>', '<b>Parent Nationality</b>', '<b>Parent I.D Number</b>', '<b>Parent Passport</b>', '<b>Parent Street No</b>', '<b>Parent Suburb</b>', '<b>Parent City</b>', '<b>Parent Postal Code</b>', '<b>Next of Kin First Name</b>', '<b>Next of Kin Last Name</b>', '<b>Next of Kin Title</b>', '<b>Next of Kin Relationship</b>', '<b>Next of Kin Nationality</b>', '<b>Next of Kin I.D Number</b>', '<b>Next of Kin Passport</b>', '<b>Next of Kin Cellphone Number</b>', '<b>Next of Kin Email</b>', '<b>Next of Kin Work Number</b>', '<b>Next of Kin Telephone Number</b>', '<b>Next of Kin Street No</b>', '<b>Next of Kin Suburb</b>', '<b>Next of Kin City</b>', '<b>Next of Kin Postal Code</b>',],
		];

        foreach($searched_students as $studentData){
            $booking = Booking::where('userId', $studentData->userId)->first();
            $student = User::where('id', $studentData->userId)->first();

            if($student->studentType == "Cash / Direct"){
                $student->studentType = "Cash";
            }

            $lookupsInstit = Lookups::where('id', $student->institutionStudy)->first();
            $lookupsFaculty = Lookups::where('id', $student->faculty)->first();

            if($student->bookingStatus == "Action"){
                $bookingStatus = "Pending";
            }else if($student->bookingStatus == "Approved" || $student->bookingStatus == "Approved Confirmed"){
                $bookingStatus = "Approved";
            }else if($student->bookingStatus == "Cancelled"){
                $bookingStatus = "Cancelled";
            }else if($student->bookingStatus == "Rejected Confirmed"){
                $bookingStatus = "Rejected";
            }else{
                $bookingStatus = "Incomplete";
            }

            if($student->applicationStatus == "Action"){
                $applicationStatus = "Pending";
            }else if($student->applicationStatus == "Approved" || $student->applicationStatus == "Approved Confirmed"){
                $applicationStatus = "Approved";
            }else if($student->applicationStatus == "Cancelled"){
                $applicationStatus = "Cancelled";
            }else if($student->applicationStatus == "Rejected Confirmed"){
                $applicationStatus = "Rejected";
            }else{
                $applicationStatus = "Incomplete";
            }

            $all = [0 => 'moveIn', 1 => 'proof_of_payment', 2 => 'copyID', 3 => 'studentCard', 4 => 'proofRegistration', 5 => 'bursaryApproval', 6 => "bankStatement" ];
            $uploaded = [];
            $data = [];

            $user = User::with('files')->where('id', $studentData->userId)->first();

            foreach($user->files as $file){
                array_push($uploaded, $file->fileId);
                array_push($data, ["field" => $file->fileId, "status" => $file->status]);
            }
            $left = array_diff($all, $uploaded);
            foreach($left as $dataLeft){
                if($dataLeft == "moveIn"){
                    if($user->applicationStatus == "Approved Confirmed"){
                        array_push($data, ["field" => $dataLeft, "status" => "Not Uploaded"]);
                    }else{
                        array_push($data, ["field" => $dataLeft, "status" => "na"]);
                    }
                }               
            }


            foreach($data as $file){
                if($file['field'] == "moveIn"){
                    if($file['status'] == "Action" || $file['status'] == "Pending"){
                        $leaseStatus = "Pending";
                    }else if($file['status'] == "Approved"){
                        $leaseStatus = "Approved";
                    }else if($file['status'] == "Rejected"){
                        $leaseStatus = "Rejected";
                    }else if($file['status'] == "Not Uploaded"){
                        $leaseStatus = "Not Uploaded";
                    }else if($student->overall_status == "Cancelled"){
                        $leaseStatus = "Cancelled";
                    }else{
                        $leaseStatus = "N/A";
                    }
                }
            }

            $studentData = [$student->userFirstName, $student->userLastname, $student->userTitle, $student->userDob, $student->userCell, $student->email, $student->userNationality, $student->userId, $student->userPassport, $student->studentType, $student->year, $lookupsInstit->lookup_name, $student->studentNo, $student->yearOfStudy, $lookupsFaculty->lookup_name,
            $student->overall_status,
            $bookingStatus,
            $applicationStatus,
            $leaseStatus,
            $booking->id,
            date_format(date_create($student->created_at),"Y/m/d"),
            $booking->property,
             $booking->roomType, $booking->floor, $booking->unit, $booking->room, $booking->bed_code, $student->burCompanyName, $student->burRegNo, $student->burContactPerson, $student->burContactNo, $student->burEmail, $student->sponFirstName, $student->sponLastName, $student->sponCell, $student->sponEmail, $student->pNationality, $student->sponId, $student->pPassportNo, $student->pAddress1, $student->pAddress2, $student->pAddressCity, $student->pAddressPostal, $student->kinFirstName, $student->kinLastName, $student->kinTitle, $student->kinRelationship, $student->kinNationality, $student->kinId, $student->kinPassportNo, $student->kinCellNo, $student->kinEmail, $student->kinWorkNo, $student->kinTelNo, $student->kinAddressStr, $student->kinAddressSuburb, $student->kinAddressCity, $student->kinAddressPost ];
            array_push($users, $studentData);
        }

        $xlsx = new \SimpleXLSXGen();
		$xlsx->addSheet( $users, 'Students');
		$xlsx->saveAs('Student Export.xlsx');

		return "true";
    }

    public function email_check(Request $request){
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

    public function change_booking(Request $request){

        $bookingId = $request->bookingId;
        $property = $request->property;
        $roomTypeNew = $request->roomType;

        if($request->floor == "Choose a Floor"){
            //Changing Only Property & Room Type

            $oldData = Booking::where('userId', $bookingId)->first();
            $newData = ['property|Property' => $property, 'roomType|Room Type' => $roomTypeNew];

            $propertyOld = Property::where('user_id_room', $bookingId)->update(["user_id_room" => null, "room_available" => null]);

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
    
            Booking::where('userId', $bookingId)->update(["property" => $property, "roomType" => $roomTypeNew]);

            

        }else{

            $oldData = Booking::where('userId', $bookingId)->first();
            $newData = ['property|Property' => $property, 'roomType|Room Type' => $roomTypeNew, "floor|Floor" => $request->floor, "unit|Unit" => $request->unit, "bed_code|Bed Code"=> $request->bed_code];
    
            $propertyOld = Property::where('user_id_room', $bookingId)->update(["user_id_room" => null, "room_available" => null]);
    
            Property::where('residence', $property)->where('room_type', $roomTypeNew)->where("floor", $request->floor)->where('unit', $request->unit)->where('bed_code', $request->bed_code)->update(["room_available" => "1", "user_id_room"=>$bookingId]);
    
            $propertyData = Property::where('residence', $property)->where('room_type', $roomTypeNew)->where("floor", $request->floor)->where('unit', $request->unit)->where('bed_code', $request->bed_code)->first();
            
    
    
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
    
            Booking::where('userId', $bookingId)->update(["property" => $property, "roomType" => $roomTypeNew, "floor" => $request->floor, "unit" => $request->unit, "room" => $request->room, "bed" => $request->bed, "bed_code"=> $request->bed_code, "property_data_id" => $propertyData->id]);
    
    
            $lookup = Lookups::where('group_id', '7')->first();
            $booking = Booking::where('userId', $bookingId)->first();
            $user = User::where('id', $bookingId)->first();
            $booking_data = [];
    
            if($lookup && $lookup->lookup_name == "1"){
                $booking_data['room_rate'] = "0";
                $actionFileData['action'] = "Booking Fee Waived";
            }else{
                //Get residences from WP
                $jsonDataResidence = json_decode(file_get_contents('https://www.campusafrica.co.za/wp-json/wp/v2/residences'));
                $residenceId = "";
    
                foreach($jsonDataResidence as $residence){
                    if($residence->title->rendered == $property){
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
                        if($property == "YMCA"){
                            if($room->slug == "ymca-3-sharing"){
                                $roomType = "3 Sharing";
                                $roomName = "ymca-3-sharing";
                            }
                            if($room->slug == "ymca-2-sharing"){
                                $roomType = "2 Sharing";
                                $roomName = "ymca-2-sharing";
                            }
                            if($room->slug == "ymca-single-room-large"){
                                $roomType = "Single Large";
                                $roomName = "ymca-single-room-large";
                            }
                            if($room->slug == "ymca-single-room-medium"){
                                $roomType = "Single Medium";
                                $roomName = "ymca-single-room-medium";
                            }
                            if($room->slug == "ymca-single-room-small"){
                                $roomType = "Single Small";
                                $roomName = "ymca-single-room-small";
                            }
                        }
    
                        if($property == "126 Siemert"){
                            if($room->slug == "126-siemert-single-room"){
                                $roomType = "Single";
                                $roomName = "126-siemert-single-room";
                            }
                            if($room->slug == "126-siemert-2-sharing"){
                                $roomType = "2 Sharing";
                                $roomName = "126-siemert-2-sharing";
                            }
                        }
    
                        if($property == "Wynton Joy"){
                            if($room->slug == "wynton-joy-single-room"){
                                $roomType = "Single";
                                $roomName = "wynton-joy-single-room";
                            }
                            if($room->slug == "wynton-joy-2-sharing"){
                                $roomType = "2 Sharing";
                                $roomName = "wynton-joy-2-sharing";
                            }
                        }
    
                        if($property == "Dunvista Mansions"){
                            if($room->slug == "dunvista-mansions-single-room"){
                                $roomType = "Single";
                                $roomName = "dunvista-mansions-single-room";
                            }
                            if($room->slug == "dunvista-mansions-2-sharing"){
                                $roomType = "2 Sharing";
                                $roomName = "dunvista-mansions-2-sharing";
                            }
                        }
    
                        if($property == "49 Jorissen"){
                            if($room->slug == "49-jorissen-single-room-2-bedroom-apartment"){
                                $roomType = "Single 2 bedroom";
                                $roomName = "49-jorissen-single-room-2-bedroom-apartment";
                            }
                            if($room->slug == "49-jorissen-single-room-3-bedroom-apartment"){
                                $roomType = "Single 3 bedroom";
                                $roomName = "49-jorissen-single-room-3-bedroom-apartment";
                            }
                            if($room->slug == "49-jorissen-single-room-4-bedroom-apartment"){
                                $roomType = "Single 4 bedroom";
                                $roomName = "49-jorissen-single-room-4-bedroom-apartment";
                            }
                            if($room->slug == "49-jorissen-2-sharing-hotel"){
                                $roomType = "2 Sharing 2 Bedroom | 2 Sharing";
                                $roomName = "49-jorissen-2-sharing-hotel";
                            }
                        }
    
                        if($property == "80 Jorissen"){
                            if($room->slug == "80-jorissen-single-room"){
                                $roomType = "Single";
                                $roomName = "80-jorissen-single-room";
                            }
                            if($room->slug == "80-jorissen-2-sharing"){
                                $roomType = "2 Sharing";
                                $roomName = "80-jorissen-2-sharing";
                            }
                        }
    
                        if($property == "Braamlofts"){
                            if($room->slug == "braamlofts-single-room"){
                                $roomType = "Single";
                                $roomName = "braamlofts-single-room";
                            }
                            if($room->slug == "braamlofts-2-sharing"){
                                $roomType = "2 Sharing";
                                $roomName = "braamlofts-2-sharing";
                            }
                            if($room->slug == "braamlofts-3-sharing"){
                                $roomType = "3 Sharing";
                                $roomName = "braamlofts-3-sharing";
                            }
                        }
    
                        $appliesTo = $room->acf->booking_fee_applies_to;
    
                        if($room->slug == $roomName && $roomType == $roomTypeNew){
    
    
                            if($room->acf->booking_fee_override !== ""){
                                $booking_data['room_rate'] = $room->acf->booking_fee_override;
                            }
    
                            if($user->studentType == "NSFAS"){
                                if(!in_array("NSFAS Students", $appliesTo)){
                                    $booking_data['room_rate'] = "0";
                                    $actionFileData['action'] = "Booking Fee is R0";
                                }
                            }
    
                            if($user->studentType == "Bursary"){
                                if(!in_array("Bursary Students", $appliesTo)){
                                    $booking_data['room_rate'] = "0";
                                    $actionFileData['action'] = "Booking Fee is R0";
                                }
                            }
    
                            if($user->studentType == "Cash / Direct"){
                                if(!in_array("Cash Students", $appliesTo)){
                                    $booking_data['room_rate'] = "0";
                                    $actionFileData['action'] = "Booking Fee is R0";
                                }
                            }
                        }
                    }
                }
            }
    
            
    
            if($booking_data['room_rate'] == "0"){
    
                $uploaded = UserFiles::where('userUid', $bookingId)->where('fileId', 'proof_of_payment')->first();
    
                if(!$uploaded){
                    UserFiles::create([
                        'userUid' => $bookingId,
                        'fileId' => 'proof_of_payment',
                        'status' => 'Approved'
                    ]);
                }else{
                    UserFiles::where('userUid', $bookingId)->where('fileId', 'proof_of_payment')->update([
                        'status' => 'Approved'
                    ]);
                }
    
                Booking::where('userId', $bookingId)->update(['room_rate' => $booking_data['room_rate']]);
    
                $actionFileData['userUid'] = $bookingId;
                $actionFileData['actionUserUid'] = Auth::user()->id;
                $actionFileData['actionUserDisplayName'] = Auth::user()->userFirstName;
                $actionFileData['actionUserRole'] = Auth::user()->userRole;
            
                ActionList::create($actionFileData);
            }else{
                // UserFiles::where('userUid', $bookingId)->where('fileId', 'proof_of_payment')->update([
                //     'status' => 'Rejected'
                // ]);
                $fileUploaded = UserFiles::where('userUid', $bookingId)->where('fileId', 'proof_of_payment')->first();

                if($fileUploaded && $fileUploaded->fileUrl !== null){
                    if($oldData->room_rate !== $booking_data['room_rate']){

                        UserFiles::where('userUid', $bookingId)->where('fileId', 'proof_of_payment')->update([
                            'status' => 'Uploaded'
                        ]);
                    }
                }else{
                    UserFiles::where('userUid', $bookingId)->where('fileId', 'proof_of_payment')->update([
                        'status' => 'Rejected'
                    ]);
                }

                //Recalculate booking status
                User::where('id', $bookingId)->update([
                    'bookingStatus' => 'Action'
                ]);
    
                Booking::where('userId', $bookingId)->update(['room_rate' => $booking_data['room_rate']]);
    
                $actionFileData['userUid'] = $bookingId;
                $actionFileData['action'] = "Booking Fee updated to R".$booking_data['room_rate'];
                $actionFileData['actionUserUid'] = Auth::user()->id;
                $actionFileData['actionUserDisplayName'] = Auth::user()->userFirstName;
                $actionFileData['actionUserRole'] = Auth::user()->userRole;
            
                ActionList::create($actionFileData);
            }
        }



        return "true";
    }

    public function change_lease(Request $request){

        $bookingId = $request->bookingId;
        
        $lease_from_date = $request->lease_from_date;
        $lease_from_time = $request->lease_from_time;
        $lease_to_date = $request->lease_to_date;
        $lease_to_time = $request->lease_to_time;        

        $oldData = Booking::where('userId', $bookingId)->first();
        $newData = ['lease_from_date|Lease From Date' => $lease_from_date, 'lease_from_time|Lease From Time' => $lease_from_time, "lease_to_date|Lease To Date" => $request->lease_to_date, "lease_to_time|Lease To Time" => $request->lease_to_time];

        Booking::where('userId', $bookingId)->update(["lease_from_date" => $lease_from_date, "lease_from_time"=>$lease_from_time, "lease_to_date"=>$lease_to_date, "lease_to_time"=>$lease_to_time, "lease_unique" => "1"]);

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

        return "true";
    }

    public function manage_properties(Request $request){

        $properties = Property::get();

        return view('admin.manage_properties', ["headerTitle" => "Manage Properties", "properties" => $properties, "title" => "Manage Properties"]);
    }

    public function property_edit(Request $request, $propertyId){
        // $propertyId

        if($propertyId == "0"){
            $head = "Add";
            $propertyData = "";
        }else{
            $head = "Edit";
            $propertyData = Property::where('id', $propertyId)->first();
        }

        return view('admin.property_edit', ["headerTitle" => $head." Properties", "title" => $head." Properties", "propertyData" => $propertyData]);
    }

    public function save_property (Request $request){

        $propertyId = $request->propertyId;

        $data = [
            "residence" => $request->residence,
            "meta_info_1" => $request->meta_info_1,
            "meta_info_2" => $request->meta_info_2,
            "floor" => $request->floor,
            "unit" => $request->unit,
            "room" => $request->room,
            "bed" => $request->bed,
            "bed_code" => $request->bed_code,
            "inventory" => $request->inventory,
            "room_type" => $request->roomType,
            "room_rate" => $request->room_rate
        ];

        if($propertyId == "0"){
            Property::create($data);
        }else{
            Property::where('id', $propertyId)->update($data);
        }

        return redirect("/manage-properties");
    }

    public function get_booking_info(Request $request){
        $bookingData = Booking::where('userId', $request->bookingId)->first();

        $property = Property::where('user_id_room', $request->bookingId)->first();

        if(!empty($property)){
            $selected = $property;
        }else{
            $selected = [];
        }

        if (isset($request->getType) && $request->getType == "floor") {
            $propertyInfo = Property::where('residence', $request->property)->where('room_type', $request->roomType)->get();

            //Create a unique Floor list
            $floors = [];

            foreach($propertyInfo as $pi) {
                $new_floor = $pi->floor;

                if (!empty($pi->meta_info_1)) {
                    $new_floor .= $pi->meta_info_1;
                }

                if (!isset($floors[$new_floor])) {
                    $floors[$new_floor] = $pi;
                }
            }

            return ["true", $selected, $floors];

        } elseif (isset($request->getType) && $request->getType == "unit") {
            $propertyInfo = Property::where('residence', $request->property)->where('room_type', $request->roomType)->where('floor', $request->floor)->where('meta_info_1', $request->meta)->get();

            //Create a unique Unit list
            $units = [];

            foreach($propertyInfo as $pi) {
                if (!isset($units[$pi->unit])) {
                    $units[$pi->unit] = $pi;
                }
            }

            return ["true", $selected, $units];

        } elseif (isset($request->getType) && $request->getType == "bed_code") {
            $propertyInfo = Property::where('residence', $request->property)->where('room_type', $request->roomType)->where('floor', $request->floor)->where('unit', $request->unit)->get();

            //Create a unique Unit list
            $bed_code = [];
            $booked_bed_codes = [];

            foreach($propertyInfo as $pi) {
                if (!isset($units[$pi->bed_code])) {

                    $bed_used = Booking::with('users')->where('property', $request->property)->where('roomType', $request->roomType)->where('floor', $request->floor)->where('unit', $request->unit)->where('bed_code', $pi->bed_code)->get();

                    if (count($bed_used) == 0) {
                        $bed_code[$pi->bed_code] = $pi;
                    }else{

                        if($bed_used[0]->users->applicationStatus == "Rejected Confirmed" || $bed_used[0]->users->applicationStatus == "Cancelled"){
                            $bed_code[$pi->bed_code] = $pi;
                        }else{
                            $booked_bed_codes[$pi->bed_code] = $pi;
                        }
                    }
                }
            }

            return ["true", $selected, $bed_code, $booked_bed_codes];
        }
    }

    public function properties_import(Request $request){

        return view('admin.imports', ["headerTitle" => "Imports", "title" => "Imports"]);

    }

    public function import_data(Request $request){
        
        $files = $request->file('file');

        $file = fopen($files,"r");
        $i = 0;
        $numberOfFields = 11;

        while (($filedata = fgetcsv($file)) !== FALSE) {
            $num = count($filedata);

            // Skip first row & check number of fields
            if($i > 0 && $num == $numberOfFields){ 
                $property_arr = [];

                if($filedata[0] == "80 JORISSEN"){
                    $property_arr['residence'] = "80 Jorissen";
                }else if($filedata[0] == "DUNVISTA MANSIONS"){
                    $property_arr['residence'] = "Dunvista Mansions";
                }else if($filedata[0] == "BRAAMLOFTS"){
                    $property_arr['residence'] = "Braamlofts";
                }else if($filedata[0] == "49 JORISSEN"){
                    $property_arr['residence'] = "49 Jorissen";
                }else if($filedata[0] == "WYNTON JOY"){
                    $property_arr['residence'] = "Wynton Joy";
                }else if($filedata[0] == "126 SIEMERT"){
                    $property_arr['residence'] = "126 Siemert";
                }else if($filedata[0] == "YMCA"){
                    $property_arr['residence'] = "YMCA";
                }

                $property_arr['meta_info_1'] = $filedata[1];
                $property_arr['meta_info_2'] = $filedata[2];
                $property_arr['floor'] = $filedata[3];

                if($filedata[4] == "" || $filedata[4] == " "){

                    $property_arr['unit'] = "0";
                }else{
                    $property_arr['unit'] = $filedata[4];
                }
                $property_arr['room'] = $filedata[5];
                $property_arr['bed'] = $filedata[6];
                $property_arr['bed_code'] = $filedata[7];

                if($filedata[8] == "YES"){
                    $property_arr['inventory'] = "1";
                }else{
                    $property_arr['inventory'] = "0";
                }

                $property_arr['room_type'] = $filedata[9];
                $property_arr['room_rate'] = $filedata[10];

                Property::create($property_arr);

            }
            $i++;
        }

        fclose($file);

        echo "done";

    }

    public function properties_info(Request $request){
        $residences = Residence::get();

        return view('admin.property_address', ["headerTitle" => "Residense Info", "title" => "Residense Info", "residences" => $residences]);
    }

    public function residence_edit(Request $request, $residenceId){
        if($residenceId == "0"){
            $head = "Add";
            $propertyData = "";
        }else{
            $head = "Edit";
            $propertyData = Residence::where('id', $residenceId)->first();
        }

        return view('admin.residence_edit', ["headerTitle" => $head." Residence", "title" => $head." Residence", "propertyData" => $propertyData]);
    }

    public function save_residence (Request $request){

        $propertyId = $request->propertyId;

        $data = [
            "residence" => $request->residence,
            "booking_rate" => $request->booking_rate,
            "owner" => $request->owner,
            "address" => $request->address,
            "nsfas_booking_rate" => $request->nsfas_booking_rate,
            "bursary_booking_rate" => $request->bursary_booking_rate,
        ];

        if($request->nsfas_booking_rate == null){
            $data['nsfas_booking_rate'] = "0";
        }
        if($request->bursary_booking_rate == null){
            $data['bursary_booking_rate'] = "0";
        }
        if($request->booking_rate == null){
            $data['booking_rate'] = "0";
        }

        if($propertyId == "0"){
            Residence::create($data);
        }else{
            $oldData = Residence::where('id', $propertyId)->first();

            // if($oldData->nsfas_booking_rate !== $data['nsfas_booking_rate']){
            //     if($data['nsfas_booking_rate'] == "0"){
            //         $this->updateInfoRes('NSFAS');
            //     }else{
            //         $this->updateInfoResRedo('NSFAS');
            //     }
            // }

            // if($oldData->bursary_booking_rate !== $data['bursary_booking_rate']){
            //     if($data['bursary_booking_rate'] == "0"){
            //         $this->updateInfoRes('Bursary');
            //     }else{
            //         $this->updateInfoResRedo('Bursary');
            //     }
            // }

            // if($oldData->booking_rate !== $data['booking_rate']){
            //     if($data['booking_rate'] == "0"){
            //         $this->updateInfoRes('Cash / Direct');
            //     }else{
            //         $this->updateInfoResRedo('Cash / Direct');
            //     }
            // }
            
            Residence::where('id', $propertyId)->update($data);
        }

        return redirect("/properties-info");
    }

    public function updateInfoRes($studentType){

        $students = User::where('studentType', $studentType)->get();
        foreach($students as $student){
            $file = UserFiles::where('userUid', $student['id'])->where('fileId', 'proof_of_payment')->first();

            if(!empty($file)){
                UserFiles::where('userUid', $student['id'])->where('fileId', 'proof_of_payment')->update(['status' => 'Approved']);
            }else{
                UserFiles::create([
                    'userUid'=> $student->id,
                    'fileId' => 'proof_of_payment',
                    'status' => 'Approved'
                ]);
            }

            ActionList::create([
                "userUid" => $student['id'],
                "action" => "Booking Fee Waived",
                "actionUserUid" => Auth::user()->id,
                "actionUserDisplayName" => Auth::user()->userFirstName,
                "actionUserRole" => Auth::user()->userRole,
            ]);
        }
    }

    public function updateInfoResRedo($studentType){
        $students = User::where('studentType', $studentType)->get();
        foreach($students as $student){
            $file = UserFiles::where('userUid', $student['id'])->where('fileId', 'proof_of_payment')->first();

            if(!empty($file) && $file->fileUrl !== null){
                UserFiles::where('userUid', $student['id'])->where('fileId', 'proof_of_payment')->update(['status' => 'Rejected']);
            }

            ActionList::create([
                "userUid" => $student['id'],
                "action" => "Booking Fee Added",
                "actionUserUid" => Auth::user()->id,
                "actionUserDisplayName" => Auth::user()->userFirstName,
                "actionUserRole" => Auth::user()->userRole,
            ]);
        }
    }

    public function action_data(Request $request){

        if($request->FileNameForApproval == "studentCard"){
            $name = 'STUDENT CARD / PROOF OF STUDENT NUMBER';
            $field = "student_card_approved";
        }else if($request->FileNameForApproval == "copyID"){
            $name = 'COPY OF ID DOCUMENT';
            $field = "id_data_approved";
        }else if($request->FileNameForApproval == "proofRegistration"){
            $name = 'PROOF OF REGISTRATION';
            $field = "registration_approved";
        }else if($request->FileNameForApproval == "bursaryApproval"){
            $name = 'NSFAS / BURSARY';
            $field = "bursary_approved";
            $field2 = "burser_approved";
        }else if($request->FileNameForApproval == "bankStatement"){
            $name = 'BANK STATEMENT';
            $field = "bank_approved";
            $field2 = "parent_approved";
        }

        $dataToUpdate = [
            $field => $request->type,
        ];

        if(isset($field2)){
            $dataToUpdate[$field2] = $request->type;
        }
        
        User::where('id', $request->userId)->update($dataToUpdate);

        if($request->type == "1"){
            $action = "Approved";
        }else{
            $action = "Rejected";
        }

        ActionList::create([
            "userUid" => $request->userId,
            "action" => $name." Data ".$action,
            "actionUserUid" => Auth::user()->id,
            "actionUserDisplayName" => Auth::user()->userFirstName,
            "actionUserRole" => Auth::user()->userRole,
          ]);

        return "true";
    }

    public function approve_app_data(Request $request){
        if($request->type == "Address"){
            User::where('id', $request->userId)->update(["address_approved" => "2"]);

            ActionList::create([
                "userUid" => $request->userId,
                "action" => "Physical Address Data Approved",
                "actionUserUid" => Auth::user()->id,
                "actionUserDisplayName" => Auth::user()->userFirstName,
                "actionUserRole" => Auth::user()->userRole,
              ]);
        }else{
            User::where('id', $request->userId)->update(["kin_approved" => "2"]);

            ActionList::create([
                "userUid" => $request->userId,
                "action" => "Next of Kin Data Approved",
                "actionUserUid" => Auth::user()->id,
                "actionUserDisplayName" => Auth::user()->userFirstName,
                "actionUserRole" => Auth::user()->userRole,
              ]);
        }
    }

    public function room_types(Request $request){

        $room_types = RoomTypes::get();
        

        return view('admin.room_types', ["headerTitle" => "Room Types", "room_types" => $room_types, "title" => "Room Types"]);
    }

    public function room_type_edit(Request $request, $id){
        if($id == "0"){
            $head = "Add";
            $roomTypeData = "";
        }else{
            $head = "Edit";
            $roomTypeData = RoomTypes::where('id', $id)->first();
        }

        $residence = Residence::get();

        return view('admin.room_type_edit', ["headerTitle" => $head." Room Type", "title" => $head." Room Type", "roomTypeData" => $roomTypeData, "residence" => $residence]);
    }

    public function save_room_type (Request $request){

        $roomTypeId = $request->id;

        $data = [
            "property_id" => $request->property_id,
            "name" => $request->name
        ];

        if($roomTypeId == "0"){
            RoomTypes::create($data);
        }else{
            RoomTypes::where('id', $roomTypeId)->update($data);
        }

        return redirect("/room-types");
    }

    public function nsfas_approval_upload(Request $request){

        $uploads = ImportNSFASApproval::with('users')->get();
        $active = ImportNSFASApproval::where('status', '1')->first();
        if($active){
            $students = ImportNSFASStudentAuditLog::where('importId', $active->id)->where('status', '0')->get();
        }else{
            $students = ImportNSFASStudentAuditLog::where('status', '0')->get();
        }
        
        return view('admin.nsfas_upload', ["headerTitle" => "NSFAS Approval Upload", "title" => "NSFAS Approval Upload", 'uploads' => $uploads, 'students' => $students, 'active' => $active]);
    }

    public function new_import_data(){
        $uploads = ImportNSFASApproval::with('users')->get();
        $active = ImportNSFASApproval::where('status', '1')->first();
        if($active){
            $students = ImportNSFASStudentAuditLog::where('importId', $active->id)->where('status', '0')->get();
        }else{
            $students = ImportNSFASStudentAuditLog::where('status', '0')->get();
        }
        

        return [$uploads, $students];
    }

    public function get_upload_info(Request $request){
        $active = ImportNSFASApproval::where('created_at', $request->timestamp)->first();
        $students = ImportNSFASStudentAuditLog::where('importId', $active->id)->where('status', '0')->get();

        return $students;
    }

    public function import_nsfas_approval(Request $request){
        $files = $request->file('file');

        $inputFileName  = $request->file('file')->getClientOriginalName();

        if ($request->hasFile('file')) {
            
            if ($request->file('file')->isValid()) {
                $userUid = "nsfas_upload";

                $fileName = 'uploads/'.$inputFileName;
                $path = $request->file('file')->move('uploads/', $inputFileName);


                /**  Identify the type of $inputFileName  **/
                $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($fileName);

                /**  Create a new Reader of the type that has been identified  **/
                $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);

                /**  Load $inputFileName to a Spreadsheet Object  **/
                $spreadsheet = $reader->load($fileName);

                /**  Convert Spreadsheet Object to an Array for ease of use  **/
                $schdeules = $spreadsheet->getActiveSheet()->toArray();


                $i = 0;
                // $approved = 0;
                // $skipped = 0;
                $not_found = 0;
                $students_Arr = [];

                $matched = 0;
                $unmatched = 0;
                $totalStudents = 0;

                $fileList = [];
                $actionLogList = [];

                $allStudents = User::with('files')->where("userRole", "0")->get();

                foreach($schdeules as $user){
                    
                    if($i > 0){

                        if($user[0] !== null){
                            $totalStudents++;

                            $idFound = 0;

                            foreach($allStudents as $thisStudent){

                                if($thisStudent->studentNo == $user[0]){
                                    $idFound++;
                                    if($thisStudent->studentType == "NSFAS"){
                                        $files = $thisStudent->files;
                                        $approvalFound = 0;

                                        foreach($files as $file){
                                            if($file->fileId == "bursaryApproval"){
                                                $approvalFound++;

                                                if($file->status !== "Approved"){
                                                    UserFiles::where('userUid', $thisStudent->id)->where('fileId', 'bursaryApproval')->update(['status' => 'Approved', 'fileName' => null, 'fileUrl' => null, 'uploaded_by' => 'Import']);
                                                    array_push($actionLogList, ["userUid" => $thisStudent->id, "action" => "NSFAS / Bursary has been Approved via import", "actionUserUid" => Auth::user()->id, "actionUserDisplayName" => Auth::user()->userFirstName." ".Auth::user()->userLastname, "actionUserRole" => Auth::user()->userRole, "created_at" => date('Y-m-d H:i:s'), "updated_at" => date('Y-m-d H:i:s')]);
                                                }
                                            }
                                        }

                                        if($approvalFound == 0){
                                            array_push($fileList, ['userUid'=> $thisStudent->id, 'fileId' => 'bursaryApproval', 'status' => 'Approved', 'uploaded_by' => 'Import']);
                                            array_push($actionLogList, ["userUid" => $thisStudent->id, "action" => "NSFAS / Bursary has been Approved via import", "actionUserUid" => Auth::user()->id, "actionUserDisplayName" => Auth::user()->userFirstName." ".Auth::user()->userLastname, "actionUserRole" => Auth::user()->userRole, "created_at" => date('Y-m-d H:i:s'), "updated_at" => date('Y-m-d H:i:s')]);
                                        }
                                    }
                                }
                            }

                            if($idFound == "0"){
                                $unmatched ++;
                                array_push($students_Arr, [$user[0], $user[1], "0"]);
                            }else{
                                $matched ++;
                                array_push($students_Arr, [$user[0], $user[1], "1"]);
                            }

                        }
                    }
                    $i++;
                }
            }
        }

        UserFiles::insert($fileList);
        ActionList::insert($actionLogList);

        ImportNSFASApproval::where('status', '1')->update(["status" => 0]);

        $newImport = ImportNSFASApproval::create([ 'file_name' => $inputFileName, 'actionUserId' => Auth::user()->id, "matched" => $matched, "unmatched" => $unmatched, "total_records" => $totalStudents, "status" => 1]);

        $newStudentForImport = [];

        foreach($students_Arr as $student){

            array_push($newStudentForImport, ["importId" => $newImport->id, "student_number" => $student[0], "status" => $student[2], "institution" => $student[1]]);
        }

        foreach (array_chunk($newStudentForImport,1000) as $data)  
        {
            ImportNSFASStudentAuditLog::insert($data); 
        }

        echo "done";
        

        

        // dd($schdeules);

        // $file = fopen($files,"r");
        // $i = 0;
        // $numberOfFields = 3;

        // $approved = 0;
        // $skipped = 0;
        // $not_found = 0;
        // $students_not_found = [];

        // while (($filedata = fgetcsv($file)) !== FALSE) {
            
        //     $num = count($filedata);

        //     // Skip first row & check number of fields
        //     if($i > 0 && $num == $numberOfFields){ 
        
        //         $student = User::where("email", $filedata[2])->first();

        //         if(!empty($student) && $student->studentType == "NSFAS"){
        //             $fileInfo = UserFiles::where('userUid', $student->id)->where('fileId', 'bursaryApproval')->first();

        //             if(!empty($fileInfo) && $fileInfo->status !== "Approved"){
        //                 $approved ++;
        //                 UserFiles::where('userUid', $student->id)->where('fileId', 'bursaryApproval')->update(['status' => 'Approved', 'uploadedBy' => 'Import']);
        //                 ActionList::create([
        //                     "userUid" => $student->id,
        //                     "action" => "Approved via import",
        //                     "actionUserUid" => Auth::user()->id,
        //                     "actionUserDisplayName" => "Import",
        //                     "actionUserRole" => Auth::user()->userRole,
        //                   ]);
                          
        //             }else if(empty($fileInfo)){
        //                 $approved ++;
        //                 UserFiles::create(['userUid'=> $student->id, 'fileId' => 'bursaryApproval', 'status' => 'Approved', 'uploadedBy' => 'Import']);

        //                 ActionList::create([
        //                     "userUid" => $student->id,
        //                     "action" => "Approved via import",
        //                     "actionUserUid" => Auth::user()->id,
        //                     "actionUserDisplayName" => "Import",
        //                     "actionUserRole" => Auth::user()->userRole,
        //                   ]);

        //             }else{
        //                 $skipped ++;
        //             }
        //         }else{
        //             $not_found ++;
        //             array_push($students_not_found, [$filedata[0], $filedata[1], $filedata[2]]);
        //         }
        //     }
        //     $i++;
        // }

        // fclose($file);

        // $newImport = ImportNSFASApproval::create(['approved' => $approved, 'skipped' => $skipped, 'not_found' => $not_found]);

        // foreach($students_not_found as $not_found){

        //     ImportNSFASStudentAuditLog::create(["importId" => $newImport->id, "student_first_name" => $not_found[0], "student_last_name" => $not_found[1], "student_email" => $not_found[2]]);
        // }
        // echo "done";

    }

    public function download_docs(Request $request, $userId){

        $allFilesArr = [];
        $files = UserFiles::where('userUid', $userId)->where('status', 'Approved')->get();
        $userData = User::where('id', $userId)->first();

        foreach($files as $file){
            if($file->fileUrl !== '' && $file->fileUrl !== null){
                array_push($allFilesArr, "storage/".$file->fileUrl);
            }
        }

        $opt = new ArchiveOptions;
        $opt->setSendHttpHeaders(true);
        
        $zip = new ZipStream($userData->userFirstName.' '.$userData->userLastname.' Approved Docs.zip', $opt);

        foreach ($allFilesArr as $file) {
            if (true === file_exists($file)) {
                try {
                    $str_arr = explode ("/", $file); 
                    $fileName = end($str_arr);
                    $zip->addFile($fileName, file_get_contents($file));
                } catch (\Exception $exception) {
                    print_r($exception->getMessage());
                }
            } else {
                print_r(sprintf('File %s does not exist.', $file));
            }
        }

        $zip->finish();

    }

    public function waive_booking_fee(Request $request){
        
        $booking = Booking::where('userId', $request->userId)->update(['room_rate' => '0', 'waive_reason' => $request->reason]);

        $file = UserFiles::where('userUid', $request->userId)->where('fileId', 'proof_of_payment')->first();

        if(!$file){
            UserFiles::create([
                'userUid' => $request->userId,
                'fileId' => 'proof_of_payment',
                'status' => 'Approved'
            ]);
        }else{
            UserFiles::where('userUid', $request->userId)->where('fileId', 'proof_of_payment')->update([
                'status' => 'Approved'
            ]);
        }

    
        ActionList::create([
            "userUid" => $request->userId,
            "action" => "Booking Fee Waived with reason: ".$request->reason,
            "actionUserUid" => Auth::user()->id,
            "actionUserDisplayName" => Auth::user()->userFirstName,
            "actionUserRole" => Auth::user()->userRole,
        ]);
        
    }

    public function undo_waive_booking_fee(Request $request){

        $lookup = Lookups::where('group_id', '7')->first();

        $booking = Booking::where('userId', $request->userId)->first();

        $booking_data = [];


        if($lookup && $lookup->lookup_name == "1"){
            $booking_data['room_rate'] = "0";
        }else{
            //Get residences from WP
            $jsonDataResidence = json_decode(file_get_contents('https://www.campusafrica.co.za/wp-json/wp/v2/residences'));
            $residenceId = "";

            foreach($jsonDataResidence as $residence){
                if($residence->title->rendered == $booking->property){
                    //get room booking fee from residence (WP)
                    $booking_data['room_rate'] = $residence->acf->booking_fee;
                    //get residence ID (WP)
                    $residenceId = $residence->id;
                }
            }

            $jsonDataRooms = json_decode(file_get_contents('https://www.campusafrica.co.za/wp-json/wp/v2/rooms?per_page=100'));
            // dd($jsonDataRooms);

            $roomType = "";
            $roomName = "";

            foreach($jsonDataRooms as $room){
                if($residenceId == $room->acf->residence){
                    if($booking->property == "YMCA"){
                        if($room->slug == "ymca-3-sharing"){
                            $roomType = "3 Sharing";
                            $roomName = "ymca-3-sharing";
                        }
                        if($room->slug == "ymca-2-sharing"){
                            $roomType = "2 Sharing";
                            $roomName = "ymca-2-sharing";
                        }
                        if($room->slug == "ymca-single-room-large"){
                            $roomType = "Single Large";
                            $roomName = "ymca-single-room-large";
                        }
                        if($room->slug == "ymca-single-room-medium"){
                            $roomType = "Single Medium";
                            $roomName = "ymca-single-room-medium";
                        }
                        if($room->slug == "ymca-single-room-small"){
                            $roomType = "Single Small";
                            $roomName = "ymca-single-room-small";
                        }
                    }

                    if($booking->property == "126 Siemert"){
                        if($room->slug == "126-siemert-single-room"){
                            $roomType = "Single";
                            $roomName = "126-siemert-single-room";
                        }
                        if($room->slug == "126-siemert-2-sharing"){
                            $roomType = "2 Sharing";
                            $roomName = "126-siemert-2-sharing";
                        }
                    }

                    if($booking->property == "Wynton Joy"){
                        if($room->slug == "wynton-joy-single-room"){
                            $roomType = "Single";
                            $roomName = "wynton-joy-single-room";
                        }
                        if($room->slug == "wynton-joy-2-sharing"){
                            $roomType = "2 Sharing";
                            $roomName = "wynton-joy-2-sharing";
                        }
                    }

                    if($booking->property == "Dunvista Mansions"){
                        if($room->slug == "dunvista-mansions-single-room"){
                            $roomType = "Single";
                            $roomName = "dunvista-mansions-single-room";
                        }
                        if($room->slug == "dunvista-mansions-2-sharing"){
                            $roomType = "2 Sharing";
                            $roomName = "dunvista-mansions-2-sharing";
                        }
                    }

                    if($booking->property == "49 Jorissen"){
                        if($room->slug == "49-jorissen-single-room-2-bedroom-apartment"){
                            $roomType = "Single 2 bedroom";
                            $roomName = "49-jorissen-single-room-2-bedroom-apartment";
                        }
                        if($room->slug == "49-jorissen-single-room-3-bedroom-apartment"){
                            $roomType = "Single 3 bedroom";
                            $roomName = "49-jorissen-single-room-3-bedroom-apartment";
                        }
                        if($room->slug == "49-jorissen-single-room-4-bedroom-apartment"){
                            $roomType = "Single 4 bedroom";
                            $roomName = "49-jorissen-single-room-4-bedroom-apartment";
                        }
                        if($room->slug == "49-jorissen-2-sharing-hotel"){
                            $roomType = "2 Sharing 2 Bedroom | 2 Sharing";
                            $roomName = "49-jorissen-2-sharing-hotel";
                        }
                    }

                    if($booking->property == "80 Jorissen"){
                        if($room->slug == "80-jorissen-single-room"){
                            $roomType = "Single";
                            $roomName = "80-jorissen-single-room";
                        }
                        if($room->slug == "80-jorissen-2-sharing"){
                            $roomType = "2 Sharing";
                            $roomName = "80-jorissen-2-sharing";
                        }
                    }

                    if($booking->property == "Braamlofts"){
                        if($room->slug == "braamlofts-single-room"){
                            $roomType = "Single";
                            $roomName = "braamlofts-single-room";
                        }
                        if($room->slug == "braamlofts-2-sharing"){
                            $roomType = "2 Sharing";
                            $roomName = "braamlofts-2-sharing";
                        }
                        if($room->slug == "braamlofts-3-sharing"){
                            $roomType = "3 Sharing";
                            $roomName = "braamlofts-3-sharing";
                        }
                    }

                    $appliesTo = $room->acf->booking_fee_applies_to;

                    if($room->slug == $roomName && $roomType == $request->roomType){

                        if($room->acf->booking_fee_override !== ""){
                            $booking_data['room_rate'] = $room->acf->booking_fee_override;
                        }

                        if($booking->studentType == "NSFAS"){
                            if(!in_array("NSFAS Students", $appliesTo)){
                                $booking_data['room_rate'] = "0";
                            }
                        }

                        if($booking->studentType == "Bursary"){
                            if(!in_array("Bursary Students", $appliesTo)){
                                $booking_data['room_rate'] = "0";
                            }
                        }

                        if($booking->studentType == "Cash / Direct"){
                            if(!in_array("Cash Students", $appliesTo)){
                                $booking_data['room_rate'] = "0";
                            }
                        }
                    }

                    
                }
            }
        }

        $booking = Booking::where('userId', $request->userId)->update($booking_data);

        UserFiles::where('userUid', $request->userId)->where('fileId', 'proof_of_payment')->update([
            'status' => 'Rejected'
        ]);
    
        ActionList::create([
            "userUid" => $request->userId,
            "action" => "Booking Fee Waiver Undone",
            "actionUserUid" => Auth::user()->id,
            "actionUserDisplayName" => Auth::user()->userFirstName,
            "actionUserRole" => Auth::user()->userRole,
        ]);
        
    }

    public function update_booking_fees(Request $request){
        
        $bookingList = Booking::whereNotNull('property')->whereNotNull('roomType')->get();

        $lookup = Lookups::where('group_id', '7')->first();

        $jsonDataResidence = json_decode(file_get_contents('https://www.campusafrica.co.za/wp-json/wp/v2/residences'));
        $jsonDataRooms = json_decode(file_get_contents('https://www.campusafrica.co.za/wp-json/wp/v2/rooms?per_page=100'));

        foreach($bookingList as $booking){
            $user = User::where('id', $booking->userId)->first();

            $booking_data = [];

            if($lookup && $lookup->lookup_name == "1"){
                $booking_data['room_rate'] = "0";
            }else{
                //Get residences from WP
                
                $residenceId = "";

                foreach($jsonDataResidence as $residence){
                    if($residence->title->rendered == $booking->property){
                        //get room booking fee from residence (WP)
                        $booking_data['room_rate'] = $residence->acf->booking_fee;
                        //get residence ID (WP)
                        $residenceId = $residence->id;
                    }
                }

                
                // dd($jsonDataRooms);

                $roomType = "";
                $roomName = "";

                foreach($jsonDataRooms as $room){
                    if($residenceId == $room->acf->residence){
                        if($booking->property == "YMCA"){
                            if($room->slug == "ymca-3-sharing"){
                                $roomType = "3 Sharing";
                                $roomName = "ymca-3-sharing";
                            }
                            if($room->slug == "ymca-2-sharing"){
                                $roomType = "2 Sharing";
                                $roomName = "ymca-2-sharing";
                            }
                            if($room->slug == "ymca-single-room-large"){
                                $roomType = "Single Large";
                                $roomName = "ymca-single-room-large";
                            }
                            if($room->slug == "ymca-single-room-medium"){
                                $roomType = "Single Medium";
                                $roomName = "ymca-single-room-medium";
                            }
                            if($room->slug == "ymca-single-room-small"){
                                $roomType = "Single Small";
                                $roomName = "ymca-single-room-small";
                            }
                        }

                        if($booking->property == "126 Siemert"){
                            if($room->slug == "126-siemert-single-room"){
                                $roomType = "Single";
                                $roomName = "126-siemert-single-room";
                            }
                            if($room->slug == "126-siemert-2-sharing"){
                                $roomType = "2 Sharing";
                                $roomName = "126-siemert-2-sharing";
                            }
                        }

                        if($booking->property == "Wynton Joy"){
                            if($room->slug == "wynton-joy-single-room"){
                                $roomType = "Single";
                                $roomName = "wynton-joy-single-room";
                            }
                            if($room->slug == "wynton-joy-2-sharing"){
                                $roomType = "2 Sharing";
                                $roomName = "wynton-joy-2-sharing";
                            }
                        }

                        if($booking->property == "Dunvista Mansions"){
                            if($room->slug == "dunvista-mansions-single-room"){
                                $roomType = "Single";
                                $roomName = "dunvista-mansions-single-room";
                            }
                            if($room->slug == "dunvista-mansions-2-sharing"){
                                $roomType = "2 Sharing";
                                $roomName = "dunvista-mansions-2-sharing";
                            }
                        }

                        if($booking->property == "49 Jorissen"){
                            if($room->slug == "49-jorissen-single-room-2-bedroom-apartment"){
                                $roomType = "Single 2 bedroom";
                                $roomName = "49-jorissen-single-room-2-bedroom-apartment";
                            }
                            if($room->slug == "49-jorissen-single-room-3-bedroom-apartment"){
                                $roomType = "Single 3 bedroom";
                                $roomName = "49-jorissen-single-room-3-bedroom-apartment";
                            }
                            if($room->slug == "49-jorissen-single-room-4-bedroom-apartment"){
                                $roomType = "Single 4 bedroom";
                                $roomName = "49-jorissen-single-room-4-bedroom-apartment";
                            }
                            if($room->slug == "49-jorissen-2-sharing-hotel"){
                                $roomType = "2 Sharing 2 Bedroom | 2 Sharing";
                                $roomName = "49-jorissen-2-sharing-hotel";
                            }
                        }

                        if($booking->property == "80 Jorissen"){
                            if($room->slug == "80-jorissen-single-room"){
                                $roomType = "Single";
                                $roomName = "80-jorissen-single-room";
                            }
                            if($room->slug == "80-jorissen-2-sharing"){
                                $roomType = "2 Sharing";
                                $roomName = "80-jorissen-2-sharing";
                            }
                        }

                        if($booking->property == "Braamlofts"){
                            if($room->slug == "braamlofts-single-room"){
                                $roomType = "Single";
                                $roomName = "braamlofts-single-room";
                            }
                            if($room->slug == "braamlofts-2-sharing"){
                                $roomType = "2 Sharing";
                                $roomName = "braamlofts-2-sharing";
                            }
                            if($room->slug == "braamlofts-3-sharing"){
                                $roomType = "3 Sharing";
                                $roomName = "braamlofts-3-sharing";
                            }
                        }

                        $appliesTo = $room->acf->booking_fee_applies_to;

                        if($room->slug == $roomName && $roomType == $booking->roomType){

                            if($room->acf->booking_fee_override !== ""){
                                $booking_data['room_rate'] = $room->acf->booking_fee_override;
                            }

                            if($booking->studentType == "NSFAS"){
                                if(!in_array("NSFAS Students", $appliesTo)){
                                    $booking_data['room_rate'] = "0";
                                }
                            }

                            if($booking->studentType == "Bursary"){
                                if(!in_array("Bursary Students", $appliesTo)){
                                    $booking_data['room_rate'] = "0";
                                }
                            }

                            if($booking->studentType == "Cash / Direct"){
                                if(!in_array("Cash Students", $appliesTo)){
                                    $booking_data['room_rate'] = "0";
                                }
                            }
                        }

                        
                    }
                }
            }

            Booking::where('userId', $booking->userId)->update($booking_data);
        
            ActionList::create([
                "userUid" => $booking->userId,
                "action" => "Booking Fee Updated (Bulk Update)",
                "actionUserUid" => Auth::user()->id,
                "actionUserDisplayName" => Auth::user()->userFirstName,
                "actionUserRole" => Auth::user()->userRole,
            ]);
        }
    }

    public function update_processing_status(){
        $userList = User::get();

        foreach($userList as $user){

            $overall = "Incomplete";

            if($user->bookingStatus == "Approved" || $user->applicationStatus == "Approved Confirmed"){
                $overall = "Approved";
            }
            
            if($user->bookingStatus == "Empty" || $user->bookingStatus == "Rejected" || $user->applicationStatus == "Empty" || $user->applicationStatus == "Rejected"){
                $overall = "Incomplete";
            }

            if($user->bookingStatus == "Action" || $user->applicationStatus == "Action" || $user->applicationStatus == "Approved"){
                $overall = "Pending";
            }

            User::where('id', $user->id)->update(['overall_status' => $overall]);
        }
    }

    public function update_lease_dates(Request $request){
        $allUsers = User::where('overall_status', '!=', 'Cancelled')->get();

        // $lookup = Lookups::where('group_id', '4')->first();

        foreach($allUsers as $user){

            if($user->bookingStatus !== "Approved Confirmed" && $user->applicationStatus !== "Approved Confirmed"){

                if($user->bookingStatus !== "Rejected Confirmed" && $user->applicationStatus !== "Rejected Confirmed"){
                    
                    $booking = Booking::where('id', $user->bookingRef)->first();

                    if($booking && $booking->lease_unique !== 1){
                        $updatedDates['lease_from_date'] = $request->lease_from_date;
                        $updatedDates['lease_from_time'] = $request->lease_from_time;
                        $updatedDates['lease_to_date'] = $request->lease_to_date;
                        $updatedDates['lease_to_time'] = $request->lease_to_time;

                        Booking::where('id', $user->bookingRef)->update($updatedDates);

                        if($booking->lease_from_date !== $request->lease_from_date){
                            ActionList::create([
                                "userUid" => $user->id,
                                "action" => "Bulk Lease From Date Updated From ".$booking->lease_from_date." to ".$request->lease_from_date,
                                "actionUserUid" => Auth::user()->id,
                                "actionUserDisplayName" => Auth::user()->userFirstName,
                                "actionUserRole" => Auth::user()->userRole,
                            ]);
                        }

                        if($booking->lease_from_time !== $request->lease_from_time){
                            ActionList::create([
                                "userUid" => $user->id,
                                "action" => "Bulk Lease From Time Updated From ".$booking->lease_from_time." to ".$request->lease_from_time,
                                "actionUserUid" => Auth::user()->id,
                                "actionUserDisplayName" => Auth::user()->userFirstName,
                                "actionUserRole" => Auth::user()->userRole,
                            ]);
                        }

                        if($booking->lease_to_date !== $request->lease_to_date){
                            ActionList::create([
                                "userUid" => $user->id,
                                "action" => "Bulk Lease To Date Updated From ".$booking->lease_to_date." to ".$request->lease_to_date,
                                "actionUserUid" => Auth::user()->id,
                                "actionUserDisplayName" => Auth::user()->userFirstName,
                                "actionUserRole" => Auth::user()->userRole,
                            ]);
                        }

                        if($booking->lease_to_time !== $request->lease_to_time){
                            ActionList::create([
                                "userUid" => $user->id,
                                "action" => "Bulk Lease To Time Updated From ".$booking->lease_to_time." to ".$request->lease_to_time,
                                "actionUserUid" => Auth::user()->id,
                                "actionUserDisplayName" => Auth::user()->userFirstName,
                                "actionUserRole" => Auth::user()->userRole,
                            ]);
                        }
                    }
                }
            }
        }
    }

    public function add_comment(Request $request){
        
        Comments::create([
            'user_uid' => $request->userId,
            'comment' => $request->comment,
            'action_user_uid' => Auth::user()->id,
        ]);

        return 'true';
    }
    public function download_matched_unmatched(Request $request, $id){
        $allFromFile = ImportNSFASStudentAuditLog::where('importId', $id)->get();

        $users = [
			['<b>Student Number</b>', '<b>First Name</b>', '<b>Last Name</b>', '<b>ID Number</b>', '<b>Institution</b>', '<b>Status</b>'],
		];

        foreach($allFromFile as $user){
            if($user->status == "1"){
                $status = "Matched";
            }else{
                $status = "Unmatched";
            }

            $userData = [$user->student_number, $user->student_first_name, $user->student_last_name, $user->student_id_number, $user->institution, $status];

            array_push($users, $userData);
        }

        $xlsx = new \SimpleXLSXGen();
		$xlsx->addSheet( $users, 'Students');
		$xlsx->saveAs('NSFAS Approval Export.xlsx');
        // $xlsx->downloadAs('NSFAS Approval Export.xlsx');

		return "true";
        // $this->download_nsfas_report();
        // return $users;
    }

    public function download_nsfas_report(Request $request){
        $studentArr = User::where('studentType', 'NSFAS')->get();

        $users = [
			['<b>Student Number</b>', '<b>First Name</b>', '<b>Last Name</b>', '<b>ID Number</b>', '<b>Institution</b>', '<b>Status</b>', '<b>Approved By</b>'],
		];

        $lookups = Lookups::where('group_id', '1')->get();

        foreach($studentArr as $student){
            $file = UserFiles::where('userUid', $student->id)->where('fileId', 'bursaryApproval')->first();

            if($file){
                $status = $file->status;
                if($file->uploaded_by == "Import"){
                    $approved_by = "Import";
                }else{
                    $approved_by = $file->approved_by;
                }
            }else{
                $status = "";
                $approved_by = "";
            }

            foreach($lookups as $lookup){
                if($student->institutionStudy == $lookup->id){
                    $institution = $lookup->value;
                }
            }
            

            $studentData = [$student->studentNo, $student->userFirstName, $student->userLastname, $student->userId, $institution, $status, $approved_by];
            array_push($users, $studentData);
        }

        $xlsx = new \SimpleXLSXGen();
		$xlsx->addSheet( $users, 'Students');
		$xlsx->saveAs('NSFAS Approval Type Export.xlsx');

		// return "true";
        return $users;
    }

    public function update_nsfas(Request $request){
        $files = UserFiles::where('fileId', "bursaryApproval")->where('fileUrl', null)->get();

        foreach($files as $file){
            $uploaded = [];
            $user = User::where('id', $file->userUid)->first();
            $all = ['copyID', 'studentCard', 'proofRegistration', 'proof_of_payment'];

            if($user->applicationStatus == "Approved Confirmed"){
                array_push($all, "moveIn");
            }
            if($user->studentType == "Cash / Direct"){
                array_push($all, "bankStatement");
            }
            if($user->studentType !== "Cash / Direct"){
                array_push($all, "bursaryApproval");
            }

            $data = [];
            $phase1Arr = [];
            $phase2 = "Empty";
            $phase2Arr = [];

            $user = User::with('files')->where('id', $file->userUid)->first();

            foreach($user->files as $file){
                array_push($uploaded, $file->fileId);
                array_push($data, ["field" => $file->fileId, "status" => $file->status]);
            }

            $left = array_diff($all, $uploaded);

            foreach($left as $dataLeft){
                if($user->studentType == "NSFAS" || $user->studentType == "Bursary"){
                    if($dataLeft == "bankStatement"){
                        array_push($data, ["field" => $dataLeft, "status" => "na"]);
                    }else{
                        array_push($data, ["field" => $dataLeft, "status" => ""]);
                    }
                }else{
                    if($dataLeft == "bursaryApproval"){
                        array_push($data, ["field" => $dataLeft, "status" => "na"]);
                    }else{
                        array_push($data, ["field" => $dataLeft, "status" => ""]);
                    }
                }
            }

            foreach($data as $item){
                if($item['field'] == "proof_of_payment" || $item['field'] == "copyID"){

                    if($item['status'] == "Approved"){
                        array_push($phase1Arr, 2);
                    }

                    if($item['status'] == "Uploaded"){
                        array_push($phase1Arr, 1);
                    }

                    if($item['status'] == "Rejected" || $item['status'] == ""){
                        array_push($phase1Arr, 0);
                    }

                }

                if($item['field'] == "studentCard" || $item['field'] == "proofRegistration" || $item['field'] == "bursaryApproval" || $item['field'] == "bankStatement"){
                    
                    if($phase2 == "Empty" && $item['status'] == "Uploaded"){
                        $phase2 = "Action";
                    }else if($phase2 == "Empty" && $item['status'] == "Approved"){
                        $phase2 = "Approved";
                    }else if(($phase2 == "Empty" && $item['status'] == "Rejected") || ($phase2 == "Empty" && $item['status'] == "")){
                        $phase2 = "Rejected";
                    }

                    if($phase2 == "Action" && $item['status'] == "Uploaded"){
                        $phase2 = "Action";
                    }else if($phase2 == "Action" && $item['status'] == "Approved"){
                        $phase2 = "Action";
                    }else if($phase2 == "Action" && $item['status'] == "Rejected"){
                        $phase2 = "Action";
                    }

                    if($phase2 == "Approved" && $item['status'] == "Uploaded"){
                        $phase2 = "Action";
                    }else if($phase2 == "Approved" && $item['status'] == "Approved"){
                        $phase2 = "Approved";
                    }else if(($phase2 == "Approved" && $item['status'] == "Rejected") || ($phase2 == "Approved" && $item['status'] == "")){
                        $phase2 = "Rejected";
                    }

                    if($phase2 == "Rejected" && $item['status'] == "Uploaded"){
                        $phase2 = "Action";
                    }else if($phase2 == "Rejected" && $item['status'] == "Approved"){
                        $phase2 = "Rejected";
                    }else if($phase2 == "Rejected" && $item['status'] == "Rejected"){
                        $phase2 = "Rejected";
                    }

                }

                //For lease mark application as completed
                if ($item['field'] == "moveIn") {
                    $phase2 = "Approved Confirmed";
                }

            }

            $booking = "Rejected";

            if (!in_array(0, $phase1Arr)) {
                $booking =  "Approved";
            }

            if (in_array(1, $phase1Arr)) {
                $booking = "Action";
            }


            $overall = "Incomplete";

            if($booking == "Approved Confirmed" || $phase2 == "Approved Confirmed"){
                $overall = "Approved";
            }

            if($booking == "Rejected" || $booking == "Empty" || $phase2 == "Rejected" || $phase2 == "Empty"){
                $overall = "Incomplete";
            }

            if($booking == "Action" || $phase2 == "Action"|| $phase2 == "Approved"){
                $overall = "Pending";
            }

            User::where('id', $file->userUid)->update(["bookingStatus" => $booking, "applicationStatus" => $phase2, 'overall_status' => $overall]);
        }

        return 'true';     

    }

    

    
}

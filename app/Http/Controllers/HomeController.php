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
        
        if(Auth::user()->userRole == "1" || Auth::user()->userRole == "2" || Auth::user()->userRole == "3" ){

            $allUsersData = User::with('files')->where('userRole', '0')->get();
            $userData = [];

            foreach($allUsersData as $user){

                $uploaded = [];
                $all = [0 => 'moveIn', 1 => 'proof_of_payment', 2 => 'copyID', 3 => 'studentCard', 4 => 'proofRegistration', 5 => 'bursaryApproval', 6 => "bankStatement" ];

                $data = [];
                $counter = 0;

                foreach($user->files as $file){
                    array_push($uploaded, $file->fileId);
                    array_push($data, ["field" => $file->fileId, "status" => $file->status]);
                }

                $left = array_diff($all, $uploaded);

                foreach($left as $dataLeft){

                    if($dataLeft == "moveIn"){
                        $booking = Booking::where('userId', $user->id)->first();
                        if($booking->lease_from_date){
                            array_push($data, ["field" => $dataLeft, "status" => "Rejected"]);
                        }else{
                            array_push($data, ["field" => $dataLeft, "status" => "na"]);
                        }
                    }else{

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

                }

                foreach($data as $item){

                    if($item['status'] == "Uploaded"){
                        $counter++;
                    }
  
                    if($item['status'] == "" || $item['status'] == "Rejected"){
                        $counter--;
                    }

                    if($item['status'] == "Approved"){
                        $counter--;
                        $counter--;
                    }

                }   
                
                $roomBooking = Booking::where('userId', $user->id)->first();

                array_push($userData, ["id" => $user->id, "bookingRef" => $user->bookingRef, "userFirstName" => $user->userFirstName, "userLastname" => $user->userLastname, "userId" => $user->userId, "roomBlock" => $user->roomBlock, "studentType" => $user->studentType, "bookingStatus" => $user->bookingStatus, "applicationStatus" => $user->applicationStatus, "files" => $data, "counter"=>$counter, "property"=> $roomBooking->property, "roomType"=> $roomBooking->roomType, "created_at"=>$user->created_at, "bed_code"=>$roomBooking->bed_code]);
            }

            usort($userData, function($a, $b) {
                return $b['counter'] - $a['counter'];
            });

            return view('admin.applications', ["headerTitle" => "Applications", "users" => $userData, "title" => "Applications"]);
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

            if($booking->lease_from_date !== null && $booking->lease_from_time !== null && $booking->lease_to_date !== null && $booking->lease_to_time !== null){
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
                    $file->status = "";
                }
                if($file->status == "Approved"){
                    $file->status = "XApproved";
                }
                array_push($data, ["field" => $file->fileId, "status" => $file->status, "url" => $file->fileUrl, "uploaded_by" => $file->uploaded_by, "created_at" => $file->created_at, "id" => $file->id]);
            }

            $left = array_diff($all, $uploaded);

            foreach($left as $dataLeft){
                array_push($data, ["field" => $dataLeft, "status" => ""]);
            }

            
            usort($data, function($a, $b) {
                return $a['status'] <=> $b['status'];
            });

            // dd($data);

            array_push($userData, ["files" => $data, "userInfo" => $user]);

            $actions = ActionList::where('userUid', $userId)->orderBy('created_at', 'DESC')->get();

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

            $booking = Booking::where('userId', $userId)->first();
            $residence = Residence::where('residence', $booking->residence)->first();

            


            return view('admin.app_profile', ["headerTitle" => $name, "appProfile" => "true", "user" => $userData, "actions" => $actions, "completed" => $completed, "lookups" => $lookup, "title" => "Student Profile", "booking" => $booking, "residence" => $residence]);
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

            }

            $booking = "Rejected";

            if (!in_array(0, $phase1Arr)) {
                $booking =  "Approved";
            }

            if (in_array(1, $phase1Arr)) {
                $booking = "Action";
            }

            User::where('id', $userFile->userUid)->update(["bookingStatus" => $booking, "applicationStatus" => $phase2]);

            return $userFile;


        }
    }

    public function action_list(Request $request, $userId, $filter){

        if($filter == "all"){
            $list = ActionList::where('userUid', $userId)->orderBy('created_at', 'DESC')->get();
        }elseif($filter == "admin"){
            $list = ActionList::where('userUid', $userId)->where('actionUserRole', '1')->orderBy('created_at', 'DESC')->get();
        }elseif($filter == "applicant"){
            $list = ActionList::where('userUid', $userId)->where('actionUserRole', '0')->orderBy('created_at', 'DESC')->get();
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

        User::where('id', $userId)->update(['applicationStatus' => "Approved Confirmed"]);

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

        User::where('id', $userId)->update(['bookingStatus' => "Rejected Confirmed"]);

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

        return view('admin.user_management', ["headerTitle" => "User management", "systemUsers" => $systemUsers, "students" => $students, "title" => "User Management"]);
    }

    public function user_edit(Request $request, $userId){
        
        $user = User::where('id', $userId)->first();
        

        return view('admin.userEdit', ["headerTitle" => "User Edit", "user" => $user, "title" => "Edit User"]);
    }

    public function edit_admin(Request $request){
        

        User::where('id', $request->userUid)->update([
            "userFirstName" => $request->firstName,
            "userLastname" => $request->lastName,
            "userTitle" => $request->title,
            "userDob" => $request->dob,
            "userCell" => $request->cell_no,
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
        ]);

        return redirect()->back();
    }

    public function student_edit(Request $request, $studentId){
        $user = User::where('id', $studentId)->first();
        $booking = Booking::where('userId', $studentId)->first();
        $lookup = Lookups::get();

        return view('admin.studentEdit', ["headerTitle" => "Student Edit", "user" => $user, "lookups" => $lookup, "booking" => $booking, "title" => "Edit Student"]);
    }

    public function edit_student(Request $request){

        $oldData = User::where("id", $request->userUid)->first();
        $newData = ['userFirstName|First Name' => $request->firstName,'userLastname|Last Name' => $request->lastName,'userTitle|Title' => $request->title,'userDob|Date of Birth' => $request->dob,'userCell|Cellphone Number' => $request->cell_no,'userEmail|Email' => $request->email,'userId|I.D Number' => $request->idNumber,'userNationality|Nationality' => $request->nationality,'userPassport|Passport Number' => $request->passportNo,'institutionStudy|Institution of Study' => $request->institStudy,'studentNo|Student Number' => $request->studNumber,'studentType|Student Type' => $request->studentType,'faculty|Faculty' => $request->faculty,'year|Year' => "2022",'sponFirstName|Parent / Sponser First Name' => $request->pFirstName,'sponLastName| Parent / Sponser Last Name' => $request->pLastName,'sponCell|Parent / Sponser Cellphone Number' => $request->pCell_no,'sponEmail|Parent / Sponser Email' => $request->pEmail,'sponId|Parent / Sponser I.D Number' => $request->pIdNumber,'burCompanyName|Burser Company Name' => $request->bCompanyName,'burRegNo|Burser Registration Number' => $request->bRegistrationNo,'burContactPerson|Burser Contact Person' => $request->bContactPerson,'burContactNo|Burser Contact Number' => $request->bContactNumber,'burEmail|Burser Email' => $request->bEmail,'kinFirstName|Next of Kin First Name' => $request->kFirstName,'kinLastName|Next of Kin Last Name' => $request->kLastName,'kinTitle|Next of Kin Title' => $request->kTitle,'kinId|Next of Kin I.D Number' => $request->kIdNumber,'kinAddressStr|Next of Kin Street Number' => $request->kAddress1,'kinAddressSuburb|Next of Kin Suburb' => $request->kAddress2,'kinAddressCity|Next of Kin City' => $request->kAddressCity,'kinAddressPost|Next of Kin Postal Code' => $request->kAddressPostal,'kinRelationship|Next of Kin Relationship' => $request->kRelationship,'kinTelNo|Next of Kin Telephone Number' => $request->kTeleNo,'kinCellNo|Next of Kin Cellphone Number' => $request->kCellNo,'kinWorkNo|Next of Kin Work Number' => $request->kWorkNo,'kinEmail|Next of Kin Email' => $request->kEmail,'kinNationality|Next of Kin Nationality' => $request->kinNationality,'kinPassportNo|Next of Kin Passport Number' => $request->kinPassportNo,'pNationality|Parent / Sponser Nationality' => $request->pNationality,'pPassportNo|Parent / Sponser Passport Number' => $request->pPassportNo,'pAddress1|Parent / Sponser Street Number' => $request->pAddress1,'pAddress2|Parent / Sponser Suburb' => $request->pAddress2,'pAddressCity|Parent / Sponser City' => $request->pAddressCity,'pAddressPostal|Parent / Sponser Postal Code' => $request->pAddressPostal,'userAddress1|Street Number' => $request->userAddress1,'userAddress2|Suburb' => $request->userAddress2,'userAddressCity|City' => $request->userAddressCity,'userAddressPostal|PostalCode' => $request->userAddressPostal, 'bAddress1|Burser Street Number' => $request->bAddress1,'bAddress2|Burser Suburb' => $request->bAddress2,'bAddressCity|Burser City' => $request->bAddressCity,'bAddressPostal|Burser Postal Code' => $request->bAddressPostal,'bPayment|Burser Payment' => $request->bPayment,'bint|Burser Intermediary' => $request->bint,'bIntName|Burser Intermediary Name' => $request->bIntName,];

        foreach($newData as $key => $new){
            
            $info = explode("|", $key);
            $key = $info[0];
            $changedField = $info[1];

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

                }

                User::where('id', $userUid)->update(["bookingStatus" => $phase1, "applicationStatus" => $phase2]);

            }
        }


        return ["done", $fileUrlBack, $file];
    }

    public function lookups(Request $request){

        $lookups = Lookups::get();

        return view('admin.lookups', ["headerTitle" => "Lookups", "lookups" => $lookups, "title" => "Lookups"]);
    }

    public function add_lookups(Request $request){

        return view('admin.lookups_new', ["headerTitle" => "Add Lookup", "title" => "Add Lookup"]);
    }

    public function new_lookups(Request $request){

        Lookups::create([
            "group_id" => $request->group_id,
            "lookup_name" => $request->value,
        ]);

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
            ];
        }else{
            $data = [
                "group_id" => $request->group_id,
                "lookup_name" => $request->value,
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
            'studentType' => $request->studentType,
            'bookingStatus' => "Empty",
            'applicationStatus' => "Empty",
            'yearOfStudy' => $request->yearOfStudy,
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
        ]);

        $booking = Booking::create(["userId" => $user->id, "property" => $request->property, "roomType" => $request->roomType]);

        User::where('id', $user->id)->update(["bookingRef" => $booking->id]);

        ActionList::create([
            "userUid" => $user->id,
            "action" => "Registered ".$request->firstName." as a new user",
            "actionUserUid" => Auth::user()->id,
            "actionUserDisplayName" => Auth::user()->userFirstName,
            "actionUserRole" => Auth::user()->userRole,
        ]);

        // return redirect()->route('users');
        return redirect('/application-profile/'.$user->id);
    }

    public function new_admin(Request $request){

        return view('admin.userAdd', ["headerTitle" => "Add New System User", "title" => "Add User"]);
    }

    public function add_admin(Request $request){
        User::create([
            "name" => $request->firstName,
            "userFirstName" => $request->firstName,
            "userLastname" => $request->lastName,
            "userTitle" => $request->title,
            "userDob" => $request->dob,
            "userCell" => $request->cell_no,
            "userEmail" => $request->email,
            "email" => $request->email,
            "password" => Hash::make($request->password),
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
        ]);

        return redirect()->route('users');
    }

    public function delete_lookup(Request $request){

        Lookups::where('id', $request->lookupId)->delete();

        return 'true';
    }

    public function app_filter(Request $request){
        
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

        $userData = [];

        foreach($searched_students as $student){
            
            $user = User::with('files')->where('id', $student->userId)->first();            


                $uploaded = [];
                // $all = [0 => 'moveIn', 1 => 'proof_of_payment', 2 => 'copyID', 3 => 'studentCard', 4 => 'proofRegistration', 5 => 'bursaryApproval', 6 => "bankStatement" ];
                $all = ['copyID', 'studentCard', 'proofRegistration', 'proof_of_payment', 'moveIn'];

                if($user->studentType == "Cash / Direct"){
                    array_push($all, "bankStatement");
                }
                if($user->studentType !== "Cash / Direct"){
                    array_push($all, "bursaryApproval");
                }
                $data = [];
                $counter = 0;

                if(!empty($user->files)){
                    foreach($user->files as $file){
                        array_push($uploaded, $file->fileId);
                        array_push($data, ["field" => $file->fileId, "status" => $file->status]);
                    }
                }

                

                $left = array_diff($all, $uploaded);

                foreach($left as $dataLeft){
                    if($dataLeft == "moveIn"){
                        $booking = Booking::where('userId', $user->id)->first();
                        if($booking->lease_from_date){
                            array_push($data, ["field" => $dataLeft, "status" => "Uploaded"]);
                        }else{
                            array_push($data, ["field" => $dataLeft, "status" => "Rejected"]);
                        }
                    }else{
                        if(!empty($user->studentType) && $user->studentType == "NSFAS" || !empty($user->studentType) && $user->studentType == "Bursary"){
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
                }

                foreach($data as $item){
  
                    if($item['status'] == "Uploaded"){
                        $counter++;
                    }
  
                    if($item['status'] == "" || $item['status'] == "Rejected"){
                        $counter--;
                    }

                    if($item['status'] == "Approved"){
                        $counter--;
                        $counter--;
                    }

                }   

                $roomBooking = Booking::where('userId', $user->id)->first();
                
                if(!empty($user)){

                    array_push($userData, ["id" => $user->id, "bookingRef" => $user->bookingRef, "userFirstName" => $user->userFirstName, "userLastname" => $user->userLastname, "userId" => $user->userId, "roomBlock" => $user->roomBlock, "studentType" => $user->studentType, "bookingStatus" => $user->bookingStatus, "applicationStatus" => $user->applicationStatus, "files" => $data, "counter"=>$counter, "property"=> $roomBooking->property, "roomType"=> $roomBooking->roomType, "created_at"=>$user->created_at, "bed_code" => $roomBooking->bed_code]);
                }

            

            }
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
			['<b>First Name</b>', '<b>Last Name</b>', '<b>Title</b>', '<b>DOB</b>', '<b>Cell</b>', '<b>Email</b>', '<b>Nationality</b>', '<b>I.D Number</b>', '<b>Passport</b>',  '<b>Student Type</b>', '<b>Year</b>', '<b>Institution of Study</b>', '<b>Student Number</b>', '<b>Year of Study</b>', '<b>Faculty</b>', '<b>Residence</b>', '<b>Room Type</b>', '<b>Burser Company Name</b>', '<b>Burser Registeration Number</b>', '<b>Burser Contact Person</b>', '<b>Burser Contact Number</b>', '<b>Burser Email</b>', '<b>Parent First Name</b>', '<b>Parent Last Name</b>', '<b>Parent Cellphone Number</b>', '<b>Parent Email</b>', '<b>Parent Nationality</b>', '<b>Parent I.D Number</b>', '<b>Parent Passport</b>', '<b>Parent Street No</b>', '<b>Parent Suburb</b>', '<b>Parent City</b>', '<b>Parent Postal Code</b>', '<b>Next of Kin First Name</b>', '<b>Next of Kin Last Name</b>', '<b>Next of Kin Title</b>', '<b>Next of Kin Relationship</b>', '<b>Next of Kin Nationality</b>', '<b>Next of Kin I.D Number</b>', '<b>Next of Kin Passport</b>', '<b>Next of Kin Cellphone Number</b>', '<b>Next of Kin Email</b>', '<b>Next of Kin Work Number</b>', '<b>Next of Kin Telephone Number</b>', '<b>Next of Kin Street No</b>', '<b>Next of Kin Suburb</b>', '<b>Next of Kin City</b>', '<b>Next of Kin Postal Code</b>',],
		];

        foreach($searched_students as $studentData){
            $booking = Booking::where('userId', $studentData->userId)->first();
            $student = User::where('id', $studentData->userId)->first();

            if($student->studentType == "Cash / Direct"){
                $student->studentType = "Cash";
            }

            $lookupsInstit = Lookups::where('id', $student->institutionStudy)->first();
            $lookupsFaculty = Lookups::where('id', $student->faculty)->first();

            $studentData = [$student->userFirstName, $student->userLastname, $student->userTitle, $student->userDob, $student->userCell, $student->email, $student->userNationality, $student->userId, $student->userPassport, $student->studentType, $student->year, $lookupsInstit->value, $student->studentNo, $student->yearOfStudy, $lookupsFaculty->value, $booking->property, $booking->roomType, $student->burCompanyName, $student->burRegNo, $student->burContactPerson, $student->burContactNo, $student->burEmail, $student->sponFirstName, $student->sponLastName, $student->sponCell, $student->sponEmail, $student->pNationality, $student->sponId, $student->pPassportNo, $student->pAddress1, $student->pAddress2, $student->pAddressCity, $student->pAddressPostal, $student->kinFirstName, $student->kinLastName, $student->kinTitle, $student->kinRelationship, $student->kinNationality, $student->kinId, $student->kinPassportNo, $student->kinCellNo, $student->kinEmail, $student->kinWorkNo, $student->kinTelNo, $student->kinAddressStr, $student->kinAddressSuburb, $student->kinAddressCity, $student->kinAddressPost ];
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
        $roomType = $request->roomType;

        $oldData = Booking::where('userId', $bookingId)->first();
        $newData = ['property|Property' => $property, 'roomType|Room Type' => $roomType, "floor|Floor" => $request->floor, "unit|Unit" => $request->unit, "room|Room" => $request->room, "bed|Bed" => $request->bed, "bed_code|Bed Code"=> $request->bed_code];

        $propertyOld = Property::where('user_id_room', $bookingId)->update(["user_id_room" => null, "room_available" => null]);

        Property::where('residence', $property)->where('room_type', $roomType)->where("floor", $request->floor)->where('unit', $request->unit)->where('room', $request->room)->where('bed', $request->bed)->where('bed_code', $request->bed_code)->update(["room_available" => "1", "user_id_room"=>$bookingId]);

        $propertyData = Property::where('residence', $property)->where('room_type', $roomType)->where("floor", $request->floor)->where('unit', $request->unit)->where('room', $request->room)->where('bed', $request->bed)->where('bed_code', $request->bed_code)->first();
        


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

        Booking::where('userId', $bookingId)->update(["property" => $property, "roomType" => $roomType, "floor" => $request->floor, "unit" => $request->unit, "room" => $request->room, "bed" => $request->bed, "bed_code"=> $request->bed_code, "property_data_id" => $propertyData->id]);

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
            // $propertyInfo = Property::where('residence', $request->property)->where('room_type', $request->roomType)->get();
        }else{
            $selected = [];
            
            
        }

        if(!empty($request->floor) && $request->floor !== ""){
            if(!empty($request->unit) && $request->unit !== ""){
                if(!empty($request->room) && $request->room !== ""){
                    if(!empty($request->bed) && $request->bed !== ""){
                        $propertyInfo = Property::where('residence', $request->property)->where('room_type', $request->roomType)->where('floor', $request->floor)->where('unit', $request->unit)->where('room', $request->room)->where('bed', $request->bed)->get();
                    }else{
                        $propertyInfo = Property::where('residence', $request->property)->where('room_type', $request->roomType)->where('floor', $request->floor)->where('unit', $request->unit)->where('room', $request->room)->get();
                    }
                }else{
                    $propertyInfo = Property::where('residence', $request->property)->where('room_type', $request->roomType)->where('floor', $request->floor)->where('unit', $request->unit)->get();
                }
            }else{
                $propertyInfo = Property::where('residence', $request->property)->where('room_type', $request->roomType)->where('floor', $request->floor)->get();
            }
        }else{
            $propertyInfo = Property::where('residence', $request->property)->where('room_type', $request->roomType)->get();
        }

        return ["true", $selected, $propertyInfo];
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
        ];

        if($propertyId == "0"){
            Residence::create($data);
        }else{
            Residence::where('id', $propertyId)->update($data);
        }

        return redirect("/properties-info");
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
        }else if($request->FileNameForApproval == "bankStatement"){
            $name = 'BANK STATEMENT';
            $field = "bank_approved";
        }

        $dataToUpdate = [
            $field => $request->type,
        ];
        
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

    

    
}

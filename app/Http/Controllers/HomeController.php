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

            $uploaded = [];
            $all = ['copyID', 'studentCard', 'bankStatement', 'proofRegistration', 'proof_of_payment', 'moveIn'];

            foreach($allFiles as $file){
                array_push($uploaded, $file->fileId);
            }

            $outstanding = array_diff($all, $uploaded);

            return view('user.dashboard', ["headerTitle" => "Your Dashboard", "approvedFiles" => $allApprovedFiles, "pendingFiles" => $allPendingFiles, "rejectedFiles" => $allRejectedFiles, "outstanding" => $outstanding]);
        }else{

            return view('admin.dashboard', ["headerTitle" => "Admin Dashboard"]);
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
                    $typeName = "Proof of Payment";
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
                $all = [0 => 'moveIn', 1 => 'proof_of_payment', 2 => 'copyID', 3 => 'studentCard', 4 => 'proofRegistration', 5 => 'bursaryApproval', 6 => "bankStatement" ];
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
        
        if(Auth::user()->userRole == "1"){

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

                array_push($userData, ["id" => $user->id, "bookingRef" => $user->bookingRef, "userFirstName" => $user->userFirstName, "userLastname" => $user->userLastname, "userId" => $user->userId, "roomBlock" => $user->roomBlock, "studentType" => $user->studentType, "bookingStatus" => $user->bookingStatus, "applicationStatus" => $user->applicationStatus, "files" => $data, "counter"=>$counter, "property"=> $roomBooking->property, "roomType"=> $roomBooking->roomType, "created_at"=>$user->created_at]);
            }

            usort($userData, function($a, $b) {
                return $b['counter'] - $a['counter'];
            });

            return view('admin.applications', ["headerTitle" => "Applications", "users" => $userData]);
        }
    }

    public function application_profile(Request $request, $userId){

        if(Auth::user()->userRole == "1"){

            $user = User::where('id', $userId)->first();

            $name = $user['userFirstName']." ".$user['userLastname'];

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

            return view('admin.app_profile', ["headerTitle" => $name, "appProfile" => "true", "user" => $userData, "actions" => $actions, "completed" => $completed, "lookups" => $lookup]);
        }
    }

    public function action_file(Request $request){

        if(Auth::user()->userRole == "1"){
            
            $fileId = $request->file;
            $action = $request->type;
            $reason = $request->reason;

            UserFiles::where('id', $fileId)->update([
                'status' => $action,
                'rejectReason' => $reason,
                'approved_by' => $request->userName,
            ]);

            $userFile = UserFiles::where('id', $fileId)->first();

            if($userFile['fileId'] == "copyID"){
                $typeName = "Copy of ID Document";
            }elseif($userFile['fileId'] == "studentCard"){
                $typeName = "Copy of Student Card";
            }elseif($userFile['fileId'] == "bankStatement"){
                $typeName = "Bank Statements";
            }elseif($userFile['fileId'] == "proofRegistration"){
                $typeName = "Proof of Registration";
            }elseif($userFile['fileId'] == "proof_of_payment"){
                $typeName = "Proof of Payment";
            }elseif($userFile['fileId'] == "moveIn"){
                $typeName = "Lease";
            }

            if($action == "Rejected"){
                $text = "Rejected ".$typeName." with reason: ".$reason ;
            }else{
                $text = "Approved ".$typeName;
            }

            ActionList::create([
                "userUid" => $userFile->userUid,
                "action" => $text,
                "actionUserUid" => Auth::user()->id,
                "actionUserDisplayName" => $request->userName,
                "actionUserRole" => Auth::user()->userRole,
            ]);

            $uploaded = [];
            $all = [0 => 'moveIn', 1 => 'proof_of_payment', 2 => 'copyID', 3 => 'studentCard', 4 => 'proofRegistration', 5 => 'bursaryApproval', 6 => "bankStatement" ];
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
        $all = [0 => 'copyID', 1 => 'studentCard', 2 => 'bankStatement', 3 => 'proofRegistration', 4 => 'proof_of_payment', 5 => 'moveIn', 6 => "bursaryApproval" ];
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
            $all = ['copyID', 'studentCard', 'bankStatement', 'proofRegistration', 'proof_of_payment', 'moveIn'];

            foreach($allFiles as $file){
                array_push($uploaded, $file->fileId);
            }

            $outstanding = array_diff($all, $uploaded);

            return view('user.documents', ["headerTitle" => "My Documents", "approvedFiles" => $allApprovedFiles, "pendingFiles" => $allPendingFiles, "rejectedFiles" => $allRejectedFiles, "outstanding" => $outstanding]);
    }

    public function leases(Request $request){
        
        $leases = UserFiles::with('users')->where('fileId', 'moveIn')->get();

        return view('admin.leases', ["headerTitle" => "Leases", "leases" => $leases]);
    }

    public function mailing(Request $request){

        $thankYou = view('email.application_thank_you');

        $MailerList = MailerList::all();

        return view('admin.mailing', ["headerTitle" => "Mailers", "bookingRef" => "00000", "name" => "Johan", "roomNo" => "000", "MailerList" => $MailerList]);
    }

    public function invoices(Request $request){
        return view('admin.invoices', ["headerTitle" => "Invoices"]);
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

        return view('admin.user_management', ["headerTitle" => "User management", "systemUsers" => $systemUsers, "students" => $students]);
    }

    public function user_edit(Request $request, $userId){
        
        $user = User::where('id', $userId)->first();
        

        return view('admin.userEdit', ["headerTitle" => "User Edit", "user" => $user]);
    }

    public function edit_admin(Request $request){
        

        User::where('id', $request->userUid)->update([
            "userFirstName" => $request->firstName,
            "userLastname" => $request->lastName,
            "userTitle" => $request->title,
            "userDob" => $request->dob,
            "userCell" => $request->cell_no,
            "userEmail" => $request->email,
            "userRole" => $request->userRole,
        ]);

        return redirect()->back();
    }

    public function student_edit(Request $request, $studentId){
        $user = User::where('id', $studentId)->first();
        $lookup = Lookups::get();

        return view('admin.studentEdit', ["headerTitle" => "Student Edit", "user" => $user, "lookups" => $lookup]);
    }

    public function edit_student(Request $request){

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
            'institutionCampus' => $request->institCampus,
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
        ]);

        return redirect()->back();
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

        return view('admin.studentUpload', ["headerTitle" => "Student File Upload", "user" => $user, "approvedFiles" => $allApprovedFiles, "pendingFiles" => $allPendingFiles, "rejectedFiles" => $allRejectedFiles, "outstanding" => $outstanding]);
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
                    $typeName = "Proof of Payment";
                }elseif($fileType == "moveIn"){
                    $typeName = "Lease";
                }elseif($fileType == "bursaryApproval"){
                    $typeName = "Bursary Approval";
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
                $all = [0 => 'moveIn', 1 => 'proof_of_payment', 2 => 'copyID', 3 => 'studentCard', 4 => 'proofRegistration', 5 => 'bursaryApproval', 6 => "bankStatement" ];
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


        return ["done", $fileUrlBack];
    }

    public function lookups(Request $request){

        $lookups = Lookups::get();

        return view('admin.lookups', ["headerTitle" => "Lookups", "lookups" => $lookups]);
    }

    public function add_lookups(Request $request){

        return view('admin.lookups_new', ["headerTitle" => "Add Lookup"]);
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

        return view('admin.lookups_edit', ["headerTitle" => "Edit Lookup", "lookup" => $lookup]);
    }

    public function save_lookups(Request $request){

        Lookups::where('id', $request->lookupId)->update([
            "group_id" => $request->group_id,
            "lookup_name" => $request->value,
        ]);

        return redirect()->route('lookups');
    }

    public function new_student(Request $request){

        $lookup = Lookups::get();
        
        return view('admin.new_student', ["headerTitle" => "Add New Student", "lookups" => $lookup]);
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
            'institutionCampus' => $request->institCampus,
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
        ]);

        $booking = Booking::create(["userId" => $user->id, "property" => $request->property, "roomType" => $request->roomType]);

        User::where('id', $user->id)->update(["bookingRef" => $booking->id]);

        ActionList::create([
            "userUid" => $user->id,
            "action" => "Registered ".$request->firstName."as a new user",
            "actionUserUid" => Auth::user()->id,
            "actionUserDisplayName" => Auth::user()->userFirstName,
            "actionUserRole" => Auth::user()->userRole,
        ]);

        // return redirect()->route('users');
        return redirect('/application-profile/'.$user->id);
    }

    public function new_admin(Request $request){

        return view('admin.userAdd', ["headerTitle" => "Add New System User"]);
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
            "userRole" => $request->userRole,
        ]);

        return redirect()->route('users');
    }

    public function delete_lookup(Request $request){

        Lookups::where('id', $request->lookupId)->delete();

        return 'true';
    }

    public function app_filter(Request $request){
        
        $search_value = [];

        if($request->applicationStatus !== "" && !empty($request->applicationStatus)){
            
            if($request->applicationStatus == "Empty"){
                $bookingStatus = '(users.bookingStatus LIKE "%Empty%" OR users.bookingStatus LIKE "%Rejected%")';
                array_push($search_value, $bookingStatus);
            }else{
                $bookingStatus = 'users.bookingStatus LIKE "%'.$request->applicationStatus.'%"';
                array_push($search_value, $bookingStatus);
            }
        }

        if($request->property !== "" && !empty($request->property)){
            $property = 'bookings_data.property LIKE "%'.$request->property.'%"';
            array_push($search_value, $property);
        }

        if($request->room_type !== "" && !empty($request->room_type)){
            $room_type = 'bookings_data.roomType LIKE "%'.$request->room_type.'%"';
            array_push($search_value, $room_type);
        }

        if($request->applicant !== "" && !empty($request->applicant)){
            $applicant = 'users.userFirstName LIKE "%'.$request->applicant.'%"';
            array_push($search_value, $applicant);
        }

        if($request->student_type !== "" && !empty($request->student_type)){
            $student_type = 'users.studentType LIKE "%'.$request->student_type.'%"';
            array_push($search_value, $student_type);
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

        $searched_students = DB::select('SELECT * FROM users LEFT OUTER JOIN bookings_data on users.id = bookings_data.userId '.implode($query).' AND users.userRole = "0"');

        $userData = [];

        foreach($searched_students as $student){
            
            $user = User::with('files')->where('id', $student->userId)->first();            


                $uploaded = [];
                $all = [0 => 'moveIn', 1 => 'proof_of_payment', 2 => 'copyID', 3 => 'studentCard', 4 => 'proofRegistration', 5 => 'bursaryApproval', 6 => "bankStatement" ];
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

                foreach($data as $item){
  
                    if($item['status'] == "" || $item['status'] == "Rejected"){
                        $counter++;
                    }

                }   
                
                if(!empty($user)){

                    array_push($userData, ["id" => $user->id, "bookingRef" => $user->bookingRef, "userFirstName" => $user->userFirstName, "userLastname" => $user->userLastname, "userId" => $user->userId, "roomBlock" => $user->roomBlock, "studentType" => $user->studentType, "bookingStatus" => $user->bookingStatus, "applicationStatus" => $user->applicationStatus, "files" => $data, "counter"=>$counter]);
                }

            

            }
            usort($userData, function($a, $b) {
                return $b['counter'] - $a['counter'];
            });

        return json_encode($userData);
    }

    

    
}

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
use Session;
use App\Models\UserResetToken;
use App\Models\UserLeaseToken;
use App\Models\PropertyPermission;
use Jenssegers\Agent\Agent;
use Grosv\LaravelPasswordlessLogin\LoginUrl;
use App\Models\BookingPayment;
use App\Models\StudentsInfo;
use DateTime;
use App\Models\InstitutionProperties;
use PDF;
use App\Models\PropertyCount;

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

        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        if(Auth::user()->userRole == "0"){

            $appOnline = Lookups::where('group_id', '9')->first();

            if($appOnline->lookup_name == '1'){

                // $student = StudentsInfo::where('userUid', Auth::user()->id)->where('year', $this_year)->first();
                $studentInfo = StudentsInfo::where('userUid', Auth::user()->id)->orderBy('year', 'desc')->first();
    
                if($studentInfo->year == $this_year){
                    $student = StudentsInfo::where('userUid', Auth::user()->id)->where('year', $this_year)->first();
                }else{
                    $student = StudentsInfo::where('userUid', Auth::user()->id)->orderBy('year', 'desc')->first();
                    $returnInfo = $this->inActiveStudent();
    
                    $agent = new Agent();
                    return view('user.dashNoBooking', ["active" => 0, "headerTitle" => "Dashboard", "this_year" => $this_year, "bookingList" => $returnInfo['bookingList'], "buildingList" => $returnInfo['buildingList'], "roomList" => $returnInfo['roomList'], "filesList" => $returnInfo['filesList'], "user" => $student, "agent" => $agent]);
                }
    
                
                // if($student->email_verified != 1){
    
                //     return redirect('/verify-password');
    
                // }else 
                if($student->onboard_complete != '2'){
    
                    // User::where('id', Auth::user()->id)->update(['onboard_complete' => 1]);
                    StudentsInfo::where('userUid', Auth::user()->id)->where('year', $this_year)->update(['onboard_complete' => 1]);
    
                    // $currUser = User::where('id', Auth::user()->id)->first();
                    $currUser = StudentsInfo::where('userUid', Auth::user()->id)->where('year', $this_year)->first();
    
                    if($currUser->studentType == "NSFAS"){
                        $varToCheck = 'kinFirstName';
                    }elseif($currUser->studentType == "Bursary"){
                        $varToCheck = 'burCompanyName';
                    }else{
                        $varToCheck = 'sponFirstName';
                    }
    
                    if($currUser->$varToCheck == null){
                        return redirect('/step-2/online-application');
                    }else{
    
    
                        if(($currUser->studentType == "Bursary" || $currUser->studentType == "Cash / Direct") && $currUser->kinFirstName == null){
                            return redirect('/step-3/online-application');
                        }else{
                            $userFileProofOfPayment = UserFiles::where('userUid', Auth::user()->id)->where('fileId', 'proof_of_payment')->where('year', $this_year)->first();
        
                            if(!empty($userFileProofOfPayment)){
                                return redirect('/complete/online-application');
                            }else{
                                return redirect('/step-4/online-application');
                            }
                        }
                    }
    
                }else{
    
                    $this_year = Lookups::where('group_id', '4')->first();
                    $this_year = $this_year->lookup_name;
    
                    //See if there is an active booking
                    $bookingCurr = Booking::where('userId', Auth::user()->id)->where('year', $this_year)->first();
    
                    $returnInfo = [];
    
                    if(!empty($bookingCurr)){
                        $returnInfo = $this->studentActive();
                    }else{
                        $returnInfo = $this->inActiveStudent();
                    }
    
                    if($returnInfo['Status'] == '1'){
                        return view('user.dashboard', ["active" => 1, "headerTitle" => $returnInfo['headerTitle'], "booking" => $returnInfo['booking'], "allFiles" => $returnInfo['allFiles'], "bookHeader" => $returnInfo['bookHeader'], "appHeader" => $returnInfo['appHeader'], "propertyData" => $returnInfo['propertyData'], "roomData" => $returnInfo['roomData'], "bookingPaid" => $returnInfo['bookingPaid'], "user" => $student]);
                    }else{
                        $agent = new Agent();
                        return view('user.dashNoBooking', ["active" => 0, "headerTitle" => "Dashboard", "this_year" => $this_year, "bookingList" => $returnInfo['bookingList'], "buildingList" => $returnInfo['buildingList'], "roomList" => $returnInfo['roomList'], "filesList" => $returnInfo['filesList'], "user" => $student, "agent" => $agent]);
                    }
    
                }
            }else{
                return redirect('/logout');
            }


        }else{

            return view('admin.dashboard', ["headerTitle" => "Admin Dashboard", "title" => "Dashboard"]);
        }

    }

    public function inActiveStudent(){
        
        $buildingList = Residence::whereNull('status')->get();
        $roomList = RoomTypes::get();
        $filesList = UserFiles::where('userUid', Auth::user()->id)->get();

        $bookingsIds = [];

        $userInfo = StudentsInfo::where('userUid', Auth::user()->id)->orderBy('year', 'desc')->first();

        // foreach($userInfo as $info){
        //     if($info->rob_50_approve == '2'){
        //         array_push($bookingsIds, $info->bookingId);
        //     }
        // }

        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        $bookingList = Booking::where('userId', Auth::user()->id)->where('id', $userInfo->bookingId)->get();

        if(($userInfo->year < $this_year - 1) || $userInfo->rob_50_approve != '2'){
            $bookingList = [];
        }



        return ["Status" => 0, "bookingList" => $bookingList, "buildingList" => $buildingList, "roomList" => $roomList, "filesList" => $filesList];
    }

    public function studentActive(){
        $allFiles = [];
        $all = ['copyID', 'studentCard', 'proofRegistration', 'proof_of_payment'];

        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        $currUser = StudentsInfo::where('userUid', Auth::user()->id)->where('year', $this_year)->first();

        if($currUser->rob_60_cancelled == "0" && $currUser->rob_50_approve == '2'){
            array_push($all, "moveIn");
        }
        if($currUser->studentType == "Cash / Direct"){
            array_push($all, "bankStatement");
        }
        if($currUser->studentType !== "Cash / Direct"){
            array_push($all, "bursaryApproval");
        }

        $allSearchable = UserFiles::where("userUid", Auth::user()->id)->where('year', $this_year)->get();

        foreach($allSearchable as $search){
            if(!in_array($search->fileId, $all)){
                array_push($all, $search->fileId);
            }
        }

        $appHeader = 0;
        $bookHeader = 0;

        foreach($all as $file){
            $file_search = UserFiles::where("userUid", Auth::user()->id)->where('fileId', $file)->where('year', $this_year)->first();

            if($file || ($file_search && (($file_search->fileId !== "moveIn") || ($file_search->fileId == "moveIn" && $currUser->rob_50_approve == '2')))){
                //
                if(($currUser->studentType == "Cash / Direct" && $file !== 'bursaryApproval') || $currUser->studentType != "Cash / Direct" && $file !== 'bankStatement'){

                    if($file_search && !empty($file_search)){
                        $important = 0;

                        if($file_search->status == "Approved"){
                            $important = 1;
                        }else if($file_search->status == "Uploaded"){
                            $important = 2;
                        }else{
                            $important = 3;
                        }
                        $output = (object) array(
                            "fileId" => $file_search->fileId,
                            "status" => $file_search->status !== null ? $file_search->status : "None",
                            "fileUrl" => $file_search->fileUrl,
                            "important" => $important,
                        );
                        array_push($allFiles, $output);
                    }else{
                        $output = (object) array(
                            "fileId" => $file,
                            "status" => "None",
                            "fileUrl" => "",
                            "important" => 3,
                        );
                        array_push($allFiles, $output);
                    }

                    if($file !== 'copyID' && $file !== 'proof_of_payment'){
                        if($output->status == "Uploaded"){
                            if($appHeader < 1){
                                $appHeader = 1;
                            }
                        }

                        if($output->status == "Rejected" || $output->status == "None" || $output->status == null){
                            if($appHeader < 2){
                                $appHeader = 2;
                            }
                        }
                    }

                    if($appHeader == 0 && ($currUser->rob_50_approve == 1 || $currUser->rob_50_approve == 0)){
                        $appHeader = 1;
                    }

                    if($currUser->rob_50_approve == 2 && $file == 'moveIn'){
                        if($output->status == "Rejected" || $output->status == "None" || $output->status == null){
                            $appHeader = 2;
                        }

                        if($output->status == "Uploaded"){
                            $appHeader = 1;
                        }

                        if($output->status == "Approved"){
                            $appHeader = 0;
                        }
                    }

                    if($file == 'copyID' || $file == 'proof_of_payment'){
                        if($output->status == "Uploaded"){
                            if($bookHeader < 1){
                                $bookHeader = 1;
                            }
                        }

                        if($output->status == "Rejected" || $output->status == "None" || $output->status == null){
                            if($bookHeader < 2){
                                $bookHeader = 2;
                            }
                        }
                    }

                    if($currUser->rob_60_cancelled == '1'){
                        $appHeader = 3;
                        $bookHeader = 3;
                    }
                }
            }


        }
        

        $booking = Booking::where('userId', Auth::user()->id)->where('year', $this_year)->first();

        $propertyData = Residence::where('id', $booking->property)->first();
        $roomData = RoomTypes::where('id', $booking->roomType)->first();

        usort($allFiles, function($a, $b) {
            return $b->important - $a->important;
        });

        $bookingPaid = 1;
        $findBookingPaid = UserFiles::where("userUid", Auth::user()->id)->where('year', $this_year)->where('fileId', 'proof_of_payment')->first();

        if(empty($findBookingPaid) || $findBookingPaid->status == "Rejected"){
            $bookingPaid = 0;
        }

        // return view('user.dashboard', ["headerTitle" => "Dashboard", "booking" => $booking, "allFiles" => $allFiles, "bookHeader" => $bookHeader, "appHeader" => $appHeader, "propertyData" => $propertyData, "roomData" => $roomData, "bookingPaid" => $bookingPaid]);
        return ["Status" => 1, "headerTitle" => "Dashboard", "booking" => $booking, "allFiles" => $allFiles, "bookHeader" => $bookHeader, "appHeader" => $appHeader, "propertyData" => $propertyData, "roomData" => $roomData, "bookingPaid" => $bookingPaid];
    }

    public function room_booking(){

        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        $proof_of_payment = UserFiles::where('userUid', Auth::user()->id)->where('fileId', 'proof_of_payment')->where('year', $this_year)->first();
        $lease = UserFiles::where('userUid', Auth::user()->id)->where('fileId', 'moveIn')->where('year', $this_year)->first();
        $booking = Booking::where('userId', Auth::user()->id)->where('year', $this_year)->first();
        $lookup = Lookups::get();

        return view('user.room_booking', ["headerTitle" => "Room Booking", "proof_of_payment" => $proof_of_payment, "lease" => $lease, "lookups" => $lookup, "booking" => $booking]);

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

        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        if ($request->hasFile('file')) {
            
            if ($request->file('file')->isValid()) {

                $filenameWithExt  = $request->file('file')->getClientOriginalName();
                $fileName = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                $extension = $request->file('file')->extension();
                $fileNameToStore = $fileName. '_'. time().'.'.$extension;

                $request->file('file')->storeAs('public/files/'.$userUid.'/', $fileNameToStore);

                $fileUrl = 'files/'.$userUid.'/'. $fileNameToStore;

                $hasFile = UserFiles::where('userUid', $userUid)->where('fileId', $fileType)->where('year', $this_year)->first();

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
                        'year' => $this_year,
                    ]);

                    $text = "Uploaded ".$typeName." for review";
                }else{
                    UserFiles::where('userUid', $userUid)->where('fileId', $fileType)->where('year', $this_year)->update([
                        'fileId' => $fileType,
                        'userUid' => $userUid,
                        'fileName' => $fileNameToStore,
                        'fileUrl' => $fileUrl,
                        'status' => 'Uploaded',
                        'uploaded_by' => Auth::user()->userFirstName,
                        'year'=> $this_year,
                    ]);
                    $text = "Re-uploaded ".$typeName." for review";
                }
    
                ActionList::create([
                    "userUid" => $userUid,
                    "action" => $text,
                    "actionUserUid" => Auth::user()->id,
                    "actionUserDisplayName" => Auth::user()->userFirstName,
                    "actionUserRole" => Auth::user()->userRole,
                    "year" => $this_year,
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

            $allowedResidences = [];
            $allProp = Residence::whereNull('status')->get();
            $permissionProp = PropertyPermission::where('userUid', Auth::user()->id)->get();

            foreach($allProp as $prop){
                foreach($permissionProp as $permission){
                    if($permission->property == $prop->id){
                        array_push($allowedResidences, $prop);
                    }
                }
            }
            $agent = new Agent();
            return view('admin.applications', ["headerTitle" => "Applications", "title" => "Applications", "allowedResidences" => $allowedResidences, "agent" => $agent]);
        }
    }

    public function activeBooking($userId, $this_year){
        $user = User::where('id', $userId)->first();
        $studentInfo = StudentsInfo::where('userUid', $userId)->where('year', $this_year)->first();

        $name = $studentInfo->userFirstName." ".$studentInfo->userLastname;

        $files = UserFiles::where('userUid', $userId)->where('year', $this_year)->get();
        $booking = Booking::where('userId', $userId)->where('year', $this_year)->first();

        $userData = [];
        $uploaded = [];

        $all = ['copyID', 'studentCard', 'proofRegistration', 'proof_of_payment'];

        if($studentInfo->rob_50_approve == 2 && $booking->lease_from_date !== null && $booking->lease_from_time !== null && $booking->lease_to_date !== null && $booking->lease_to_time !== null){
            array_push($all, "moveIn");
        }
        if($studentInfo->studentType == "Cash / Direct"){
            array_push($all, "bankStatement");
        }
        if($studentInfo->studentType !== "Cash / Direct"){
            array_push($all, "bursaryApproval");
        }

        $approvedLease = UserFiles::where('userUid', $userId)->where('year', $this_year)->where('fileId', 'moveIn')->first();

        // if(!empty($approvedLease) && $approvedLease->status == "Approved"){
        //     array_push($all, "MDA");
        // }

        $data = [];

        foreach($files as $file){
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
            array_push($data, ["field" => $file->fileId, "status" => $file->status, "fileName" => $file->fileName, "url" => $file->fileUrl, "uploaded_by" => $file->uploaded_by, "created_at" => $file->created_at, "id" => $file->id]);
        }

        $left = array_diff($all, $uploaded);

        foreach($left as $dataLeft){
            array_push($data, ["field" => $dataLeft, "status" => "Rejected"]);
        }
        
        usort($data, function($a, $b) {
            return $a['status'] <=> $b['status'];
        });

        array_push($userData, ["files" => $data, "userInfo" => $studentInfo]);

        $actions = ActionList::with('users')->where('userUid', $userId)->where('year', $this_year)->orderBy('created_at', 'DESC')->get();

        $completed = "false";
        $completedDocs = [];

        foreach($data as $file){
            if($file['status'] == "XApproved"){
                array_push($completedDocs, $file['status']);
            }
        }

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

        foreach($files as $file){
            if($file->status == "XApproved" && $file->fileUrl !== null || $file->status == "Approved" && $file->fileUrl !== null){
                $files_to_download = 'true';
            }

            if($file->fileId == "proof_of_payment"){
                
                if($file->fileUrl !== null && $file->fileUrl !== '' && $file->status !== "Rejected" && $file->status !== "Uploaded" && $file->status !== "AUploaded"){
                    $booking_paid = "true";
                    
                }else{
                    if($file->status == "XApproved" || $file->status == "Approved"){

                        $actions = ActionList::where('userUid', $userId)->where('year', $this_year)->orderBy('id', 'DESC')->get();

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

        $leaseApproved = 'false';

        $bank = 0;
        $approval = 0;

        foreach($userData[0]['files'] as $userFile){
            if($userFile['field'] == "moveIn"){
                if($userFile['status'] == "XApproved"){
                    $leaseApproved = "true";
                }
            }

            if($userFile['field'] == "bankStatement"){
                $bank = 1;
            }

            if($userFile['field'] == "bursaryApproval"){
                $approval = 1;
            }
            
        }

        $i = 0;
        foreach($userData[0]['files'] as $userFile){
            
            if($userData[0]['userInfo']['studentType'] !== "Cash / Direct" && $bank == 1){
                if($userData[0]['files'][$i]['field'] == "bankStatement"){
                    unset($userData[0]['files'][$i]);
                }
            }

            if($userData[0]['userInfo']['studentType'] == "Cash / Direct" && $approval == 1){
                if($userData[0]['files'][$i]['field'] == "bursaryApproval"){
                    unset($userData[0]['files'][$i]);
                }
            }

            $i++;
        }

        $allowedResidences = [];
        $allProp = Residence::whereNull('status')->get();
        $permissionProp = PropertyPermission::where('userUid', Auth::user()->id)->get();

        foreach($allProp as $prop){
            foreach($permissionProp as $permission){
                if($permission->property == $prop->id){
                    array_push($allowedResidences, $prop->id);
                }
            }
        }

        $paymentBooking = BookingPayment::where('bookingId', $booking->id)->first();

        $buildingName = Residence::where('id', $booking->property)->pluck('residence')->first();
        $mailVerificationEmail = MailerList::where("mailer_name", "Password Validation From Admin")->first();

        $previousBookings = Booking::where('userId', $userId)->where('year', '!=', $this_year)->orderBy('year', 'desc')->get();

        if((($userData[0]['userInfo']['rob_60_cancelled'] == '0' || $userData[0]['userInfo']['rob_60_cancelled'] == null) && in_array($booking->property, $allowedResidences)) || $userData[0]['userInfo']['rob_60_cancelled'] == '1'){
            return ["Info" => 1, "headerTitle" => $name, "appProfile" => "true", "user" => $userData, "actions" => $actions, "completed" => $completed, "lookups" => $lookup, "title" => "Student Profile", "booking" => $booking, "residence" => $residence, "bookingPropData" => $bookingPropData, "files_to_download" => $files_to_download, "booking_paid" => $booking_paid, 'booking_waived' => $booking_waived, 'comments' => $comments, 'leaseApproved' => $leaseApproved, 'allowedResidences' => $allowedResidences, "mailVerificationEmail" => $mailVerificationEmail, "previousBookings" => $previousBookings, "paymentBooking" => $paymentBooking];
        }else{
            return ["Info" => 0, "headerTitle" => "Unable to View", "title" => "Unable to View", "buildingName" => $buildingName];
        }
    }

    public function oldBooking($userId){

        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;
        $user =  StudentsInfo::where('userUid', $userId)->orderBy('year', 'desc')->first();
        // $user = User::where('id', $userId)->first();

        $name = $user->userFirstName." ".$user->userLastname;

        $booking = Booking::where('userId', $userId)->orderBy('year', 'desc')->first();
        $files = UserFiles::where('userUid', $userId)->where('year', $booking->year)->get();

        $userData = [];
        $uploaded = [];

        $all = ['copyID', 'studentCard', 'proofRegistration', 'proof_of_payment', 'moveIn'];

        if($user->studentType == "Cash / Direct"){
            array_push($all, "bankStatement");
        }
        if($user->studentType !== "Cash / Direct"){
            array_push($all, "bursaryApproval");
        }

        $bookingList = Booking::where('userId', $userId)->orderBy('year', 'desc')->get();
        $buildingList = Residence::whereNull('status')->get();
        $roomList = RoomTypes::get();
        $filesList = UserFiles::where('userUid', $userId)->get();

        return ["Info" => 2, "headerTitle" => $name." - Expired Booking", "title" => "Expired Booking", "bookingList" => $bookingList, "buildingList" => $buildingList, "roomList" => $roomList, "filesList" => $filesList, "all" => $all, "user" => $user];
    }

    public function application_profile(Request $request, $userId){

        if(Auth::user()->userRole == "1" || Auth::user()->userRole == "2" || Auth::user()->userRole == "3"){

            //get current year
            $this_year = Lookups::where('group_id', '4')->first();
            $this_year = $this_year->lookup_name;

            //See if there is an active booking
            $bookingCurr = Booking::where('userId', $userId)->where('year', $this_year)->first();


            $returnInfo = [];

            if(!empty($bookingCurr)){
                $returnInfo = $this->activeBooking($userId, $this_year);
            }else{
                $returnInfo = $this->oldBooking($userId);
            }

            if($returnInfo['Info'] == '1'){
                return view('admin.app_profile', ["headerTitle" => $returnInfo['headerTitle'], "appProfile" => "true", "user" => $returnInfo['user'], "actions" => $returnInfo['actions'], "completed" => $returnInfo['completed'], "lookups" => $returnInfo['lookups'], "title" => "Student Profile", "booking" => $returnInfo['booking'], "residence" => $returnInfo['residence'], "bookingPropData" => $returnInfo['bookingPropData'], "files_to_download" => $returnInfo['files_to_download'], "booking_paid" => $returnInfo['booking_paid'], 'booking_waived' => $returnInfo['booking_waived'], 'comments' => $returnInfo['comments'], 'leaseApproved' => $returnInfo['leaseApproved'], 'allowedResidences' => $returnInfo['allowedResidences'], "mailVerificationEmail" => $returnInfo['mailVerificationEmail'], "previousBookings" => $returnInfo['previousBookings'], "paymentBooking" => $returnInfo['paymentBooking'], "this_year" => $this_year]);
            }elseif($returnInfo['Info'] == '0'){
                return view('errors.studentView', ["headerTitle" => $returnInfo['headerTitle'], "title" => $returnInfo['title'], "buildingName" => $returnInfo['buildingName']]);
            }elseif($returnInfo['Info'] == '2'){
                return view('admin.oldBookingNotification', ["headerTitle" => $returnInfo['headerTitle'], "title" => $returnInfo['title'], "this_year" => $this_year, "bookingList" => $returnInfo['bookingList'], "buildingList" => $returnInfo['buildingList'], "roomList" => $returnInfo['roomList'], "filesList" => $returnInfo['filesList'], "all" => $returnInfo['all'], "user" => $returnInfo['user']]);
            }

        }
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

    public function check_booking($userUid){

        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        $idCheck = UserFiles::where('userUid', $userUid)->where('year', $this_year)->where('fileId', 'copyID')->first();
        $popCheck = UserFiles::where('userUid', $userUid)->where('year', $this_year)->where('fileId', 'proof_of_payment')->first();
        // $user = User::where('id', $userUid)->first();
        $user = StudentsInfo::where('userUid', $userUid)->where('year', $this_year)->first();
        $booking = Booking::where('userid', $userUid)->where('year', $this_year)->first();

        $allCheck = UserFiles::where('userUid', $userUid)->where('year', $this_year)->get();

        if(!empty($idCheck) && !empty($popCheck)){
            if($idCheck->status == "Approved" && $popCheck->status == "Approved"){

                $all = ['studentCard', 'proofRegistration'];
                $outstanding = [];

                if($user->studentType == "Cash / Direct"){
                    array_push($all, "bankStatement");
                }
                if($user->studentType !== "Cash / Direct"){
                    array_push($all, "bursaryApproval");
                }

                $allSearchable = UserFiles::where("userUid", $user->userUid)->where('year', $this_year)->get();

                foreach($allSearchable as $search){
                    if(explode("_", $search->fileId)[0] == "other"){
                        array_push($all, $search->fileId);
                    }
                }
    
                foreach($all as $file){
                    $file_search = UserFiles::where("userUid", $user->userUid)->where('fileId', $file)->where('year', $this_year)->first();

                    if(($user->studentType == "Cash / Direct" && $file !== 'bursaryApproval') || $user->studentType != "Cash / Direct" && $file !== 'bankStatement'){

                        if($file == "studentCard"){
                            $fileName = "Student Card";
                        }elseif($file == "proofRegistration"){
                            $fileName = "Proof of Registration";
                        }elseif($file == "bankStatement"){
                            $fileName = "Bank Statements";
                        }elseif($file == "bursaryApproval"){
                            if($user->studentType != "NSFAS"){
                                $fileName = "Bursary Approval";
                            }else{
                                $fileName = "NSFAS Approval";
                            }
                        }elseif(explode("_", $file)[0] == "other"){
                            $fileName = strtoupper(str_replace("_", " ", str_replace("other", "other |", $file)));
                        }

                        if($file_search && !empty($file_search)){
                            if($file_search->status !== "Approved" && $file_search->status !== "Uploaded"){
                                array_push($outstanding, $fileName);
                            }
                        }else{
                            array_push($outstanding, $fileName);
                        }

                    }

                }

                $outstandingDocsText = "<ol>";

                foreach($outstanding as $out){
                    $outstandingDocsText .= "<li style='color: #fff; font-size: 21px; font-weight: 400; line-height: 30px;'>".$out."</li>";
                }

                $outstandingDocsText .= "</ol>";

                $genUser = User::where('id', $userUid)->first();
                $generator = new LoginUrl($genUser);
                $generator->setRedirectUrl('/dashboard');
                $url = $generator->generate();

                $message_replace['bookingRef'] = $booking->booking_code;
                $message_replace['name'] = $user->userFirstName;
                $message_replace['approvedDocs'] = "<ol><li style='color: #fff; font-size: 21px; font-weight: 400; line-height: 30px;'>Copy of ID Document</li><li style='color: #fff; font-size: 21px; font-weight: 400; line-height: 30px;'>Booking Fee Proof of Payment</li></ol>";
                $message_replace['missing'] = $outstandingDocsText;
                $message_replace['application_link'] = "<a href='".$url."' target='_blank'>";
                $message_replace['close_application_link']="</a>";

                $mailerForMessage = MailerList::where("mailer_name", "Booking Confirmation")->first();

                if($mailerForMessage->status == '1'){

                    $mailerMessage = $mailerForMessage->message;
    
                    $newMessage = str_replace("[application_number]", "#".$message_replace['bookingRef'], $mailerMessage);
                    $newMessage = str_replace("[name]", $message_replace['name'], $newMessage);
                    $newMessage = str_replace("[approved_document_list]", $message_replace['approvedDocs'], $newMessage);
                    $newMessage = str_replace("[outstanding_document_list]", $message_replace['missing'], $newMessage);
                    $newMessage = str_replace("[application_link]", $message_replace['application_link'], $newMessage);
                    $newMessage = str_replace("[close_application_link]", $message_replace['close_application_link'], $newMessage);
    
                    $data['name'] = $user->userFirstName;
                    $data['email'] = $genUser->email;
                    $data['messageForMail'] = $newMessage;
                    $data['subject'] = str_replace("[application_number]", $message_replace['bookingRef'], $mailerForMessage->subject);

                    $allowedMailsCC = Lookups::where('group_id', '11')->pluck('lookup_name')->first();
                    $data['cc'] = explode(";", $allowedMailsCC);

                    $allowedMails = Lookups::where('group_id', '10')->pluck('lookup_name')->first();

                    if(!empty($allowedMails)){
                        $mailArr = explode(";", $allowedMails);
    
                        if(in_array(explode("@", $data['email'])[1], $mailArr)){
                            $data['email'] = $genUser->email;
                        }else{
                            $data['email'] = "conurbandigital@gmail.com";
                        }
                    }else{
                        $data['email'] = $genUser->email;
                    }
    
                    // if(explode("@", $data['email'])[1] == "conurban.co.za"){
                    //     $data['email'] = $user->email;
                    // }else{
                    //     $data['email'] = "conurbandigital@gmail.com";
                    // }
    
                    Mail::send('email.green_back', $data, function($message) use ($data) {
                        // $message->to($data['email'], $data['name'])->subject
                        //     ('Campus Africa User Application');
                        $message->to($data['email'])->subject($data['subject']);
                        $message->bcc($data['cc']);
                        // $message->cc(['conurbandigital@gmail.com', 'roger@whitehart.co.za']);
                        $message->from('noreply@campusafrica.co.za','Campus Africa');
                    });
                }

            }
        }
    }

    public function rejectMail($typeName, $reason, $userId){
        
            //Send email notification about rejected document

            $this_year = Lookups::where('group_id', '4')->first();
            $this_year = $this_year->lookup_name;

            $booking = Booking::where('userid', $userId)->where('year', $this_year)->first();
            $user = User::where('id', $userId)->first();
            $userInfo = StudentsInfo::where('userUid', $userId)->where('year', $this_year)->first();

            $generator = new LoginUrl($user);
            $generator->setRedirectUrl('/dashboard');
            $url = $generator->generate();

            $message_replace['bookingRef'] = $booking->booking_code;
            $message_replace['name'] = $userInfo->userFirstName;
            $message_replace['document_name'] = $typeName;
            $message_replace['decline_reason'] = $reason;
            $message_replace['application_link'] = "<a href='".$url."' target='_blank'>";
            $message_replace['close_application_link']="</a>";

            $mailerForMessage = MailerList::where("mailer_name", "Document Declined")->first();

            if($mailerForMessage->status == '1'){

                $mailerMessage = $mailerForMessage->message;

                $newMessage = str_replace("[application_number]", "#".$message_replace['bookingRef'], $mailerMessage);
                $newMessage = str_replace("[name]", $message_replace['name'], $newMessage);
                $newMessage = str_replace("[document_name]", $message_replace['document_name'], $newMessage);
                $newMessage = str_replace("[decline_reason]", $message_replace['decline_reason'], $newMessage);
                $newMessage = str_replace("[application_link]", $message_replace['application_link'], $newMessage);
                $newMessage = str_replace("[close_application_link]", $message_replace['close_application_link'], $newMessage);

                $newSubject = str_replace("[application_number]", $message_replace['bookingRef'], $mailerForMessage->subject);
                $newSubject = str_replace("[document_name]", $message_replace['document_name'], $newSubject);

                $data['name'] = $userInfo->userFirstName;
                $data['email'] = $user->email;
                $data['messageForMail'] = $newMessage;
                $data['subject'] = $newSubject;

                
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

                Mail::send('email.red_back', $data, function($message) use ($data) {
                    $message->to($data['email'])->subject($data['subject']);
                    $message->bcc($data['cc']);
                    $message->from('noreply@campusafrica.co.za','Campus Africa');
                });
            }
        
    }

    public function action_file(Request $request){

        if(Auth::user()->userRole == "1" || Auth::user()->userRole == "2" || Auth::user()->userRole == "3"){

            $this_year = Lookups::where('group_id', '4')->first();
            $this_year = $this_year->lookup_name;
            
            $fileId = $request->file;
            $action = $request->type;
            $reason = $request->reason;

            UserFiles::where('id', $fileId)->update([
                'status' => $action,
                'rejectReason' => $reason,
                'approved_by' => $request->userName,
                'year' => $this_year,
            ]);

            $userFile = UserFiles::where('id', $fileId)->first();

            $user_status = $this->get_user_status($userFile['fileId'], "", StudentsInfo::where('userUid', $userFile->userUid)->where('year', $this_year)->first());

            $allOtherFiles = UserFiles::where('userUid', $userFile->userUid)->where('year', $this_year)->whereNotIn('fileId', ['proof_of_payment', 'copyID', 'studentCard', 'proofRegistration', 'bursaryApproval', 'bankStatement', 'moveIn'])->get();

            if($action == "Rejected"){

                $text = "Rejected ".$user_status['typeName']." with reason: ".$reason ;

                if (!empty($user_status['field_data']) && explode("_", $user_status['field_data'])[0] !== "other") {
                    // User::where('id', $userFile->userUid)->update([$user_status['field_data'] => "0"]);
                    StudentsInfo::where('userUid', $userFile->userUid)->where('year', $this_year)->update([$user_status['field_data'] => "0"]);
                }
                
                // User::where('id', $userFile->userUid)->update([$user_status['field_doc'] => "0"]);
                StudentsInfo::where('userUid', $userFile->userUid)->where('year', $this_year)->update([$user_status['field_doc'] => "0"]);

                if($user_status['field_doc'] == "rob_30_funds"){
                    // User::where('id', $userFile->userUid)->update(['parent_approved' => null, 'burser_approved' => null]);
                    StudentsInfo::where('userUid', $userFile->userUid)->where('year', $this_year)->update(['parent_approved' => null, 'burser_approved' => null]);
                }

                if(explode("_", $user_status['field_data'])[0] == "other" && count($allOtherFiles) > 1){
                    $highest = 2;

                    foreach($allOtherFiles as $otherFile){
                        if($otherFile->status == "Approved"){
                            $value = 2;
                        }else if($otherFile->status == "Uploaded"){
                            $value = 1;
                        }else{
                            $value = 0;
                        }

                        if($value < $highest){
                            $highest = $value;
                        }
                    }

                    // User::where('id', $userFile->userUid)->update([$user_status['field_doc'] => $highest]);
                    // StudentsInfo::where('userUid', $userFile->userUid)->where('year', $this_year)->update([$user_status['field_doc'] => $highest]);

                    $studentInfo  = StudentsInfo::where('useruid', $userFile->userUid)->where('year', $this_year)->first();
                    $payment = BookingPayment::where('bookingId', $studentInfo->bookingId)->first();

                    // User::where('id', $userUid)->update(['rob_45_other' => $highest]);

                    if($payment->rob_b_waived == '1' || $payment->rob_loan_waived == '1' || $payment->rob_deposit_waived == '1'){
                        StudentsInfo::where('userUid', $userFile->userUid)->where('year', $this_year)->update(['rob_45_other' => '1']);
                    }else{
                        StudentsInfo::where('userUid', $userFile->userUid)->where('year', $this_year)->update([$user_status['field_doc'] => $highest]);
                    }

                }

                $this->rejectMail($user_status['typeName'], $reason, $userFile->userUid);
                

            } else {

                $text = "Approved ".$user_status['typeName'];

                if(explode("_", $user_status['field_data'])[0] == "other" && count($allOtherFiles) > 1){
                    $highest = 2;

                    foreach($allOtherFiles as $otherFile){
                        if($otherFile->status == "Approved"){
                            $value = 2;
                        }else if($otherFile->status == "Uploaded"){
                            $value = 1;
                        }else{
                            $value = 0;
                        }

                        if($value < $highest){
                            $highest = $value;
                        }
                    }

                    // User::where('id', $userFile->userUid)->update([$user_status['field_doc'] => $highest]);
                    // StudentsInfo::where('userUid', $userFile->userUid)->where('year', $this_year)->update([$user_status['field_doc'] => $highest]);

                    $studentInfo  = StudentsInfo::where('useruid', $userFile->userUid)->where('year', $this_year)->first();
                    $payment = BookingPayment::where('bookingId', $studentInfo->bookingId)->first();

                    // User::where('id', $userUid)->update(['rob_45_other' => $highest]);

                    if($payment->rob_b_waived == '1' || $payment->rob_loan_waived == '1' || $payment->rob_deposit_waived == '1'){
                        StudentsInfo::where('userUid', $userFile->userUid)->where('year', $this_year)->update(['rob_45_other' => '1']);
                    }else{
                        StudentsInfo::where('userUid', $userFile->userUid)->where('year', $this_year)->update([$user_status['field_doc'] => $highest]);
                    }

                }else{
                    
                    // User::where('id', $userFile->userUid)->update([$user_status['field_doc'] => "2"]);
                    StudentsInfo::where('userUid', $userFile->userUid)->where('year', $this_year)->update([$user_status['field_doc'] => "2"]);
                }
                
            }

            if($userFile['fileId'] == "proof_of_payment" && $action == "Approved"){
                $userData = StudentsInfo::where('userUid', $userFile->userUid)->where('year', $this_year)->first();
                if($userData->rob_40_bed != 2){
                    StudentsInfo::where('userUid', $userFile->userUid)->where('year', $this_year)->update(["rob_40_bed" => 1]);
                }
            }

            if($userFile['fileId'] == "proof_of_payment" && $action == "Rejected"){
                $userData = StudentsInfo::where('userUid', $userFile->userUid)->where('year', $this_year)->first();
                if($userData->rob_40_bed != 2){
                    StudentsInfo::where('userUid', $userFile->userUid)->where('year', $this_year)->update(["rob_40_bed" => 0]);
                }
            }

            // if($userFile['fileId'] == "moveIn" && $action == "Rejected"){
            //     UserFiles::where('userUid', $userFile->userUid)->where('year', $this_year)->where('fileId', 'MDA')->delete();
            // }

            if($userFile['fileId'] == "moveIn" && $action == "Approved"){
                // User::where('id', $userFile->userUid)->update(['overall_status' => "Approved"]);
                StudentsInfo::where('userUid', $userFile->userUid)->where('year', $this_year)->update(['overall_status' => "Approved"]);
                
                // $userForLease = User::where('id', $userFile->userUid)->first();
                $userForLease = StudentsInfo::where('userUid', $userFile->userUid)->where('year', $this_year)->first();
                $booking = Booking::where('userId', $userFile->userUid)->where('year', $this_year)->first();

                $message_replace['booking_application'] = "#".$booking->booking_code;
                $message_replace['name'] = $userForLease->userFirstName;

                $mailerForMessage = MailerList::where("mailer_name", "Lease Approved")->first();

                $mailerMessage = $mailerForMessage->message;

                $newMessage = str_replace("[booking_application]", $message_replace['booking_application'], $mailerMessage);
                $newMessage = str_replace("[name]", $message_replace['name'], $newMessage);

                $fileForUser = UserFiles::where('userUid', $userFile->userUid)->where('year', $this_year)->where('fileId', 'moveIn')->first();

                $data['messageForMail'] = $newMessage;
                $data['email'] = $userForLease->userEmail;
                $data['name'] = $userForLease->userFirstName;
                // $data['file'] = "https://$_SERVER[HTTP_HOST]/storage/".$fileForUser->fileUrl;
                $data['subject'] = str_replace("[application_number]", $booking->booking_code, $mailerForMessage->subject);

                $allowedMailsCC = Lookups::where('group_id', '11')->pluck('lookup_name')->first();
                $data['cc'] = explode(";", $allowedMailsCC);

                $allowedMails = Lookups::where('group_id', '10')->pluck('lookup_name')->first();

                if(!empty($allowedMails)){

                    $mailArr = explode(";", $allowedMails);
    
                    if(in_array(explode("@", $data['email'])[1], $mailArr)){
                        $data['email'] = $userForLease->userEmail;
                    }else{
                        $data['email'] = "conurbandigital@gmail.com";
                    }
                }else{
                    $data['email'] = $userForLease->userEmail;
                }

                Mail::send('email.green_back', $data, function($message) use ($data) {
                    // $message->to($data['email'], $data['name'])->subject('Application Approved');
                    $message->to($data['email'])->subject($data['subject']);
                    $message->bcc($data['cc']);
                    $message->from('noreply@campusafrica.co.za','Campus Africa');
                    // $message->attach($data['file']);
                });
            }

            if($request->userName !== "" && !empty($request->userName)){
                $name = $request->userName;
            }else{
                $name = Auth::user()->userFirstName;
            }

            // $status_data = User::where('id', $userFile->userUid)->first();
            $status_data = StudentsInfo::where('userUid', $userFile->userUid)->where('year', $this_year)->first();

            if($status_data->rob_10_fee == 2 && $status_data->rob_15_id == 2 && $status_data->rob_20_card == 2 && $status_data->rob_25_reg == 2 && $status_data->rob_30_funds == 2 && $status_data->rob_35_info == 2 && $status_data->rob_40_bed == 2 && ($status_data->rob_45_other == 3 || $status_data->rob_45_other == 2) && $status_data->rob_50_approve == 0){
                // User::where('id', $userFile->userUid)->update(['rob_50_approve' => 1]);
                StudentsInfo::where('userUid', $userFile->userUid)->where('year', $this_year)->update(['rob_50_approve' => 1]);
            }

            ActionList::create([
                "userUid" => $userFile->userUid,
                "action" => $text,
                "actionUserUid" => Auth::user()->id,
                "actionUserDisplayName" => $name,
                "actionUserRole" => Auth::user()->userRole,
                "year" => $this_year,
            ]);

            $this->update_user_status($userFile->userUid);


            if($userFile->fileId == "copyID" || $userFile->fileId == "proof_of_payment"){
                $this->check_booking($userFile->userUid);
            }

            if($userFile['fileId'] != "moveIn" && $action != "Rejected"){

                $user = User::where('id', $userFile->userUid)->first();
                $studentInfoForMail = StudentsInfo::where('userUid',  $userFile->userUid)->where('year', $this_year)->first();
                $booking = Booking::where('id', $studentInfoForMail->bookingId)->first();

                $generator = new LoginUrl($user);
                $generator->setRedirectUrl('/dashboard');
                $url = $generator->generate();

                // $all = ['studentCard', 'proofRegistration', 'copyID', 'proof_of_payment'];
                // $outstanding = [];
                // $approved = [];

                // if($studentInfoForMail->studentType == "Cash / Direct"){
                //     array_push($all, "bankStatement");
                // }
                // if($studentInfoForMail->studentType !== "Cash / Direct"){
                //     array_push($all, "bursaryApproval");
                // }

                // $allSearchable = UserFiles::where("userUid", $userFile->userUid)->where('year', $this_year)->get();

                // foreach($allSearchable as $search){
                //     if(explode("_", $search->fileId)[0] == "other"){
                //         array_push($all, $search->fileId);
                //     }
                // }
    
                // foreach($all as $file){
                //     $file_search = UserFiles::where("userUid", $userFile->userUid)->where('fileId', $file)->where('year', $this_year)->first();

                //     if(($studentInfoForMail->studentType == "Cash / Direct" && $file !== 'bursaryApproval') || $studentInfoForMail->studentType != "Cash / Direct" && $file !== 'bankStatement'){

                //         if($file == "studentCard"){
                //             $fileName = "Student Card";
                //         }elseif($file == "copyID"){
                //             $fileName = "ID Document";
                //         }elseif($file == "proof_of_payment"){
                //             $fileName = "Proof of Payment";
                //         }elseif($file == "proofRegistration"){
                //             $fileName = "Proof of Registration";
                //         }elseif($file == "bankStatement"){
                //             $fileName = "Bank Statements";
                //         }elseif($file == "bursaryApproval"){
                //             if($studentInfoForMail->studentType != "NSFAS"){
                //                 $fileName = "Bursary Approval";
                //             }else{
                //                 $fileName = "NSFAS Approval";
                //             }
                //         }elseif(explode("_", $file)[0] == "other"){
                //             $fileName = strtoupper(str_replace("_", " ", str_replace("other", "other |", $file)));
                //         }

                //         if($file_search && !empty($file_search)){
                //             if($file_search->status !== "Approved" && $file_search->status !== "Uploaded"){
                //                 array_push($outstanding, $fileName);
                //             }

                //             if($file_search->status == "Approved"){
                //                 array_push($approved, $fileName);
                //             }
                //         }else{
                //             array_push($outstanding, $fileName);
                //         }

                //     }

                // }

                // $outstandingDocsText = "<ol>";

                // foreach($outstanding as $out){
                //     $outstandingDocsText .= "<li style='color: #fff; font-size: 21px; font-weight: 400; line-height: 30px;'>".$out."</li>";
                // }

                // $outstandingDocsText .= "</ol>";

                // $approvedDocsText = "<ol>";

                // foreach($approved as $apprv){
                //     $approvedDocsText .= "<li style='color: #fff; font-size: 21px; font-weight: 400; line-height: 30px;'>".$apprv."</li>";
                // }

                // $approvedDocsText .= "</ol>";


                $message_replace['bookingRef'] = $booking->booking_code;
                $message_replace['name'] = $studentInfoForMail->userFirstName;
                $message_replace['document_name'] = $user_status['typeName'];
                $message_replace['application_link'] = "<a href='".$url."' target='_blank'>";
                $message_replace['close_application_link']="</a>";
                // $message_replace['missing'] = $outstandingDocsText;
                // $message_replace['apprved'] = $approvedDocsText;

                $mailerForMessage = MailerList::where("mailer_name", "Document Approved")->first();

                if($mailerForMessage->status == '1'){

                    $mailerMessage = $mailerForMessage->message;

                    $newMessage = str_replace("[application_number]", "#".$message_replace['bookingRef'], $mailerMessage);
                    $newMessage = str_replace("[document_name]", $message_replace['document_name'], $newMessage);
                    $newMessage = str_replace("[document_name]", $message_replace['document_name'], $newMessage);
                    $newMessage = str_replace("[name]", $message_replace['name'], $newMessage);
                    // $newMessage = str_replace("[approved_document_list]", $message_replace['apprved'], $newMessage);
                    // $newMessage = str_replace("[outstanding_document_list]", $message_replace['missing'], $newMessage);
                    $newMessage = str_replace("[application_link]", $message_replace['application_link'], $newMessage);
                    $newMessage = str_replace("[close_application_link]", $message_replace['close_application_link'], $newMessage);
    
                    $mailData['name'] = $studentInfoForMail->userFirstName;
                    $mailData['email'] = $user->email;
                    $mailData['messageForMail'] = $newMessage;
                    $mailData['subject'] = str_replace("[document_name]", $user_status['typeName'], str_replace("[application_number]", $message_replace['bookingRef'], $mailerForMessage->subject));
                    
                    $allowedMailsCC = Lookups::where('group_id', '11')->pluck('lookup_name')->first();
                    $mailData['cc'] = explode(";", $allowedMailsCC);
    
                    $allowedMails = Lookups::where('group_id', '10')->pluck('lookup_name')->first();
    
                    if(!empty($allowedMails)){
    
                        $mailArr = explode(";", $allowedMails);
        
                        if(in_array(explode("@", $mailData['email'])[1], $mailArr)){
                            $mailData['email'] = $studentInfoForMail->userEmail;
                        }else{
                            $mailData['email'] = "conurbandigital@gmail.com";
                        }
                    }else{
                        $mailData['email'] = $studentInfoForMail->userEmail;
                    }
    
                    Mail::send('email.green_back', $mailData, function($message) use ($mailData) {
                        $message->to($mailData['email'])->subject($mailData['subject']);
                        $message->bcc($mailData['cc']);
                        $message->from('noreply@campusafrica.co.za','Campus Africa');
                    });
                }

            }

            return [$userFile, StudentsInfo::where('userUid', $userFile->userUid)->where('year', $this_year)->first()];

        }
    }

    public function action_list(Request $request, $userId, $filter){

        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        if($filter == "all"){
            $list = ActionList::with('users')->where('userUid', $userId)->where('year', $this_year)->orderBy('created_at', 'DESC')->get();
        }elseif($filter == "admin"){
            $list = ActionList::with('users')->where('userUid', $userId)->where('year', $this_year)->where('actionUserRole', '1')->orderBy('created_at', 'DESC')->get();
        }elseif($filter == "applicant"){
            $list = ActionList::with('users')->where('userUid', $userId)->where('year', $this_year)->where('actionUserRole', '0')->orderBy('created_at', 'DESC')->get();
        }

        return $list;
    }

    public function document_list(Request $request, $userId, $filter){

        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        $user = User::with('files')->where('id', $userId)->first();
        $userInfo = StudentsInfo::where('userUid', $userId)->where('year', $this_year)->first();
        $userFiles = UserFiles::where('userUid', $userId)->where('year', $this_year)->get();
        $userData = [];
        $uploaded = [];
        // $all = [0 => 'copyID', 1 => 'studentCard', 2 => 'bankStatement', 3 => 'proofRegistration', 4 => 'proof_of_payment', 5 => 'moveIn', 6 => "bursaryApproval" ];

        $all = ['copyID', 'studentCard', 'proofRegistration', 'proof_of_payment'];

        if($userInfo->applicationStatus == "Approved Confirmed"){
            array_push($all, "moveIn");
        }
        if($userInfo->studentType == "Cash / Direct"){
            array_push($all, "bankStatement");
        }
        if($userInfo->studentType !== "Cash / Direct"){
            array_push($all, "bursaryApproval");
        }
        $data = [];
        
        if($filter == "all"){
            foreach($userFiles as $file){
                array_push($uploaded, $file->fileId);
                array_push($data, ["field" => $file->fileId, "status" => $file->status, "url" => $file->fileUrl, "uploaded_by" => $file->uploaded_by, "created_at" => $file->created_at, "id" => $file->id]);
            }
    
            $left = array_diff($all, $uploaded);
            foreach($left as $dataLeft){
                array_push($data, ["field" => $dataLeft, "status" => ""]);
            }
        }elseif($filter == "review"){
            foreach($userFiles as $file){
                if($file->status == "Uploaded"){
                    array_push($uploaded, $file->fileId);
                    array_push($data, ["field" => $file->fileId, "status" => $file->status, "url" => $file->fileUrl, "uploaded_by" => $file->uploaded_by, "created_at" => $file->created_at, "id" => $file->id]);
                }
            }
        }elseif($filter == "approved"){
            foreach($userFiles as $file){
                if($file->status == "Approved"){
                    array_push($uploaded, $file->fileId);
                    array_push($data, ["field" => $file->fileId, "status" => $file->status, "url" => $file->fileUrl, "uploaded_by" => $file->uploaded_by, "created_at" => $file->created_at, "id" => $file->id]);
                }
            }
        }elseif($filter == "noDoc"){
            foreach($userFiles as $file){
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

        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        // $user = User::where('id', $userId)->first();
        $user = StudentsInfo::where('userUid', $userId)->where('year', $this_year)->first();

        $data['name'] = $user->userFirstName;
        $data['email'] = $user->userEmail;
        $data['roomNo'] = $user->roomNo;
        $data['bookingRef'] = $user->bookingRef;

        // Mail::send('email.application_approved', $data, function($message) use ($data) {
        //     $message->to($data['email'], $data['name'])->subject
        //         ('Thank you Application');
        //     $message->bcc('test@whitehart.co.za','Name');
        //     $message->from('test@test.com','test@test.com');
        // });

        // User::where('id', $userId)->update(['rob_50_approve' => 2, 'rob_55_lease' => 0]);
        StudentsInfo::where('userUid', $userId)->where('year', $this_year)->update(['rob_50_approve' => 2, 'rob_55_lease' => 0]);

        ActionList::create([
            "userUid" => $userId,
            "action" => "Approved application and sent confirmation email",
            "actionUserUid" => Auth::user()->id,
            "actionUserDisplayName" => Auth::user()->userFirstName,
            "actionUserRole" => Auth::user()->userRole,
            "year" => $this_year,
        ]);

        // $this->update_user_status($userId);

    }

    protected function reject_application(Request $request){
        $userId = $request->userId;

        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        // $user = User::where('id', $userId)->first();
        $user = StudentsInfo::where('userUid', $userId)->where('year', $this_year)->first();

        $data['name'] = $user->userFirstName;
        $data['email'] = $user->userEmail;
        $data['roomNo'] = $user->roomNo;
        $data['bookingRef'] = $user->bookingRef;

        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        // Mail::send('email.application_approved', $data, function($message) use ($data) {
        //     $message->to($data['email'], $data['name'])->subject
        //         ('Thank you Application');
        //     $message->bcc('test@whitehart.co.za','Name');
        //     $message->from('test@test.com','test@test.com');
        // });

        // User::where('id', $userId)->update(['rob_50_approve' => 3, 'rob_60_cancelled' => 1]);
        StudentsInfo::where('userUid', $userId)->where('year', $this_year)->update(['rob_50_approve' => 3, 'rob_60_cancelled' => 1]);
        Booking::where('userId', $userId)->where('year', $this_year)->update(['property_data_id' => null, 'floor' => null, 'unit' => null, 'room' => null, 'bed' => null, 'bed_code' => null, 'room_rate' => null, 'cancelled' => 1]);

        ActionList::create([
            "userUid" => $userId,
            "action" => "Rejected application and sent rejectment email",
            "actionUserUid" => Auth::user()->id,
            "actionUserDisplayName" => Auth::user()->userFirstName,
            "actionUserRole" => Auth::user()->userRole,
            "year" => $this_year,
        ]);

        // $this->update_user_status($userId);
    }

    public function documents(Request $request){

        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        $studentInfo = StudentsInfo::where('userUid', Auth::user()->id)->where('year', $this_year)->first();
            
            $allFiles = [];
            $all = ['copyID', 'studentCard', 'proofRegistration', 'proof_of_payment'];

            if($studentInfo->rob_60_cancelled == "0" && $studentInfo->rob_50_approve == '2'){
                array_push($all, "moveIn");
            }
            if($studentInfo->studentType === "Cash / Direct"){
                array_push($all, "bankStatement");
            }
            if($studentInfo->studentType !== "Cash / Direct"){
                array_push($all, "bursaryApproval");
            }

            $allSearchable = UserFiles::where("userUid", Auth::user()->id)->where('year', $this_year)->get();

            foreach($allSearchable as $search){
                if(!in_array($search->fileId, $all)){
                    array_push($all, $search->fileId);
                }
            }

            foreach($all as $file){
                $file_search = UserFiles::where("userUid", Auth::user()->id)->where('fileId', $file)->where('year', $this_year)->first();

                if($file_search && !empty($file_search)){
                    if($file_search->status == "Approved"){
                        $important = 1;
                    }else if($file_search->status == "Uploaded"){
                        $important = 2;
                    }else{
                        $important = 3;
                    }
                    $output = (object) array(
                        "fileId" => $file_search->fileId,
                        "status" => $file_search->status !== null ? $file_search->status : "None",
                        "fileUrl" => $file_search->fileUrl,
                        "important" => $important,
                    );
                    array_push($allFiles, $output);
                }else{
                    $output = (object) array(
                        "fileId" => $file,
                        "status" => "None",
                        "fileUrl" => "",
                        "important" => 3
                    );
                    array_push($allFiles, $output);
                }
            }

            usort($allFiles, function($a, $b) {
                return $b->important - $a->important;
            });

            $user = StudentsInfo::where('userUid', Auth::user()->id)->where('year', $this_year)->first();

            return view('user.documents', ["headerTitle" => "My Documents", "allFiles" => $allFiles, "active" => 1, "user" => $user]);
    }

    public function leases(Request $request){
        
        $this_year = Lookups::where('group_id', '4')->first();

        $leases = UserFiles::with('users')->with('booking')->whereHas('booking', function($q) use($this_year) {
            $q->where('year', $this_year->lookup_name); 
        })->where('fileId', 'moveIn')->where('year', $this_year)->get();

        return view('admin.leases', ["headerTitle" => "Leases", "leases" => $leases, "title" => "Leases"]);
    }

    public function mailing(Request $request){

        if(Auth::user()->role_lookup == 1){

            $thankYou = view('email.application_thank_you');
    
            $MailerList = MailerList::all();
    
            return view('admin.mailing', ["headerTitle" => "Mailers", "bookingRef" => "00000", "name" => "Johan", "roomNo" => "000", "MailerList" => $MailerList, "title" => "Mailers"]);
        }else{
            return "404 Error";
        }

    }

    public function invoices(Request $request){
        return view('admin.invoices', ["headerTitle" => "Invoices", "title" => "Invoices"]);
    }

    public function thanks(Request $request){

        // $data['test'] = "";

        // Mail::send('email.application_thank_you', $data, function($message) use ($data) {
        //     $message->to("mirichea@whitehart.co.za", "Mirichea")->subject
        //         ('Thank you Application');
        //     $message->bcc('test@whitehart.co.za','Name');
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

    public function saveMail(Request $request){
        MailerList::where('id', $request->mailerID)->update(['message' => $request->text, 'subject' => $request->mailSubject]);
        return 'true';
    }

    public function get_mail_list(Request $request){
        $mailList = MailerList::get();

        return json_encode($mailList);
    }

    // public function saveMailTy(Request $request){
        
    //     MailerList::where('id', '6')->update(['message' => $request->text]);

    //     return 'true';
        
    // }

    // public function saveMailAa(Request $request){
        
    //     MailerList::where('id', '1')->update(['message' => $request->text]);

    //     return 'true';
        
    // }

    // public function saveMailAav(Request $request){
        
    //     MailerList::where('id', '2')->update(['message' => $request->text]);

    //     return 'true';
        
    // }
    // public function saveMailAD(Request $request){
        
    //     MailerList::where('id', '5')->update(['message' => $request->text]);

    //     return 'true';
        
    // }
    // public function saveMailADd(Request $request){
        
    //     MailerList::where('id', '3')->update(['message' => $request->text]);

    //     return 'true';
        
    // }
    // public function saveMailAR(Request $request){
        
    //     MailerList::where('id', '4')->update(['message' => $request->text]);

    //     return 'true';
        
    // }

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

        $propertyList = Residence::get();
        
        $userAccess = PropertyPermission::where('userUid', $userId)->get();
        $accessString = "";

        foreach($userAccess as $access){
            $accessString .= $access->property.",";
        }
        
        if(Auth::user()->role_manage_users == "1") {
            return view('admin.userEdit', ["headerTitle" => "User Edit", "user" => $user, "title" => "Edit User", "propertyList" => $propertyList, "accessString" => $accessString, "userAccess" => $userAccess]);
        } else {
            return "404 Error";
        }
    }

    public function edit_admin(Request $request){

        $oldUser = User::where('id', $request->userUid)->first();

        $regen = "0";

        if($request->role_lease_regen !== "1"){
            $regen = "0";
        }else{
            $regen = "1";
        }       

        User::where('id', $request->userUid)->update([
            "userFirstName" => ucwords(strtolower($request->firstName)),
            "userLastname" => ucwords(strtolower($request->lastName)),
            // "userTitle" => $request->title,
            // "userDob" => $request->dob,
            // "userCell" => $request->cell_no,
            "userEmail" => $request->email,
            "email" => $request->email,
            "userRole" => "1",
            // "email_info" => $request->email_details,
            "role_create_app" => $request->create_app,
            "role_edit_app" => $request->edit_app,
            "role_upload_docs" => $request->upload_docs,
            "role_approve_docs" => $request->approve_docs,
            "role_delete_booking" => $request->delete_booking,
            "role_delete_uploaded" => $request->delete_uploaded,
            // "role_braamlofts" => $request->role_braamlofts,
            // "role_80_jorissen" => $request->role_80_jorissen,
            // "role_49_jorissen" => $request->role_49_jorissen,
            // "role_dunvista_mansions" => $request->role_dunvista_mansions,
            // "role_wynton_joy" => $request->role_wynton_joy,
            // "role_126_siemert" => $request->role_126_siemert,
            // "role_ymca" => $request->role_ymca,
            "role_change_date" => $request->role_change_date,
            "role_change_bed" => $request->role_change_bed,
            "role_manage_users" => $request->role_manage_users,
            "role_nsfas_approval_upload" => $request->role_nsfas_approval_upload,
            "role_cancel_booking" => $request->role_cancel_booking,
            "role_update_booking_fee" => $request->role_update_booking_fee,
            "role_lookup" => $request->role_lookup,
            "role_comment" => $request->role_comment,
            "user_role_name" => $request->user_role_name,
            "role_cancel_lease" => $request->role_cancel_lease,
            "role_lease_regen" => $regen,
            "role_modify_approved" => $request->role_modify_approved !== '1' ? 0 : 1,
            "role_non_funding" => $request->role_non_funding !== '1' ? 0 : 1,
            "role_funding" => $request->role_funding !== '1' ? 0 : 1,
            "role_app_lease" => $request->role_app_lease !== '1' ? 0 : 1,
            "template" => $request->template,
            "role_change_student_type" => $request->role_change_student_type,
            "role_view_booking_summary" => $request->role_view_booking_summary,
            "role_toggle_online_booking" => $request->role_toggle_online_booking,
            "role_change_building" => $request->role_change_building,
            // "role_change_building" => $request->role_change_building,
            "role_waive_fee" => $request->role_waive_fee,
            // "role_approve_waive" => $request->role_approve_waive,
            "role_bm_report" => $request->role_bm_report,
            "role_mda_report" => $request->role_mda_report,
            "role_all_apps_report" => $request->role_all_apps_report,
        ]);

        $allAccess = explode(",", $request->propertyAccess);
        $userAccess = PropertyPermission::where('userUid', $request->userUid)->get();

        foreach($userAccess as $access){
            if(!in_array($access->property, $allAccess)){
                PropertyPermission::where('id', $access->id)->delete();
            }
        }

        foreach($allAccess as $access){
            if($access !== ""){
                $currentAccess = PropertyPermission::where('userUid', $request->userUid)->where('property', $access)->first();
    
                if(!$currentAccess || empty($currentAccess)){
                    PropertyPermission::create(['userUid'=> $request->userUid, "property" => $access]);
                }
            }
        }

        if($oldUser->userEmail !== $request->email){
            $otherTokens = UserResetToken::where("userUid", $request->userUid)->where('active', 1)->get();

            foreach($otherTokens as $old){
                UserResetToken::where("id", $old->id)->update(['active' => 0]);
            }

            $userToken = UserResetToken::create(["userUid" => $request->userUid, "userToken" => $this->generateRandomLongString()]);

            $data['name'] = $request->firstName;
            $data['email'] = $request->email;
            $data['userToken'] = $userToken->userToken;
            $data['type'] = "updated";

            Mail::send('email.system_user_application', $data, function($message) use ($data) {
                $message->to($data['email'], $data['name'])->subject
                    ('Campus Africa User Updated');
                $message->from('noreply@campusafrica.co.za','Campus Africa');
            });
        }

        return redirect('/users');
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
        $newData = ['userFirstName|First Name' => $request->firstName,'userLastname|Last Name' => $request->lastName,'userTitle|Title' => $request->title,'userDob|Date of Birth' => $request->dob,'userCell|Cellphone Number' => $request->cell_no,'userEmail|Email' => $request->email,'userId|I.D Number' => $request->idNumber,'userNationality|Nationality' => $request->nationality,'userPassport|Passport Number' => $request->passportNo,'institutionStudy|Institution of Study' => $request->institStudy,'studentNo|Student Number' => $request->studNumber,'studentType|Student Type' => $request->studentType,'faculty|Faculty' => $request->faculty,'year|Year' => "2022",'sponFirstName|Parent / Sponser First Name' => $request->pFirstName,'sponLastName| Parent / Sponser Last Name' => $request->pLastName,'sponCell|Parent / Sponser Cellphone Number' => $request->pCell_no,'sponEmail|Parent / Sponser Email' => $request->pEmail,'sponId|Parent / Sponser I.D Number' => $request->pIdNumber,'burCompanyName|Burser Company Name' => $request->bCompanyName,'burRegNo|Burser Registration Number' => $request->bRegistrationNo,'burContactPerson|Burser Contact Person' => $request->bContactPerson,'burContactNo|Burser Contact Number' => $request->bContactNumber,'burEmail|Burser Email' => $request->bEmail,'kinFirstName|Next of Kin First Name' => $request->kFirstName,'kinLastName|Next of Kin Last Name' => $request->kLastName,'kinTitle|Next of Kin Title' => $request->kTitle,'kinId|Next of Kin I.D Number' => $request->kIdNumber,'kinAddressStr|Next of Kin Street Number' => $request->kAddress1,'kinAddressSuburb|Next of Kin Suburb' => $request->kAddress2,'kinAddressCity|Next of Kin City' => $request->kAddressCity,'kinAddressPost|Next of Kin Postal Code' => $request->kAddressPostal,'kinRelationship|Next of Kin Relationship' => $request->kRelationship,'kinTelNo|Next of Kin Telephone Number' => $request->kTeleNo,'kinCellNo|Next of Kin Cellphone Number' => $request->kCellNo,'kinWorkNo|Next of Kin Work Number' => $request->kWorkNo,'kinEmail|Next of Kin Email' => $request->kEmail,'kinNationality|Next of Kin Nationality' => $request->kinNationality,'kinPassportNo|Next of Kin Passport Number' => $request->kinPassportNo,'pNationality|Parent / Sponser Nationality' => $request->pNationality,'pPassportNo|Parent / Sponser Passport Number' => $request->pPassportNo,'pAddress1|Parent / Sponser Street Number' => $request->pAddress1,'pAddress2|Parent / Sponser Suburb' => $request->pAddress2,'pAddressCity|Parent / Sponser City' => $request->pAddressCity,'pAddressPostal|Parent / Sponser Postal Code' => $request->pAddressPostal,'userAddress1|Street Number' => $request->userAddress1,'userAddress2|Suburb' => $request->userAddress2,'userAddressCity|City' => $request->userAddressCity,'userAddressPostal|PostalCode' => $request->userAddressPostal, 'bAddress1|Burser Street Number' => $request->bAddress1,'bAddress2|Burser Suburb' => $request->bAddress2,'bAddressCity|Burser City' => $request->bAddressCity,'bAddressPostal|Burser Postal Code' => $request->bAddressPostal,'bPayment|Burser Payment' => $request->bPayment,'bint|Burser Intermediary' => $request->bint,'bIntName|Burser Intermediary Name' => $request->bIntName,];

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
                $this_year = Lookups::where('group_id', '4')->first();
                $this_year = $this_year->lookup_name;

                ActionList::create([
                    "userUid" => $request->userUid,
                    "action" => "Updated ".$changedField." From ".$oldData->$key." to ".$new,
                    "actionUserUid" => Auth::user()->id,
                    "actionUserDisplayName" => Auth::user()->userFirstName,
                    "actionUserRole" => Auth::user()->userRole,
                    "year" => $this_year
                ]);
            }
        }

        User::where('id', $request->userUid)->update([
            'userFirstName' => ucwords(strtolower($request->firstName)),
            'userLastname' => ucwords(strtolower($request->lastName)),
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
            // 'nsfas_bursar' => $request->nsfas_bursar,
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

        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        $allApprovedFiles = UserFiles::where("userUid", $studentId)->where('status', 'Approved')->where('year', $this_year)->get();
        $allPendingFiles = UserFiles::where("userUid", $studentId)->where('status', 'Uploaded')->where('year', $this_year)->get();
        $allRejectedFiles = UserFiles::where("userUid", $studentId)->where('status', 'Rejected')->where('year', $this_year)->get();

        $allFiles = UserFiles::where("userUid", $studentId)->where('year', $this_year)->get();

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

    

    

    public function new_student(Request $request){

        $lookup = Lookups::get();

        $allowed = [];
        $allProp = Residence::whereNull('status')->get();
        $permissionProp = PropertyPermission::where('userUid', Auth::user()->id)->get();

        foreach($allProp as $prop){
            foreach($permissionProp as $permission){
                if($permission->property == $prop->id){
                    array_push($allowed, $prop);
                }
            }
        }
        
        return view('admin.new_student', ["headerTitle" => "Add New Student", "lookups" => $lookup, "title" => "Add Student", "PropertyList" => $allowed]);
    }

    public function add_student(Request $request){
        $user = User::create([
            'name' => $request->firstName,
            // 'userFirstName' => $request->firstName,
            // 'userLastname' => $request->lastName,
            // 'userTitle' => $request->title,
            'userDob' => $request->dob,
            // 'userCell' => $request->cell_no,
            'userEmail' => $request->email,
            'email' => $request->email,
            'password' => Hash::make("!Placeholder#"),
            'userId' => $request->idNumber,
            'userNationality' => $request->nationality,
            'userPassport' => $request->passportNo,
            // 'institutionStudy' => $request->institStudy,
            // 'studentNo' => $request->studNumber,
            // // 'institutionCampus' => $request->institCampus,
            // 'faculty' => $request->faculty,
            // 'year' => $request->year,
            // 'sponFirstName' => $request->pFirstName,
            // 'sponLastName' => $request->pLastName,
            // 'sponCell' => $request->pCell_no,
            // 'sponEmail' => $request->pEmail,
            // 'sponId' => $request->pIdNumber,
            // 'burCompanyName' => $request->bCompanyName,
            // 'burRegNo' => $request->bRegistrationNo,
            // 'burContactPerson' => $request->bContactPerson,
            // 'burContactNo' => $request->bContactNumber,
            // 'burEmail' => $request->bEmail,
            // 'kinFirstName' => $request->kFirstName,
            // 'kinLastName' => $request->kLastName,
            // 'kinTitle' => $request->kTitle,
            // //'kinId' => $request->kIdNumber,
            // 'kinAddressStr' => $request->kAddress1,
            // 'kinAddressSuburb' => $request->kAddress2,
            // 'kinAddressCity' => $request->kAddressCity,
            // 'kinAddressPost' => $request->kAddressPostal,
            // 'kinRelationship' => $request->kRelationship,
            // 'kinTelNo' => $request->kTeleNo,
            // 'kinCellNo' => $request->kCellNo,
            // 'kinWorkNo' => $request->kWorkNo,
            // 'kinEmail' => $request->kEmail,
            // 'studentType' => $request->studentType,
            // 'bookingStatus' => "Empty",
            // 'applicationStatus' => "Empty",
            // 'yearOfStudy' => $request->yearOfStudy,
            // //'kinNationality' => $request->kinNationality,
            // //'kinPassportNo' => $request->kinPassportNo,
            // 'pNationality' => $request->pNationality,
            // 'pPassportNo' => $request->pPassportNo,
            // 'pAddress1' => $request->pAddress1,
            // 'pAddress2' => $request->pAddress2,
            // 'pAddressCity' => $request->pAddressCity,
            // 'pAddressPostal' => $request->pAddressPostal,
            // 'userAddress1' => $request->userAddress1,
            // 'userAddress2' => $request->userAddress2,
            // 'userAddressCity' => $request->userAddressCity,
            // 'userAddressPostal' => $request->userAddressPostal,
            // 'app_address' => $request->app_address,
            // 'app_address_p' => $request->app_address_p,
            // 'app_parent_kin' => $request->app_parent_kin,
            // 'bAddress1' => $request->bAddress1,
            // 'bAddress2' => $request->bAddress2,
            // 'bAddressCity' => $request->bAddressCity,
            // 'bAddressPostal' => $request->bAddressPostal,
            // 'bPayment' => $request->bPayment,
            // 'bint' => $request->bint,
            // 'bIntName' => $request->bIntName,
            // // 'nsfas_bursar' => $request->nsfas_bursar,
            // 'rob_55_lease' => 3,
            // 'rob_45_other' => 3,

            // 'userCellCode' => $request->cell_no_code,
            // 'kinCellCode' => $request->kCellNo_code,
            // 'kinWorkCode' => $request->kWorkNo_code,
            // 'kinTelCode' => $request->kTeleNo_code,
            // 'burContactCode' => $request->bContactNumber_code,
            // 'sponCellCode' => $request->pCell_no_code,
            // 'app_source' => 0,
        ]);

        $userInfo = StudentsInfo::create([
            'userUid' => $user->id,
            'userFirstName' => ucwords(strtolower($request->firstName)),
            'userLastname' => ucwords(strtolower($request->lastName)),
            'userTitle' => $request->title,
            'userDob' => $request->dob,
            'userCell' => $request->cell_no,
            'userEmail' => $request->email,
            'userId' => $request->idNumber,
            'userNationality' => $request->nationality,
            'userPassport' => $request->passportNo,
            'institutionStudy' => $request->institStudy,
            'studentNo' => $request->studNumber,
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
            'rob_10_fee' => 0,
            'rob_15_id' => 0,
            'rob_20_card' => 0,
            'rob_25_reg' => 0,
            'rob_30_funds' => 0,
            'rob_35_info' => 1,
            'rob_40_bed' => 0,
            'rob_50_approve' => 0,
            'rob_60_cancelled' => 0,
            'onboard_complete' => 0,
            'rob_55_lease' => 3,
            'rob_45_other' => 3,
            'userCellCode' => $request->cell_no_code,
            'kinCellCode' => $request->kCellNo_code,
            'kinWorkCode' => $request->kWorkNo_code,
            'kinTelCode' => $request->kTeleNo_code,
            'burContactCode' => $request->bContactNumber_code,
            'sponCellCode' => $request->pCell_no_code,
            'app_source' => 0,
            'gender' => $request->gender,
        ]);

        ActionList::create([
            "userUid" => $user->id,
            "action" => "Registered ".$request->firstName." as a new user",
            "actionUserUid" => Auth::user()->id,
            "actionUserDisplayName" => Auth::user()->userFirstName,
            "actionUserRole" => Auth::user()->userRole,
            "year" => $request->year,
        ]);

        //Booking data        
        $booking_data = array(
            "userId" => $user->id, 
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
        $is_nsfas = false;

        if($roomTypeData->use_nsfas == 1){
            $is_nsfas = true;
        }

        $allResidences = Residence::whereNull('status')->get();
        $allRoomTypes = RoomTypes::get();

        $deposit = 0;


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
                        $deposit = $room->acf->price_from;

                        // dd($room);
    
                        $appliesTo = $room->acf->booking_fee_applies_to;

                        if($room->acf->booking_fee_override !== ""){
                            $booking_data['room_rate'] = $room->acf->booking_fee_override;
                        }
    
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

        if($roomTypeData && $roomTypeData->pull_price == null){
            $booking_data['room_rate'] = $roomTypeData->booking_price;
            $deposit = $roomTypeData->rent_price;

            if($userInfo->studentType == "NSFAS"){
                if($roomTypeData->apply_nsfas == null){
                    $booking_data['room_rate'] = "0";
                    $actionFileData['action'] = "Booking Fee is R0";
                }
            }

            if($userInfo->studentType == "Bursary"){
                if($roomTypeData->apply_bursary == null){
                    $booking_data['room_rate'] = "0";
                    $actionFileData['action'] = "Booking Fee is R0";
                }
            }

            if($userInfo->studentType == "Cash / Direct"){
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

        $booking = Booking::create($booking_data);

        $admin_fee = Lookups::where('group_id', '8')->first();
        // $admin_fee = $admin_fee->lookup_name;

        // dd($admin_fee->lookup_name);

        // $ts1 = strtotime($booking_data['lease_from_date']);
        // $ts2 = strtotime($booking_data['lease_to_date']);
        // $month1 = date('m', $ts1);
        // $month2 = date('m', $ts2);
        // $diff = $month2 - $month1;
        $diff = 0;
        for($i = new DateTime($booking_data['lease_from_date']); $i <= new DateTime($booking_data['lease_to_date']); $i->modify('+1 month')){
            $diff++;
        }
        $yearly_rent = floatval($deposit) * floatval($diff);

        $institution = Lookups::where('id', $userInfo->institutionStudy)->first();
        $funded = floatval($institution->nsfas_amount) * floatval($diff);

        $topup = floatval($yearly_rent) - floatval($funded);
        $monthlyFunded = $institution->nsfas_amount;
        $nsfas_rate = null;

        if(($userInfo->studentType == "Cash / Direct") || ($userInfo->studentType == "Bursary" && $userInfo->nsfas_bursar !== 1)){
            $admin_fee = $admin_fee->lookup_name;
            $deposit = $monthly = $monthlyFunded = $deposit;
            $funded = $yearly_rent;
            $topup = '0';
            // $monthlyFunded = 0;
        }else{
            $monthly = $deposit;
            $deposit = 0;
            $admin_fee = 0;

            if($is_nsfas === true){
                $monthly = $monthlyFunded;
                $deposit = 0;
                $admin_fee = 0;
                $yearly_rent = $funded;
                $topup = 0;
                $nsfas_rate = 1;
            }
        }

        BookingPayment::create(["bookingId" => $booking->id, "booking_fee" => $booking_data['room_rate'], "booking_fee_waived" => 0, "rob_b_waived" => 0, "loan_admin_fee" => $admin_fee, "loan_admin_fee_waived" => 0, "rob_loan_waived" => 0, "deposit" => floatval($deposit), "deposit_waived" => 0, "rob_deposit_waived" => 0, "monthly_rental" => $monthly, "monthly_rental_total" => $yearly_rent, "total_funded" => $funded, "total_top_up" => $topup, "funded_monthly" => $monthlyFunded, "nsfas_rate" => $nsfas_rate]);

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
        User::where('id', $user->id)->update(["bookingRef" => $booking->id]);
        StudentsInfo::where('id', $userInfo->id)->update(['bookingId' => $booking->id, "bookingRef" => $booking->id]);

        $residence = Residence::where('residence', $request->property)->first();

        if($booking_data['room_rate'] == "0"){

            $this_year = Lookups::where('group_id', '4')->first();
            $this_year = $this_year->lookup_name;

            // User::where('id', $user->id)->update(["rob_10_fee" => 2]);
            StudentsInfo::where('id', $userInfo->id)->update(["rob_10_fee" => 2]);

            $userData = StudentsInfo::where('userUid', $userInfo->id)->where('year', $this_year)->first();
            if($userData->rob_40_bed != 2){
                StudentsInfo::where('id', $userInfo->id)->update(["rob_40_bed" => 1]);
            }

            UserFiles::create([
                'userUid'=> $user->id,
                'fileId' => 'proof_of_payment',
                'status' => 'Approved',
                'year' => $this_year,
            ]);

            $actionFileData['userUid'] = $user->id;
            $actionFileData['actionUserUid'] = Auth::user()->id;
            $actionFileData['actionUserDisplayName'] = Auth::user()->userFirstName;
            $actionFileData['actionUserRole'] = Auth::user()->userRole;
            $actionFileData['year'] = $this_year;
        
            ActionList::create($actionFileData);
        }

        if($request->studentType == "NSFAS"){
            
            $nsfasStudentsApproval = ImportNSFASStudentAuditLog::where('student_number', $request->studNumber)->orderBy('created_at', 'desc')->first();

            if(!empty($nsfasStudentsApproval)){

                $yearOfImport = ImportNSFASApproval::where('id', $nsfasStudentsApproval->importId)->first();

                $this_year = Lookups::where('group_id', '4')->first();
                $this_year = $this_year->lookup_name;
    
                if(!empty($nsfasStudentsApproval) && $yearOfImport->year == $this_year){
                    UserFiles::create([
                        'userUid' => $user->id,
                        'fileId' => 'bursaryApproval',
                        'status' => 'Approved',
                        'year' => $this_year,
                    ]);
    
                    // User::where('id', $user->id)->update(["rob_30_funds" => 2]);
                    StudentsInfo::where('id', $userInfo->id)->update(["rob_30_funds" => 2]);
    
                    ActionList::create([
                        "userUid" => $user->id,
                        // "action" => "NSFAS / Bursary has been Approved via import",
                        "action" => "NSFAS Approval has been Approved via import",
                        "actionUserUid" => Auth::user()->id,
                        "actionUserDisplayName" => Auth::user()->userFirstName,
                        "actionUserRole" => Auth::user()->userRole,
                        "year" => $this_year,
                    ]);
                }
            }
        }

        $generator = new LoginUrl($user);
        $generator->setRedirectUrl('/verify-email-account');
        $url = $generator->generate();

        $message_replace['bookingRef'] = $bookingYear.$bookingPrefix.$bookingID;
        $message_replace['name'] = $user->userFirstName;
        $message_replace['application_link'] = "<a href='".$url."' target='_blank'>";
        $message_replace['close_application_link']="</a>";

        $mailerForMessage = MailerList::where("mailer_name", "Password Validation From Admin")->first();

        if($mailerForMessage->status == '1'){

            $mailerMessage = $mailerForMessage->message;
    
            $newMessage = str_replace("[application_number]", "#".$message_replace['bookingRef'], $mailerMessage);
            $newMessage = str_replace("[name]", $message_replace['name'], $newMessage);
            $newMessage = str_replace("[application_link]", $message_replace['application_link'], $newMessage);
            $newMessage = str_replace("[close_application_link]", $message_replace['close_application_link'], $newMessage);
    
            $data['name'] = $user->userFirstName;
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
            
    
            // if(explode("@", $data['email'])[1] == "conurban.co.za"){
            //     $data['email'] = $user->email;
            // }else{
            //     $data['email'] = "conurbandigital@gmail.com";
            // }
    
            Mail::send('email.yellow_back', $data, function($message) use ($data) {
                // $message->to($data['email'], $data['name'])->subject
                //     ('Campus Africa User Application');
                $message->to($data['email'])->subject($data['subject']);
                // $message->cc(['conurbandigital@gmail.com', 'roger@whitehart.co.za']);
                $message->bcc($data['cc']);
                $message->from('noreply@campusafrica.co.za','Campus Africa');
            });
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

        $propertyList = Residence::whereNull('status')->get();

        return view('admin.userAdd', ["headerTitle" => "Add New System User", "title" => "Add User", "propertyList" => $propertyList]);
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

    public function generateRandomLongString($length = 24) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function add_admin(Request $request){

        $password = $this->generateRandomString();

        $regen = "0";

        if($request->role_lease_regen !== "1"){
            $regen = "0";
        }else{
            $regen = "1";
        }

        //

        $newUser = User::create([
            "name" => $request->firstName,
            "userFirstName" => ucwords(strtolower($request->firstName)),
            "userLastname" => ucwords(strtolower($request->lastName)),
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
            // "role_braamlofts" => $request->role_braamlofts,
            // "role_80_jorissen" => $request->role_80_jorissen,
            // "role_49_jorissen" => $request->role_49_jorissen,
            // "role_dunvista_mansions" => $request->role_dunvista_mansions,
            // "role_wynton_joy" => $request->role_wynton_joy,
            // "role_126_siemert" => $request->role_126_siemert,
            // "role_ymca" => $request->role_ymca,
            "role_change_date" => $request->role_change_date,
            "role_change_bed" => $request->role_change_bed,
            "role_manage_users" => $request->role_manage_users,
            // "email_info" => $request->email_details,
            "role_nsfas_approval_upload" => $request->role_nsfas_approval_upload,
            "role_cancel_booking" => $request->role_cancel_booking,
            "role_update_booking_fee" => $request->role_update_booking_fee,
            "role_lookup" => $request->role_lookup,
            "role_comment" => $request->role_comment,
            "user_role_name" => $request->user_role_name,
            "role_cancel_lease" => $request->role_cancel_lease,
            "role_lease_regen" => $regen,
            "role_modify_approved" => $request->role_modify_approved !== '1' ? 0 :1,
            "role_non_funding" => $request->role_non_funding !== '1' ? 0 : 1,
            "role_funding" => $request->role_funding !== '1' ? 0 : 1,
            "role_app_lease" => $request->role_app_lease !== '1' ? 0 : 1,
            "template" => $request->template,
            "role_change_student_type" => $request->role_change_student_type,
            "role_view_booking_summary" => $request->role_view_booking_summary,
            "role_toggle_online_booking" => $request->role_toggle_online_booking,
            "role_change_building" => $request->role_change_building,
            // "role_change_building" => $request->role_change_building,
            "role_waive_fee" => $request->role_waive_fee,
            // "role_approve_waive" => $request->role_approve_waive,
            "role_bm_report" => $request->role_bm_report,
            "role_mda_report" => $request->role_mda_report,
            "role_all_apps_report" => $request->role_all_apps_report,
        ]);

        $allAccess = explode(",", $request->propertyAccess);

        foreach($allAccess as $access){
            if($access !== ""){
                PropertyPermission::create(['userUid'=> $newUser->id, "property" => $access]);
            }
        }

        // if($request->email_details == "1"){

            $userToken = UserResetToken::create(["userUid" => $newUser->id, "userToken" => $this->generateRandomLongString()]);

            $data['name'] = $request->firstName;
            $data['email'] = $request->email;
            $data['userToken'] = $userToken->userToken;
            $data['type'] = "registered";

            Mail::send('email.system_user_application', $data, function($message) use ($data) {
                $message->to($data['email'], $data['name'])->subject
                    ('Campus Africa User Registered');
                $message->from('noreply@campusafrica.co.za','Campus Africa');
            });

        // }
        

        return redirect()->route('users');
    }

    public function delete_lookup(Request $request){

        Lookups::where('id', $request->lookupId)->delete();

        return 'true';
    }

    public function delete_room(Request $request){
        RoomTypes::where('id', $request->roomId)->delete();

        return 'true';
    }

    public function set_page_session(Request $request){
        Session::put('page', $request->page);
    }

    public function set_page_session_order(Request $request){
        Session::put('sortId', $request->sortId);
        Session::put('sortDirection', $request->sortDirection);
    }

    public function app_filter(Request $request){
        
        $search_value = [];
        
        if ($request->firstLoad !== "1") {

            Session::put('property', $request->property);
            Session::put('room_type', $request->room_type);
            Session::put('applicant', $request->applicant);
            Session::put('studentNo', $request->studentNo);
            Session::put('idPassport', $request->idPassport);
            Session::put('bookingRef', $request->bookingRef);
            Session::put('student_type', $request->student_type);
            Session::put('fieldStatus', $request->fieldStatus);
            Session::put('status', $request->status);
            Session::put('date', $request->date);
            Session::put('beds', $request->beds);
            Session::put('processingStatus', $request->processingStatus);
            Session::put('show_cancelled', $request->show_cancelled);
            Session::put('applied', $request->applied);
            Session::put('gender', $request->gender);
            Session::put('email', $request->email);

        } else {

            $request->property = Session::get('property');
            $request->room_type = Session::get('room_type');
            $request->applicant = Session::get('applicant');
            $request->studentNo = Session::get('studentNo');
            $request->idPassport = Session::get('idPassport');
            $request->bookingRef = Session::get('bookingRef');
            $request->student_type = Session::get('student_type');
            $request->fieldStatus = Session::get('fieldStatus');
            $request->status = Session::get('status');
            $request->date = Session::get('date');
            $request->beds = Session::get('beds');
            $request->processingStatus = Session::get('processingStatus');
            $request->show_cancelled = Session::get('show_cancelled');
            $request->applied = Session::get('applied');
            $request->gender = Session::get('gender');
            $request->email = Session::get('email');

        }

        // if($request->processingStatus !== "" && !empty($request->processingStatus)){
        //     if($request->processingStatus !== "All" && $request->processingStatus !== "NA"){

        //         $processingStatus = 'u.overall_status LIKE "%'.$request->processingStatus.'%"';
        //         array_push($search_value, $processingStatus);
        //     }
        // }


        if($request->studentNo !== "" && !empty($request->studentNo)){
            $studentNo = 'ui.studentNo LIKE "%'.$request->studentNo.'%"';
            array_push($search_value, $studentNo);
        }

        if($request->idPassport !== "" && !empty($request->idPassport)){
            $idNo = '(ui.userId LIKE "%'.$request->idPassport.'%" OR ui.userPassport LIKE "%'.$request->idPassport.'%")';
            array_push($search_value, $idNo);
        }

        if($request->bookingRef !== "" && !empty($request->bookingRef)){
            $bookingRef = 'bookings_data.booking_code LIKE "%'.$request->bookingRef.'%"';
            array_push($search_value, $bookingRef);
        }

        if($request->beds !== "" && !empty($request->beds)){
            if($request->beds == "Allocated"){
                $beds = 'bookings_data.bed_code is not null';
                array_push($search_value, $beds);
            }

            if($request->beds == "Unallocated"){
                $beds = 'bookings_data.bed_code is null';
                array_push($search_value, $beds);
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

        // if($request->applied !== "" && !empty($request->applied)){
            if($request->applied !== "All" && $request->applied !== null){
                $applied = 'ui.app_source = "'.$request->applied.'"';
                array_push($search_value, $applied);
            }
        // }


        if($request->gender !== "All" && $request->gender !== null){
            
            if($request->gender !== "3"){
                $gender = 'ui.gender = "'.$request->gender.'"';
            }else{
                $gender = 'ui.gender is null';
            }
            
            array_push($search_value, $gender);
        }

        if($request->applicant !== "" && !empty($request->applicant)){
            $name = $request->applicant;
            $explodeName = explode(" ", $name);
            if(count($explodeName) > 1){
                $applicant = 'ui.userFirstName LIKE "%'.$explodeName[0].'%"';
                array_push($search_value, $applicant);

                $applicant = 'ui.userLastname LIKE "%'.$explodeName[1].'%"';
                array_push($search_value, $applicant);
            }else{
                $applicant = '(ui.userFirstName LIKE "%'.$request->applicant.'%" OR ui.userLastname LIKE "%'.$request->applicant.'%")';
                array_push($search_value, $applicant);

                // $applicantSurname = 'u.userLastname LIKE "%'.$request->applicant.'%"';
                // array_push($search_value, $applicantSurname);
            }
            
        }

        if($request->email !== "" && !empty($request->email)){
            $email = 'ui.userEmail LIKE "%'.$request->email.'%"';
            array_push($search_value, $email);
        }

        if($request->show_cancelled !== 'true'){
            $cancelled = '(ui.rob_60_cancelled = "0" OR ui.rob_60_cancelled is null)';
            array_push($search_value, $cancelled);
        }


        if($request->fieldStatus !== null && $request->fieldStatus !== "All"){

            $queryString = "";
            $i = 1;
            foreach($request->status as $id){
                
                if($i < count($request->status)){
                    $queryString .= "'".$id."',";
                }else{
                    $queryString .= "'".$id."'";
                }
                $i++;
            }

            

            if(!in_array("3", $request->status)){
                $value = '(ui.'.$request->fieldStatus.' in ('.$queryString.') AND (ui.rob_60_cancelled = "0" OR ui.rob_60_cancelled is null))';
            }else{
                $value = '(ui.'.$request->fieldStatus.' in ('.$queryString.') OR ui.rob_60_cancelled = "1")';
            }

            array_push($search_value, $value);

        }

        if($request->student_type !== "" && !empty($request->student_type)){
            if($request->student_type !== "All"){
                $student_type = 'ui.studentType LIKE "%'.$request->student_type.'%"';
                array_push($search_value, $student_type);
            }
        }

        //Get current booking year
        $this_year = Lookups::where('group_id', '4')->first();
        array_push($search_value, "bookings_data.year = '" . $this_year->lookup_name . "'");

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
                u.*, ui.*, bookings_data.*, p.residence as property_residence, r.name as roomTypeName
            FROM
                users u
                LEFT OUTER JOIN bookings_data on u.id = bookings_data.userId
                LEFT OUTER JOIN students_info ui on bookings_data.id = ui.bookingId
                LEFT OUTER JOIN property p on p.id = bookings_data.property
                LEFT OUTER JOIN room_types r on r.id = bookings_data.roomType
            WHERE u.userRole = 0 ' . implode($query);

            // dd($sql);

        //Get all of our students
        $searched_students = DB::select($sql);

        $userData = [];

        $allowed = [];
        $allProp = Residence::whereNull('status')->get();
        $permissionProp = PropertyPermission::where('userUid', Auth::user()->id)->get();

        foreach($allProp as $prop){
            foreach($permissionProp as $permission){
                if($permission->property == $prop->id){
                    array_push($allowed, $prop->id);
                }
            }
        }

        foreach($searched_students as $student) {

            $addUser = 1;
            $counter = 0;
            $itemCount = 0;

            $studentArrCheckList = [
                $student->rob_10_fee, 
                $student->rob_15_id, 
                $student->rob_20_card, 
                $student->rob_25_reg, 
                $student->rob_30_funds, 
                $student->rob_35_info, 
                $student->rob_40_bed, 
                $student->rob_45_other, 
                $student->rob_50_approve, 
                $student->rob_55_lease, 
            ];

            foreach($studentArrCheckList as $listItem){
                if($listItem == 1){
                    $counter += 25;
                    $itemCount ++;
                }

                if($listItem == 0){
                    $counter--;
                    $counter--;
                    $itemCount ++;
                }

                if($listItem == 2){
                    $counter++;
                    $itemCount ++;
                }

            }

            if($itemCount == 9 && $counter == 9 || $itemCount == 10 && $counter == 10){
                $counter = -50;
            }

            if($student->rob_60_cancelled == 1){
                $counter = -60;
            }
            
            //Only add users that pass the above checks
            if ($addUser == 1) {
                $student->counter = $counter;
                if(in_array($student->property, $allowed)){

                    array_push($userData, (array) $student);
                }
            }

        }   

        //Sort our data based on our weighted counter
        usort($userData, function($a, $b) {
            return $b['counter'] - $a['counter'];
        });  

        return json_encode([$userData, Session::get('sortDirection'), Session::get('sortId') , Session::get('page')]);
    }

    public function delete_booking(Request $request){

        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        Booking::where('id', $request->bookingId)->where('year', $this_year)->delete();
        StudentsInfo::where('userUid', $request->userId)->where('year', $this_year)->delete();

        $allFiles = UserFiles::where('userUid', $request->userId)->where('year', $this_year)->get();

        foreach($allFiles as $file){
            UserFiles::where('id', $file->id)->delete();
        }
        // User::where('id', $request->userId)->delete();

        return 'true';
    }

    public function cancel_booking(Request $request){
        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        // User::where('id', $request->userId)->update(['rob_60_cancelled' => 1, 'rob_40_bed' => 1, 'rob_50_approve' => 0]);
        StudentsInfo::where('userUid', $request->userId)->where('year', $this_year)->update(['rob_60_cancelled' => 1, 'rob_40_bed' => 1, 'rob_50_approve' => 0]);

        Booking::where('userId', $request->userId)->where('year', $this_year)->update(['property_data_id' => null, 'floor' => null, 'unit' => null, 'room' => null, 'bed' => null, 'bed_code' => null, 'room_rate' => null, 'cancelled' => 1]);

        Property::where('user_id_room', $request->userId)->update(["user_id_room" => null, "room_available" => null]);

        //Delete the Lease
        UserFiles::where('userUid', $request->userId)->where('fileId', 'moveIn')->where('year', $this_year)->delete();

        // $hasMda = UserFiles::where('userUid', $request->userId)->where('year', $this_year)->where('fileId', 'MDA')->first();
        
        // if(!empty($hasMda)){
        //     UserFiles::where('userUid', $userId)->where('year', $this_year)->where('fileId', 'MDA')->delete();
        // }

        if($request->reason){
            $cancelledReason = "Application Cancelled because: ".$request->reason;
        }else{
            $cancelledReason = "Application Cancelled";
        }

        ActionList::create([
            "userUid" => $request->userId,
            "action" => $cancelledReason,
            "actionUserUid" => Auth::user()->id,
            "actionUserDisplayName" => Auth::user()->userFirstName,
            "actionUserRole" => Auth::user()->userRole,
            "year" => $this_year,
        ]);

        return 'true';
    }

    public function restore_booking(Request $request){

        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        // User::where('id', $request->userId)->update(['rob_60_cancelled' => 0]);
        StudentsInfo::where('userUid', $request->userId)->where('year', $this_year)->update(['rob_60_cancelled' => 0]);

        Booking::where('userId', $request->userId)->where('year', $this_year)->update(['cancelled' => null]);

        ActionList::create([
            "userUid" => $request->userId,
            "action" => "Restored Cancelled Application",
            "actionUserUid" => Auth::user()->id,
            "actionUserDisplayName" => Auth::user()->userFirstName,
            "actionUserRole" => Auth::user()->userRole,
            "year" => $this_year,
        ]);

        return 'true';
    }

    public function reports(Request $request){

        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        $allowed = [];
        $allProp = Residence::whereNull('status')->get();
        $permissionProp = PropertyPermission::where('userUid', Auth::user()->id)->get();

        foreach($allProp as $prop){
            foreach($permissionProp as $permission){
                if($permission->property == $prop->id){
                    array_push($allowed, $prop);
                }
            }
        }

        $appYears = StudentsInfo::groupBy('year')->orderBy('year', 'desc')->get();


        return view('admin.reports', ["headerTitle" => "Reports", "title" => "Reports", "propertyList" => $allowed, "appYears" => $appYears]);
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

            $studentData = [$student->userFirstName, $student->userLastname, $student->userTitle, $student->userDob, $student->userCell, $student->userEmail, $student->userNationality, $student->userId, $student->userPassport, $student->studentType, $student->year, $lookupsInstit->lookup_name, $student->studentNo, $student->yearOfStudy, $lookupsFaculty->lookup_name,
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

        $reports = [
			['<b>First Name</b>', '<b>Last Name</b>','<b>Student Type</b>', '<b>Student Number</b>', '<b>NSFAS Approval</b>',],
		];

        foreach($searched_students as $studentData){
            $student = User::with('files')->where('id', $studentData->userId)->first();

            $approval = "";

            foreach($student->files as $file){
                if($file->fileId == "bursaryApproval"){
                    if($student->overall_status == "Rejected" || $student->overall_status == "Cancelled"){
                        $approval = "N/A";
                    }else{
                        $approval = $file->status;
                    }
                }
            }


            $fileInfo = [$student->userFirstName, $student->userLastname, $student->studentType, $student->studentNo, $approval];

            array_push($reports, $fileInfo);
        }

        $xlsx = new \Shuchkin\SimpleXLSXGen();
		$xlsx->addSheet( $users, 'Students');
        $xlsx->addSheet( $reports, 'NSFAS Approval');
		$xlsx->saveAs('Student Export.xlsx');

		return "true";
    }

    public function email_check(Request $request){
        $email = $request->email;
        $idNumber = $request->idNumber;

        $emailValid = true;
        $idNumberValid = true;
        $property = null;

        if($email !== ""){
            $user = $emailCheck = User::where('email', $email)->first();

            if(!empty($emailCheck)){
                $emailValid = false;
            }

            $user = StudentsInfo::where('userEmail', $email)->orderBy('year', 'desc')->first();
        }

        if($idNumber !== ""){
            $user = $idCheck = StudentsInfo::where('userId', $idNumber)->orderby('year', 'desc')->first();

            if(!empty($idCheck)){
                $idNumberValid = false;
                $booking = Booking::where('userId', $idCheck->userUid)->orderBy('year', 'desc')->first();
                $property = Residence::where('id', $booking->property)->pluck('residence')->first();
            }
        }
        
        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        $booking = Booking::where('userId', $user->userUid)->orderBy('year', 'desc')->first();

        return ["done", $emailValid, $idNumberValid, $user, $property, $booking, $this_year];
    }

    public function passport_check(Request $request){
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

    public function get_booking_details(Request $request){
        //

        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        $booking = Booking::with('residence')->with('roomType')->where('userId', $request->userId)->where('year', $this_year)->first();
        // $user = User::with('files')->where('id', $request->userId)->first();
        $userFiles = UserFiles::where('userUid', $request->userId)->where('year', $this_year)->get();
        $userInfo = StudentsInfo::where('bookingId', $booking->id)->first();
        $residence = Residence::where('residence', $booking->property)->first();

        //Get booking Meta
        $bookingPropData = Property::where('id', $booking->property_data_id)->first();

        $booking_paid = 'false';
        $booking_waived = 'false';

        foreach($userFiles as $file){

            if($file->fileId == "proof_of_payment"){
                
                if($file->fileUrl !== null && $file->fileUrl !== '' && $file->status !== "Rejected" && $file->status !== "Uploaded" && $file->status !== "AUploaded"){
                    $booking_paid = "true";
                    
                }else{
                    if($file->status == "XApproved" || $file->status == "Approved"){

                        $actions = ActionList::where('userUid', $request->userId)->where('year', $this_year)->orderBy('id', 'DESC')->get();

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

        $leaseApproved = 'false';


        foreach($userFiles as $userFile){
            if($userFile['fileId'] == "moveIn"){
                if($userFile['status'] == "Approved"){
                    $leaseApproved = "true";
                }
            }
        }

        return [$booking, $bookingPropData, $leaseApproved, $booking_paid, $booking_waived, $userInfo];
    }

    // public function change_booking_building(Request $request){
    //     $bookingId = $request->bookingId;
    //     $property = $request->property;
    //     $roomTypeNew = $request->roomType;

    //     $userLeaseStatus = User::where('id', $bookingId)->pluck('rob_55_lease')->first();
    //     $useApproveStatus = User::where('id', $bookingId)->pluck('rob_50_approve')->first();

    //     if($userLeaseStatus !== '0' && $useApproveStatus == '2'){
    //         User::where('id', $bookingId)->update(['rob_55_lease' => '0']);
    //         UserFiles::where('userUid', $bookingId)->where('fileId', 'moveIn')->update(['status' => 'Rejected']);
    //     }

    //     $oldData = Booking::where('userId', $bookingId)->first();
    //     $newData = ['property|Property' => $property, 'roomType|Room Type' => $roomTypeNew];

    //     $propertyOld = Property::where('user_id_room', $bookingId)->update(["user_id_room" => null, "room_available" => null]);

    //     foreach($newData as $key => $new){
            
    //         $info = explode("|", $key);
    //         $key = $info[0];
    //         $changedField = $info[1];

    //         if($oldData->$key !== $new){
    //             if($oldData->$key == "" || $oldData->$key == null){
    //                 $oldData->$key = "Unassigned";
    //             }
    //             ActionList::create([
    //                 "userUid" => $bookingId,
    //                 "action" => "Updated ".$changedField." From ".$oldData->$key." to ".$new,
    //                 "actionUserUid" => Auth::user()->id,
    //                 "actionUserDisplayName" => Auth::user()->userFirstName,
    //                 "actionUserRole" => Auth::user()->userRole,
    //             ]);
    //         }
    //     }

    //     $booking = Booking::where('userId', $bookingId)->first();
    //     $bookingYear = substr($oldData->year, -2);
    //     $bookingPrefix = Residence::where('id', $property)->pluck('building_prefix')->first();
    //     $id_booking = $booking->id;
    //     $placeholder = "";

    //     for($i = 0; $i < 5 - strlen($id_booking); $i++){
    //         $placeholder .= "0";
    //     }

    //     $bookingID = $placeholder.$id_booking;

    //     Booking::where('userId', $bookingId)->update(["property" => $property, "roomType" => $roomTypeNew, 'booking_code' => $bookingYear.$bookingPrefix.$bookingID, "property_data_id" => null, "floor" => null, "unit" => null, "room" => null, "bed" => null, "bed_code" => null, "room_rate" => null]);

    //     //Update the User robot
    //     User::where('id', $bookingId)->update(['rob_40_bed' => 0, 'rob_10_fee' => 0, 'rob_50_approve' => 0, 'rob_55_lease' => 3]);
    //     UserFiles::where('userUid', $bookingId)->where('fileId', 'proof_of_payment')->update(['status' => 'Rejected']);
    // }

    public function change_booking(Request $request){

        $bookingId = $request->bookingId;
        $property = $request->property;
        $roomTypeNew = $request->roomType;

        // $userLeaseStatus = User::where('id', $bookingId)->pluck('rob_55_lease')->first();
        // $useApproveStatus = User::where('id', $bookingId)->pluck('rob_50_approve')->first();

        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        $userInfo = StudentsInfo::where('userUid', $bookingId)->where('year', $this_year)->first();

        $userLeaseStatus = StudentsInfo::where('userUid', $bookingId)->where('year', $this_year)->pluck('rob_55_lease')->first();
        $useApproveStatus = StudentsInfo::where('userUid', $bookingId)->where('year', $this_year)->pluck('rob_50_approve')->first();

        // if($userLeaseStatus !== '0' && $useApproveStatus == '2'){
        //     User::where('id', $bookingId)->update(['rob_55_lease' => '0']);
        //     UserFiles::where('userUid', $bookingId)->where('fileId', 'moveIn')->update(['status' => 'Rejected']);
        // }

        if($request->floor == "Choose a Floor"){
            //Changing Only Property & Room Type

            $oldData = Booking::where('userId', $bookingId)->where('year', $this_year)->first();
            $oldData->roomType = RoomTypes::where('id', $oldData->roomType)->pluck('name')->first();
            $oldData->property = Residence::where('id', $oldData->property)->pluck('residence')->first();
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

                    if($changedField == "Property"){
                        $new = Residence::where('id', $new)->pluck('residence')->first();
                    }

                    if($changedField == "Room Type"){
                        $new = RoomTypes::where('id', $new)->pluck('name')->first();
                    }
                    ActionList::create([
                        "userUid" => $bookingId,
                        "action" => "Updated ".$changedField." From ".$oldData->$key." to ".$new,
                        "actionUserUid" => Auth::user()->id,
                        "actionUserDisplayName" => Auth::user()->userFirstName,
                        "actionUserRole" => Auth::user()->userRole,
                        "year" => $this_year,
                    ]);
                }
            }

            $booking = Booking::where('userId', $bookingId)->where('year', $this_year)->first();
            $bookingYear = substr($oldData->year, -2);
            $bookingPrefix = Residence::where('id', $property)->pluck('building_prefix')->first();
            $id_booking = $booking->id;
            $placeholder = "";

            for($i = 0; $i < 5 - strlen($id_booking); $i++){
                $placeholder .= "0";
            }

            $bookingID = $placeholder.$id_booking;
    
            Booking::where('userId', $bookingId)->where('year', $this_year)->update(["property" => $property, "roomType" => $roomTypeNew, 'booking_code' => $bookingYear.$bookingPrefix.$bookingID, "property_data_id" => null, "floor" => null, "unit" => null, "room" => null, "bed" => null, "bed_code" => null, "room_rate" => null]);

            //Update the User robot
            // User::where('id', $bookingId)->update(['rob_40_bed' => 1, 'rob_50_approve' => 0, 'rob_55_lease' => 3]);
            StudentsInfo::where('userUid', $bookingId)->where('year', $this_year)->update(['rob_40_bed' => 1, 'rob_50_approve' => 0, 'rob_55_lease' => 3]);

            $lookup = Lookups::where('group_id', '7')->first();
            $user = User::where('id', $bookingId)->first();
            $studentInfo = StudentsInfo::where('userUid', $bookingId)->where('year', $this_year)->first();
            $booking_data = [];

            $roomTypeNewId = $roomTypeNew;

            $property = Residence::where('id', $property)->pluck('residence')->first();
            $roomTypeNew = RoomTypes::where('id', $roomTypeNew)->pluck('name')->first();
            
            $roomTypeData = RoomTypes::where('id', $roomTypeNewId)->first();
            $roomTypeId = $roomTypeData->room_map_slug;
            $is_nsfas = false;
            if($roomTypeData->use_nsfas == 1){
                $is_nsfas = true;
            }

            $allResidences = Residence::whereNull('status')->get();
            $allRoomTypes = RoomTypes::get();
            $deposit = 0;
    
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

                        if($room->id == $roomTypeId){
                            $roomType = $roomTypeNew;
                            $roomName = $room->slug;

                            $appliesTo = $room->acf->booking_fee_applies_to;
                            $deposit = $room->acf->price_from;

                            if($room->acf->booking_fee_override !== ""){
                                $booking_data['room_rate'] = $room->acf->booking_fee_override;
                            }
        
                            if($room->slug == $roomName && $roomType == $roomTypeNew){
        
        
                                if($room->acf->booking_fee_override !== ""){
                                    $booking_data['room_rate'] = $room->acf->booking_fee_override;
                                }
        
                                if($studentInfo->studentType == "NSFAS"){
                                    if(!in_array("NSFAS Students", $appliesTo)){
                                        $booking_data['room_rate'] = "0";
                                        $actionFileData['action'] = "Booking Fee is R0";
                                    }
                                }
        
                                if($studentInfo->studentType == "Bursary"){
                                    if(!in_array("Bursary Students", $appliesTo)){
                                        $booking_data['room_rate'] = "0";
                                        $actionFileData['action'] = "Booking Fee is R0";
                                    }
                                }
        
                                if($studentInfo->studentType == "Cash / Direct"){
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

            if($roomTypeData->pull_price == null){
                $booking_data['room_rate'] = $roomTypeData->booking_price;
                $deposit = $roomTypeData->rent_price;
            
                if($studentInfo->studentType == "NSFAS"){
                    if($roomTypeData->apply_nsfas == null){
                        $booking_data['room_rate'] = "0";
                        $actionFileData['action'] = "Booking Fee is R0";
                    }
                }
            
                if($studentInfo->studentType == "Bursary"){
                    if($roomTypeData->apply_bursary == null){
                        $booking_data['room_rate'] = "0";
                        $actionFileData['action'] = "Booking Fee is R0";
                    }
                }
            
                if($studentInfo->studentType == "Cash / Direct"){
                    if($roomTypeData->apply_cash == null){
                        $booking_data['room_rate'] = "0";
                        $actionFileData['action'] = "Booking Fee is R0";
                    }
                }
            }

            if($booking_data['room_rate'] == "0"){

                $this_year = Lookups::where('group_id', '4')->first();
                $this_year = $this_year->lookup_name;
    
                $uploaded = UserFiles::where('userUid', $bookingId)->where('fileId', 'proof_of_payment')->where('year', $this_year)->first();

                $userData = StudentsInfo::where('userUid', $bookingId)->where('year', $this_year)->first();
    
                if(!$uploaded){
                    UserFiles::create([
                        'userUid' => $bookingId,
                        'fileId' => 'proof_of_payment',
                        'status' => 'Approved',
                        'year' => $this_year,
                    ]);
                    if($userData->rob_40_bed != 2){
                        StudentsInfo::where('userUid', $bookingId)->where('year', $this_year)->update(["rob_40_bed" => 1]);
                    }
                }else{
                    UserFiles::where('userUid', $bookingId)->where('fileId', 'proof_of_payment')->where('year', $this_year)->update([
                        'status' => 'Approved'
                    ]);
                    if($userData->rob_40_bed != 2){
                        StudentsInfo::where('userUid', $bookingId)->where('year', $this_year)->update(["rob_40_bed" => 1]);
                    }
                }
    
                Booking::where('userId', $bookingId)->where('year', $this_year)->update(['room_rate' => $booking_data['room_rate']]);
                // User::where('id', $bookingId)->update(['rob_10_fee' => 2]);
                StudentsInfo::where('userUid', $bookingId)->where('year', $this_year)->update(['rob_10_fee' => 2]);

                if($booking_data['room_rate'] != $booking->room_rate){

                    // $ts1 = strtotime($booking->lease_from_date);
                    // $ts2 = strtotime($booking->lease_to_date);
                    // $month1 = date('m', $ts1);
                    // $month2 = date('m', $ts2);
                    // $diff = $month2 - $month1;
                    $diff = 0;
                    for($i = new DateTime($booking->lease_from_date); $i <= new DateTime($booking->lease_to_date); $i->modify('+1 month')){
                        $diff++;
                    }
                    $yearly_rent = floatval($deposit) * floatval($diff);
                    $user = StudentsInfo::where('userUid', $bookingId)->where('year', $this_year)->first();

                    $monthly_rental = $deposit;
                    $nsfas_rate = null;

                    $institution = Lookups::where('id', $user->institutionStudy)->first();
                    // $funded = floatval($institution->nsfas_amount) * floatval($diff);
                    if($user->studentType == "NSFAS" || $user->nsfas_bursar == '1'){
                        $funded = floatval($institution->nsfas_amount) * floatval($diff);
                        $monthlyFunded = $institution->nsfas_amount;
                        $deposit = 0;

                        if($is_nsfas === true){
                            $monthly = $monthlyFunded;
                            $deposit = 0;
                            $admin_fee = 0;
                            $yearly_rent = $funded;
                            $topup = 0;
                            $monthly_rental = $monthlyFunded;
                            $nsfas_rate = 1;
                        }
                    }else{
                        $funded = $yearly_rent;
                        $monthlyFunded = $deposit;
                    }
    
                    $payment = BookingPayment::where('bookingId', $booking->id)->first();
    
                    BookingPayment::where('bookingId', $booking->id)->update(['booking_fee' => $booking_data['room_rate'], 'booking_fee_waived' => 0, 'rob_b_waived' => 0, 'deposit' => $deposit, 'deposit_waived' => 0, 'rob_deposit_waived' => 0, 'monthly_rental' => $monthly_rental, 'monthly_rental_total' => $yearly_rent, 'total_top_up' => $yearly_rent - $funded, 'funded_monthly' => $monthlyFunded, 'total_funded' => $funded, 'nsfas_rate' => $nsfas_rate ]);
                }

    
                $actionFileData['userUid'] = $bookingId;
                $actionFileData['action'] = "Booking Fee is R0";
                $actionFileData['actionUserUid'] = Auth::user()->id;
                $actionFileData['actionUserDisplayName'] = Auth::user()->userFirstName;
                $actionFileData['actionUserRole'] = Auth::user()->userRole;
                $actionFileData['year'] = $this_year;
            
                ActionList::create($actionFileData);

                

            }else{
                // UserFiles::where('userUid', $bookingId)->where('fileId', 'proof_of_payment')->update([
                //     'status' => 'Rejected'
                // ]);

                $this_year = Lookups::where('group_id', '4')->first();
                $this_year = $this_year->lookup_name;

                $fileUploaded = UserFiles::where('userUid', $bookingId)->where('fileId', 'proof_of_payment')->where('year', $this_year)->first();

                if($fileUploaded && $fileUploaded->fileUrl !== null){
                    if($oldData->room_rate !== $booking_data['room_rate']){

                        // UserFiles::where('userUid', $bookingId)->where('fileId', 'proof_of_payment')->update([
                        //     'status' => 'Uploaded'
                        // ]);
                        // User::where('id', $bookingId)->update(['rob_10_fee' => 1]);

                        UserFiles::where('userUid', $bookingId)->where('fileId', 'proof_of_payment')->where('year', $this_year)->update([
                            'status' => 'Rejected'
                        ]);

                        $userData = StudentsInfo::where('userUid', $bookingId)->where('year', $this_year)->first();
                        if($userData->rob_40_bed != 2){
                            StudentsInfo::where('userUid', $bookingId)->where('year', $this_year)->update(['rob_40_bed' => 0]);
                        }
                        // User::where('id', $bookingId)->update(['rob_10_fee' => 0]);
                        StudentsInfo::where('userUid', $bookingId)->where('year', $this_year)->update(['rob_10_fee' => 0]);
                    }
                }else{
                    UserFiles::where('userUid', $bookingId)->where('fileId', 'proof_of_payment')->where('year', $this_year)->update([
                        'status' => 'Rejected'
                    ]);
                    // User::where('id', $bookingId)->update(['rob_10_fee' => 0]);
                    $userData = StudentsInfo::where('userUid', $bookingId)->where('year', $this_year)->first();
                    if($userData->rob_40_bed != 2){
                        StudentsInfo::where('userUid', $bookingId)->where('year', $this_year)->update(['rob_40_bed' => 0]);
                    }
                    StudentsInfo::where('userUid', $bookingId)->where('year', $this_year)->update(['rob_10_fee' => 0]);
                }

                //Recalculate booking status
                // User::where('id', $bookingId)->update([
                //     'bookingStatus' => 'Action'
                // ]);

                StudentsInfo::where('userUid', $bookingId)->where('year', $this_year)->update([
                    'bookingStatus' => 'Action'
                ]);
    
                Booking::where('userId', $bookingId)->where('year', $this_year)->update(['room_rate' => $booking_data['room_rate']]);

                // if($booking_data['room_rate'] != $booking->room_rate){

                    // $ts1 = strtotime($booking->lease_from_date);
                    // $ts2 = strtotime($booking->lease_to_date);
                    // $month1 = date('m', $ts1);
                    // $month2 = date('m', $ts2);
                    // $diff = $month2 - $month1;
                    // $yearly_rent = floatval($deposit) * floatval($diff);
                    $diff = 0;
                    for($i = new DateTime($booking->lease_from_date); $i <= new DateTime($booking->lease_to_date); $i->modify('+1 month')){
                        $diff++;
                    }
                    $yearly_rent = floatval($deposit) * floatval($diff);
                    $user = StudentsInfo::where('userUid', $bookingId)->where('year', $this_year)->first();
                    $nsfas_rate = null;
                    $monthly_rental = $deposit;

                    $institution = Lookups::where('id', $user->institutionStudy)->first();
                    // $funded = floatval($institution->nsfas_amount) * floatval($diff);
                    if($user->studentType == "NSFAS" || $user->nsfas_bursar == '1'){
                        $funded = floatval($institution->nsfas_amount) * floatval($diff);
                        $monthlyFunded = $institution->nsfas_amount;
                        $deposit = 0;

                        if($is_nsfas === true){
                            $monthly = $monthly_rental = $monthlyFunded;
                            $deposit = 0;
                            $admin_fee = 0;
                            $nsfas_rate = 1;
                            $yearly_rent = $funded;
                            $topup = 0;
                            $nsfas_rate = 1;
                        }
                    }else{
                        $funded = $yearly_rent;
                        $monthlyFunded = $monthly_rental = $deposit;
                    }

                    $payment = BookingPayment::where('bookingId', $booking->id)->first();

                    BookingPayment::where('bookingId', $booking->id)->update(['booking_fee' => $booking_data['room_rate'], 'booking_fee_waived' => 0, 'rob_b_waived' => 0, 'deposit' => $deposit, 'deposit_waived' => 0, 'rob_deposit_waived' => 0, 'monthly_rental' => $monthly_rental, 'monthly_rental_total' => $yearly_rent, 'total_top_up' => $yearly_rent - $funded, 'funded_monthly' => $monthlyFunded, 'total_funded' => $funded, 'nsfas_rate' => $nsfas_rate]);
                // }
    
                $actionFileData['userUid'] = $bookingId;
                $actionFileData['action'] = "Booking Fee updated to R".$booking_data['room_rate'];
                $actionFileData['actionUserUid'] = Auth::user()->id;
                $actionFileData['actionUserDisplayName'] = Auth::user()->userFirstName;
                $actionFileData['actionUserRole'] = Auth::user()->userRole;
                $actionFileData['year'] = $this_year;
            
                ActionList::create($actionFileData);
            }

            if($studentInfo->rob_60_cancelled == '1'){
                $this_year = Lookups::where('group_id', '4')->first();
                $this_year = $this_year->lookup_name;

                // User::where('id', $bookingId)->update(['rob_60_cancelled' => 0]);
                StudentsInfo::where('userUid', $bookingId)->where('year', $this_year)->update(['rob_60_cancelled' => 0]);

                Booking::where('userId', $bookingId)->where('year', $this_year)->update(['cancelled' => null]);

                ActionList::create([
                    "userUid" => $bookingId,
                    "action" => "Restored Cancelled Application",
                    "actionUserUid" => Auth::user()->id,
                    "actionUserDisplayName" => Auth::user()->userFirstName,
                    "actionUserRole" => Auth::user()->userRole,
                    "year" => $this_year,
                ]);
            }

            $this->update_user_status($bookingId);

        } else {

            //Get current booking year
            $this_year = Lookups::where('group_id', '4')->first();

            $newProperty = Residence::where('id', $property)->pluck('residence')->first();
            $newRoom = RoomTypes::where('id', $roomTypeNew)->pluck('name')->first();

            $oldData = Booking::where('userId', $bookingId)->where('year', $this_year->lookup_name)->first();
            $oldData->roomId = $oldData->roomType;
            $oldData->roomType = RoomTypes::where('id', $oldData->roomType)->pluck('name')->first();
            $oldData->property = Residence::where('id', $oldData->property)->pluck('residence')->first();

            $newData = ['property|Property' => $newProperty, 'roomType|Room Type' => $newRoom, "floor|Floor" => $request->floor, "unit|Unit" => $request->unit, "bed_code|Bed Code"=> $request->bed_code];
    
            $propertyOld = Property::where('user_id_room', $bookingId)->update(["user_id_room" => null, "room_available" => null]);
    
            Property::where('residence', $property)->where('room_type', $roomTypeNew)->where("floor", $request->floor)->where('unit', $request->unit)->where('bed_code', $request->bed_code)->update(["room_available" => "1", "user_id_room"=>$bookingId]);
    
            $propertyDataAll = Property::where('residence', $property)->where('room_type', $roomTypeNew)->where("floor", $request->floor)->where('unit', $request->unit)->where('bed_code', $request->bed_code)->get();
            $propertyData = "";

            //Find the correct bed
            foreach($propertyDataAll as $pi) {
                if ($pi->year <= $pi->year_end || $pi->year_end == 0) {
                    if (($this_year->lookup_name >= $pi->year && $this_year->lookup_name <= $pi->year_end) || ($this_year->lookup_name >= $pi->year && $pi->year_end == 0)) {
                        $propertyData = $pi;
                    }
                }
            }    
    
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
                        "year" => $this_year->lookup_name,
                    ]);
                }
            }

            $bookingYear = substr($oldData->year, -2);
            $bookingPrefix = Residence::where('id', $property)->pluck('building_prefix')->first();
            $id_booking = $oldData->id;
            $placeholder = "";

            for($i = 0; $i < 5 - strlen($id_booking); $i++){
                $placeholder .= "0";
            }

            $bookingID = $placeholder.$id_booking;
    
            Booking::where('userId', $bookingId)->where('year', $this_year->lookup_name)->update(["property" => $property, "roomType" => $roomTypeNew, "floor" => $request->floor, "unit" => $request->unit, "room" => $request->room, "bed" => $request->bed, "bed_code"=> $request->bed_code, "property_data_id" => $propertyData->id, 'booking_code' => $bookingYear.$bookingPrefix.$bookingID]);

            //Update the User robot
            // User::where('id', $bookingId)->update(['rob_40_bed' => 2]);
            StudentsInfo::where('userUid', $bookingId)->where('year', $this_year->lookup_name)->update(['rob_40_bed' => 2]);
            $booking = Booking::where('userId', $bookingId)->where('year', $this_year->lookup_name)->first();

            $user = StudentsInfo::where('userUid', $bookingId)->where('year', $this_year->lookup_name)->first();


            if($oldData->roomId !== $booking->roomType){

                $lookup = Lookups::where('group_id', '7')->first();
                $user = StudentsInfo::where('userUid', $bookingId)->where('year', $this_year->lookup_name)->first();
                $booking_data = [];

                $roomTypeNewId = $roomTypeNew;
    
                $property = Residence::where('id', $property)->pluck('residence')->first();
                $roomTypeNew = RoomTypes::where('id', $roomTypeNew)->pluck('name')->first();

                $roomTypeData = RoomTypes::where('id', $roomTypeNewId)->first();
                $roomTypeId = $roomTypeData->room_map_slug;
                $is_nsfas = false;
                if($roomTypeData->use_nsfas == 1){
                    $is_nsfas = true;
                }
    
                $allResidences = Residence::whereNull('status')->get();
                $allRoomTypes = RoomTypes::get();
                $deposit = 0;
        
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

                            if($room->id == $roomTypeId){
                                $roomType = $roomTypeNew;
                                $roomName = $room->slug;
                                $appliesTo = $room->acf->booking_fee_applies_to;
                                $deposit = $room->acf->price_from;

                                if($room->acf->booking_fee_override !== ""){
                                    $booking_data['room_rate'] = $room->acf->booking_fee_override;
                                }
            
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
                }

                if($roomTypeData->pull_price == null){
                    $booking_data['room_rate'] = $roomTypeData->booking_price;
                    $deposit = $roomTypeData->rent_price;
                
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
        
                if($booking_data['room_rate'] == "0"){

                    $this_year = Lookups::where('group_id', '4')->first();
                    $this_year = $this_year->lookup_name;
        
                    $uploaded = UserFiles::where('userUid', $bookingId)->where('fileId', 'proof_of_payment')->where('year', $this_year)->first();

                    $userData = StudentsInfo::where('userUid', $bookingId)->where('year', $this_year)->first();
                
        
                    if(!$uploaded){
                        UserFiles::create([
                            'userUid' => $bookingId,
                            'fileId' => 'proof_of_payment',
                            'status' => 'Approved',
                            'year' => $this_year,
                        ]);
                        if($userData->rob_40_bed != 2){
                            StudentsInfo::where('userUid', $bookingId)->where('year', $this_year)->update(["rob_40_bed" => 1]);
                        }
                    }else{
                        UserFiles::where('userUid', $bookingId)->where('fileId', 'proof_of_payment')->where('year', $this_year)->update([
                            'status' => 'Approved'
                        ]);
                        if($userData->rob_40_bed != 2){
                            StudentsInfo::where('userUid', $bookingId)->where('year', $this_year)->update(["rob_40_bed" => 1]);
                        }
                    }
        
                    Booking::where('userId', $bookingId)->where('year', $this_year)->update(['room_rate' => $booking_data['room_rate']]);
                    // User::where('id', $bookingId)->update(['rob_10_fee' => 2]);
                    StudentsInfo::where('userUid', $bookingId)->where('year', $this_year)->update(['rob_10_fee' => 2]);

                    // if($booking_data['room_rate'] != $booking->room_rate){

                        // $ts1 = strtotime($booking->lease_from_date);
                        // $ts2 = strtotime($booking->lease_to_date);
                        // $month1 = date('m', $ts1);
                        // $month2 = date('m', $ts2);
                        // $diff = $month2 - $month1;
                        // $yearly_rent = floatval($deposit) * floatval($diff);

                        $diff = 0;
                        for($i = new DateTime($booking->lease_from_date); $i <= new DateTime($booking->lease_to_date); $i->modify('+1 month')){
                            $diff++;
                        }
                        $yearly_rent = floatval($deposit) * floatval($diff);
                        $user = StudentsInfo::where('userUid', $booking->userId)->where('year', $this_year)->first();
                        $nsfas_rate = null;

                        $institution = Lookups::where('id', $user->institutionStudy)->first();
                        if($user->studentType == "NSFAS" || $user->nsfas_bursar == '1'){
                            $funded = floatval($institution->nsfas_amount) * floatval($diff);
                            $monthlyFunded = $monthly = $institution->nsfas_amount;
                            $deposit = 0;

                            if($is_nsfas === true){
                                $monthly = $monthlyFunded;
                                $deposit = 0;
                                $admin_fee = 0;
                                $yearly_rent = $funded;
                                $topup = 0;
                                $nsfas_rate = 1;
                            }
                        }else{
                            $funded = $yearly_rent;
                            $monthlyFunded = $monthly = $deposit;
                        }

                        $payment = BookingPayment::where('bookingId', $booking->id)->first();

                        BookingPayment::where('bookingId', $booking->id)->update(['booking_fee' => $booking_data['room_rate'], 'booking_fee_waived' => 0, 'rob_b_waived' => 0, 'deposit' => $deposit, 'deposit_waived' => 0, 'rob_deposit_waived' => 0, 'monthly_rental' => $monthly, 'monthly_rental_total' => $yearly_rent, 'total_top_up' => $yearly_rent - $funded, 'funded_monthly' => $monthlyFunded, 'total_funded' => $funded, 'nsfas_rate' => $nsfas_rate]);
                    // }
        
                    $actionFileData['userUid'] = $bookingId;
                    $actionFileData['action'] = "Booking Fee is R0";
                    $actionFileData['actionUserUid'] = Auth::user()->id;
                    $actionFileData['actionUserDisplayName'] = Auth::user()->userFirstName;
                    $actionFileData['actionUserRole'] = Auth::user()->userRole;
                    $actionFileData['year'] = $this_year;
                
                    ActionList::create($actionFileData);
                }else{
                    // UserFiles::where('userUid', $bookingId)->where('fileId', 'proof_of_payment')->update([
                    //     'status' => 'Rejected'
                    // ]);

                    $this_year = Lookups::where('group_id', '4')->first();
                    $this_year = $this_year->lookup_name;

                    $fileUploaded = UserFiles::where('userUid', $bookingId)->where('fileId', 'proof_of_payment')->where('year', $this_year)->first();
    
                    if($fileUploaded && $fileUploaded->fileUrl !== null){
                        if($oldData->room_rate != $booking_data['room_rate']){
    
                            UserFiles::where('userUid', $bookingId)->where('fileId', 'proof_of_payment')->where('year', $this_year)->update([
                                'status' => 'Uploaded'
                            ]);
                            // User::where('id', $bookingId)->update(['rob_10_fee' => 1]);
                            StudentsInfo::where('userUid', $bookingId)->where('year', $this_year)->update(['rob_10_fee' => 1]);
                        }
                    }else{
                        UserFiles::where('userUid', $bookingId)->where('fileId', 'proof_of_payment')->where('year', $this_year)->update([
                            'status' => 'Rejected'
                        ]);
                        // User::where('id', $bookingId)->update(['rob_10_fee' => 0]);
                        $userData = StudentsInfo::where('userUid', $bookingId)->where('year', $this_year)->first();
                        if($userData->rob_40_bed != 2){
                            StudentsInfo::where('userUid', $bookingId)->where('year', $this_year)->update(['rob_40_bed' => 0]);
                        }
                        StudentsInfo::where('userUid', $bookingId)->where('year', $this_year)->update(['rob_10_fee' => 0]);
                    }
    
                    //Recalculate booking status
                    // User::where('id', $bookingId)->update([
                    //     'bookingStatus' => 'Action'
                    // ]);
                    StudentsInfo::where('userUid', $bookingId)->where('year', $this_year)->update([
                        'bookingStatus' => 'Action'
                    ]);
        
                    Booking::where('userId', $bookingId)->where('year', $this_year)->update(['room_rate' => $booking_data['room_rate']]);

                    // if($booking_data['room_rate'] != $booking->room_rate){

                        // $ts1 = strtotime($booking->lease_from_date);
                        // $ts2 = strtotime($booking->lease_to_date);
                        // $month1 = date('m', $ts1);
                        // $month2 = date('m', $ts2);
                        // $diff = $month2 - $month1;
                        // $yearly_rent = floatval($deposit) * floatval($diff);

                        $diff = 0;
                        for($i = new DateTime($booking->lease_from_date); $i <= new DateTime($booking->lease_to_date); $i->modify('+1 month')){
                            $diff++;
                        }
                        $yearly_rent = floatval($deposit) * floatval($diff);
                        $user = StudentsInfo::where('userUid', $booking->userId)->where('year', $this_year)->first();
                        $nsfas_rate = null;

                        $institution = Lookups::where('id', $user->institutionStudy)->first();
                        // $funded = floatval($institution->nsfas_amount) * floatval($diff);
                        if($user->studentType == "NSFAS" || $user->nsfas_bursar == '1'){
                            $funded = floatval($institution->nsfas_amount) * floatval($diff);
                            $monthlyFunded = $institution->nsfas_amount;
                            $monthly = $deposit;
                            $deposit = 0;

                            if($is_nsfas === true){
                                $monthly = $monthlyFunded;
                                $deposit = 0;
                                $admin_fee = 0;
                                $yearly_rent = $funded;
                                $topup = 0;
                                $nsfas_rate = 1;
                            }
                        }else{
                            $funded = $yearly_rent;
                            $monthlyFunded = $monthly = $deposit;
                        }

                        $payment = BookingPayment::where('bookingId', $booking->id)->first();

                        BookingPayment::where('bookingId', $booking->id)->update(['booking_fee' => $booking_data['room_rate'], 'booking_fee_waived' => 0, 'rob_b_waived' => 0, 'deposit' => $deposit, 'deposit_waived' => 0, 'rob_deposit_waived' => 0, 'monthly_rental' => $monthly, 'monthly_rental_total' => $yearly_rent, 'total_top_up' => $yearly_rent - $funded, 'funded_monthly' => $monthlyFunded, 'total_funded' => $funded, 'nsfas_rate' => $nsfas_rate]);
                    // }
        
                    // if($booking_data['room_rate'] != $booking->room_rate){

                    //     // $ts1 = strtotime($booking->lease_from_date);
                    //     // $ts2 = strtotime($booking->lease_to_date);
                    //     // $month1 = date('m', $ts1);
                    //     // $month2 = date('m', $ts2);
                    //     // $diff = $month2 - $month1;
                    //     // $yearly_rent = floatval($deposit) * floatval($diff);

                    //     $diff = 0;
                    //     for($i = new DateTime($booking->lease_from_date); $i <= new DateTime($booking->lease_to_date); $i->modify('+1 month')){
                    //         $diff++;
                    //     }
                    //     $yearly_rent = floatval($deposit) * floatval($diff);
                    //     $user = StudentsInfo::where('userUid', $bookingId)->where('year', $this_year)->first();

                    //     $institution = Lookups::where('id', $user->institutionStudy)->first();
                    //     // $funded = floatval($institution->nsfas_amount) * floatval($diff);
                    //     if($user->studentType == "NSFAS" || $user->nsfas_bursar == '1'){
                    //         $funded = floatval($institution->nsfas_amount) * floatval($diff);
                    //         $monthlyFunded = $institution->nsfas_amount;
                    //     }else{
                    //         $funded = $yearly_rent;
                    //         $monthlyFunded = $deposit;
                    //     }

                    //     $payment = BookingPayment::where('bookingId', $booking->id)->first();

                    //     BookingPayment::where('bookingId', $booking->id)->update(['booking_fee' => $booking_data['room_rate'], 'booking_fee_waived' => 0, 'rob_b_waived' => 0, 'deposit' => $deposit, 'deposit_waived' => 0, 'rob_deposit_waived' => 0, 'monthly_rental' => $deposit, 'monthly_rental_total' => $yearly_rent, 'total_top_up' => $yearly_rent - $funded, 'funded_monthly' => $monthlyFunded, 'total_funded' => $funded]);
                    // }
        
                    $actionFileData['userUid'] = $bookingId;
                    $actionFileData['action'] = "Booking Fee updated to R".$booking_data['room_rate'];
                    $actionFileData['actionUserUid'] = Auth::user()->id;
                    $actionFileData['actionUserDisplayName'] = Auth::user()->userFirstName;
                    $actionFileData['actionUserRole'] = Auth::user()->userRole;
                    $actionFileData['year'] = $this_year;
                
                    ActionList::create($actionFileData);
                }
            }
    
    
        }

        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        $status_data = StudentsInfo::where('userUid', $bookingId)->where('year', $this_year)->first();

        if($status_data->rob_10_fee == 2 && $status_data->rob_15_id == 2 && $status_data->rob_20_card == 2 && $status_data->rob_25_reg == 2 && $status_data->rob_30_funds == 2 && $status_data->rob_35_info == 2 && $status_data->rob_40_bed == 2 && ($status_data->rob_45_other == 3 || $status_data->rob_45_other == 2) && $status_data->rob_50_approve == 0){
            // User::where('id', $bookingId)->update(['rob_50_approve' => 1]);
            StudentsInfo::where('userUid', $bookingId)->where('year', $this_year)->update(['rob_50_approve' => 1]);
        }


        if($request->change_bed_type == "cancel"){

            $this_year = Lookups::where('group_id', '4')->first();
            $this_year = $this_year->lookup_name;

            // User::where('id', $bookingId)->update(['rob_55_lease' => '0']);
            StudentsInfo::where('userUid', $bookingId)->where('year', $this_year)->update(['rob_55_lease' => '0']);
            UserFiles::where('userUid', $bookingId)->where('fileId', 'moveIn')->where('year', $this_year)->update(['status' => 'Rejected']);
    
            // $hasMda = UserFiles::where('userUid', $bookingId)->where('year', $this_year)->where('fileId', 'MDA')->first();
        
            // if(!empty($hasMda)){
            //     UserFiles::where('userUid', $bookingId)->where('year', $this_year)->where('fileId', 'MDA')->delete();
            // }
    
            ActionList::create([
                "userUid" => $bookingId,
                "action" => "Rejected Approved Lease to Change Bed",
                "actionUserUid" => Auth::user()->id,
                "actionUserDisplayName" => Auth::user()->userFirstName,
                "actionUserRole" => Auth::user()->userRole,
                "year" => $this_year,
            ]);
        }



        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;
        


        $this->update_user_status($bookingId);
        return ["true", UserFiles::where('userUid', $bookingId)->where('year', $this_year)->where('fileId', 'proof_of_payment')->first()];
    }

    public function change_lease(Request $request){

        $bookingId = $request->bookingId;
        
        $lease_from_date = $request->lease_from_date;
        $lease_from_time = $request->lease_from_time;
        $lease_to_date = $request->lease_to_date;
        $lease_to_time = $request->lease_to_time;    
        
        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        $oldData = Booking::where('userId', $bookingId)->where('year', $this_year)->first();
        $newData = ['lease_from_date|Lease From Date' => $lease_from_date, 'lease_from_time|Lease From Time' => $lease_from_time, "lease_to_date|Lease To Date" => $request->lease_to_date, "lease_to_time|Lease To Time" => $request->lease_to_time];

        Booking::where('userId', $bookingId)->where('year', $this_year)->update(["lease_from_date" => $lease_from_date, "lease_from_time"=>$lease_from_time, "lease_to_date"=>$lease_to_date, "lease_to_time"=>$lease_to_time, "lease_unique" => "1"]);

        $roomTypeData = RoomTypes::where('id', $oldData->roomType)->first();
        $is_nsfas = false;
        if($roomTypeData->use_nsfas == 1){
            $is_nsfas = true;
        }

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
                    "year" => $this_year,
                ]);
            }
        }

        $paymentBooking = BookingPayment::where('bookingId', $oldData->id)->first();
        $userInfo = StudentsInfo::where('bookingId', $oldData->id)->where('year', $this_year)->first();

        // $ts1 = strtotime($lease_from_date);
        // $ts2 = strtotime($lease_to_date);
        // $month1 = date('m', $ts1);
        // $month2 = date('m', $ts2);
        // $diff = $month2 - $month1;
        $diff = 0;
        for($i = new DateTime($lease_from_date); $i <= new DateTime($lease_to_date); $i->modify('+1 month')){
            $diff++;
        }

        $yearly_rent = floatval($paymentBooking->monthly_rental) * floatval($diff);

        $institution = Lookups::where('id', $userInfo->institutionStudy)->first();
        $funded = floatval($institution->nsfas_amount) * floatval($diff);
        
        $topup = floatval($yearly_rent) - floatval($funded);
        $monthlyFunded = $institution->nsfas_amount;

        if(($userInfo->studentType == "Cash / Direct") || ($userInfo->studentType == "Bursary" && $userInfo->nsfas_bursar !== 1)){
            $funded = $yearly_rent;
            $topup = '0';
            $monthlyFunded = $paymentBooking->monthly_rental;
        }else{
            $funded = $funded;
            $topup = $topup;
            if($is_nsfas === true){
                $topup = 0;
            }
        }

        BookingPayment::where('bookingId', $oldData->id)->update(["monthly_rental_total" => $yearly_rent, "total_funded" => $funded, "total_top_up" => $topup, "funded_monthly" => $monthlyFunded]);

        return "true";
    }

    public function manage_properties(Request $request){

        if(Auth::user()->role_lookup == 1){
            //Get current booking year
            $this_year = Lookups::where('group_id', '4')->first();
            $this_year = $this_year->lookup_name;

            $allowed = [];
            $allProp = Residence::whereNull('status')->get();
            // $permissionProp = PropertyPermission::where('userUid', Auth::user()->id)->get();

            foreach($allProp as $prop){
                // foreach($permissionProp as $permission){
                    // if($permission->property == $prop->id){
                        array_push($allowed, $prop);
                    // }
                // }
            }

            return view('admin.manage_properties', ["headerTitle" => "Manage Properties", "title" => "Manage Properties", "this_year" => $this_year, "propertyList" => $allowed]);
        }else{
            return "404 Error";
        }

        
    }

    public function property_edit(Request $request, $propertyId){
        // $propertyId

        if(Auth::user()->role_lookup == 1){
            //Get current booking year
            $this_year = Lookups::where('group_id', '4')->first();
            $this_year = $this_year->lookup_name;

            if($propertyId == "0"){
                $head = "Add";
                $propertyData = "";

                $bookings = [];
                
            }else{
                $head = "Edit";
                $propertyData = Property::where('id', $propertyId)->first();

                $lookup = Lookups::where('group_id', '4')->first();

                //Get bookings tied to this bed
                $bookings = Booking::where('property_data_id', $propertyId)->where('year', $lookup->lookup_name)->with('users')->get();            
            }

            $Residence = Residence::whereNull('status')->get();

            return view('admin.property_edit', ["headerTitle" => $head." Properties", "title" => $head." Properties", "propertyData" => $propertyData, "bookingData" => $bookings, "this_year" => $this_year, "Residence" => $Residence]);
        }else{
            return "404 Error";
        }

        
    }

    public function save_property (Request $request){
        //Get current booking year
        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        $propertyId = $request->propertyId;

        //Get current property data
        $current_data = Property::where('id', $propertyId)->first();

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
            "room_rate" => $request->room_rate,            
        ];

        if (isset($current_data->room_available)) {
            $data['room_available'] = $current_data->room_available;
            $data['user_id_room'] = $current_data->user_id_room;
        }

        if ($request->submit_type == "current") {
            $data['year'] = $this_year;
            $data['year_end'] = isset($current_data->year_end) && $current_data->year_end == 0 ? 0 : $this_year;

        } else {
            $data['year'] = $this_year + 1;
            $data['year_end'] = 0;
        }

        //Duplicate check
        if ((isset($current_data->year) && $current_data->year <> $data['year']) || $propertyId == "0") {
            $filter_data = [
                "residence" => $request->residence,
                "floor" => $request->floor, 
                "unit" => $request->unit, 
                "room" => $request->room, 
                "bed" => $request->bed, 
                "year" => $data['year']
            ];
            $duplicate_data = Property::where($filter_data)->get();

            $found = 0;
            foreach($duplicate_data as $dd) {
                if ($dd->year_end == 0 || $dd->year_end >= $dd->year) {
                    $found++;
                }
            }

            if ($found > 0) {
                return 'A bed for next year already exists';
            }
        }

        if($propertyId == "0") {
            $data['year_end'] = 0;   
            $data['room_available'] = null;
            Property::create($data);
        }else{
            //First delete the old one
            if ($request->submit_type == "current") {
                Property::where('id', $propertyId)->update(["year_end" => $this_year - 1]);
            } else {
                if ($current_data->year == $this_year) {
                    Property::where('id', $propertyId)->update(["year_end" => $this_year]);
                } else {
                    Property::where('id', $propertyId)->update(["year_end" => $this_year - 1]);
                }
            }    

            //Now add the new one
            $new_id = Property::create($data)->id;

            //Update bookings with this bed
            if ($request->submit_type == "current") {
                $bookings = Booking::where('property_data_id', $propertyId)->with('users')->get();
                
                foreach($bookings as $b) {
                    $booking_data = [
                        "floor" => $request->floor,
                        "unit" => $request->unit,
                        "room" => $request->room,
                        "bed" => $request->bed,
                        "bed_code" => $request->bed_code,
                        "roomType" => $request->roomType,
                        "property" => $request->residence,
                        "property_data_id" => $new_id
                    ];

                    Booking::where('id', $b->id)->update($booking_data);
                }
            }
        }

        return "done";
    }
    
    public function delete_bed (Request $request) {

        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        $property = Property::where('id', $request->propertyId)->first();
        
        if($property->year == $this_year){
            Property::where('id', $request->propertyId)->update(['year_end' => $property->year - 1]);
        }else{
            Property::where('id', $request->propertyId)->update(['year_end' => $property->year]);
        }


        return "done";
    }

    public function check_rooms($bookingId){

        $property = Property::get();
        $this_year = Lookups::where('group_id', '4')->first();
        $searchable_rooms = [];

        foreach($property as $prop){
            if($prop->year_end == 0 || $prop->year_end >= $this_year){
                if(!in_array($prop->room_type, $searchable_rooms)){
                    array_push($searchable_rooms, $prop->room_type);
                }
            }
        }

        $allRooms = RoomTypes::where('name', '!=', 'Building Manager')->whereIn('id', $searchable_rooms)->pluck('id');

        $allowedProps = PropertyPermission::where('userUid', Auth::user()->id)->pluck('property');

        $propertyInfo = Property::whereNull('room_available')->whereIn('room_type', $allRooms)->whereIn('residence', $allowedProps)->groupBy('residence')->get();
        $propertyInfoRooms = Property::with('roomType')->whereNull('room_available')->whereIn('room_type', $allRooms)->whereIn('residence', $allowedProps)->groupBy('room_type')->get();
        $property = [];

        foreach($propertyInfo as $pi){
            if(Residence::where('id', $pi->residence)->pluck('status')->first() != '1'){
                
                array_push($property, Residence::where('id', $pi->residence)->first());
            }
        }

        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        $booking = Booking::where('userId', $bookingId)->where('year', $this_year)->first();

        $userInfo = StudentsInfo::where('bookingId', $booking->id)->first();
        // $inSelected = InstitutionProperties::where("institution_id", $userInfo->institutionStudy)->pluck('property_id')->toArray();

        $inSelectedCash = InstitutionProperties::where("institution_id", $userInfo->institutionStudy)->where('cash', '1')->pluck('property_id')->toArray();
        $inSelectedBursary = InstitutionProperties::where("institution_id", $userInfo->institutionStudy)->where('bursary', '1')->pluck('property_id')->toArray();
        $inSelectedNsfas = InstitutionProperties::where("institution_id", $userInfo->institutionStudy)->where('nsfas', '1')->pluck('property_id')->toArray();

        return [$property, $propertyInfo, $booking, $propertyInfoRooms, $userInfo, $inSelectedCash, $inSelectedBursary, $inSelectedNsfas];
    }

    public function check_all_rooms($bookingId){

        $property = Property::get();
        $this_year = Lookups::where('group_id', '4')->first();
        $searchable_rooms = [];

        foreach($property as $prop){
            if($prop->year_end == 0 || $prop->year_end >= $this_year){
                if(!in_array($prop->room_type, $searchable_rooms)){
                    array_push($searchable_rooms, $prop->room_type);
                }
            }
        }

        $allRooms = RoomTypes::where('name', '!=', 'Building Manager')->whereIn('id', $searchable_rooms)->pluck('id');

        $propertyInfo = Property::whereNull('room_available')->whereIn('room_type', $allRooms)->groupBy('residence')->get();
        $propertyInfoRooms = Property::with('roomType')->whereNull('room_available')->whereIn('room_type', $allRooms)->groupBy('room_type')->get();
        $propertyInfoRoomsAll = Property::with('roomType')->whereIn('room_type', $allRooms)->groupBy('room_type')->get();
        $property = [];

        foreach($propertyInfo as $pi){
            if(Residence::where('id', $pi->residence)->pluck('status')->first() != '1'){
                array_push($property, Residence::where('id', $pi->residence)->first());
            }
        }

        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        $booking = Booking::where('userId', $bookingId)->where('year', $this_year)->first();

        $userInfo = StudentsInfo::where('bookingId', $booking->id)->first();
        // $inSelected = InstitutionProperties::where("institution_id", $userInfo->institutionStudy)->pluck('property_id')->toArray();

        $inSelectedCash = InstitutionProperties::where("institution_id", $userInfo->institutionStudy)->where('cash', '1')->pluck('property_id')->toArray();
        $inSelectedBursary = InstitutionProperties::where("institution_id", $userInfo->institutionStudy)->where('bursary', '1')->pluck('property_id')->toArray();
        $inSelectedNsfas = InstitutionProperties::where("institution_id", $userInfo->institutionStudy)->where('nsfas', '1')->pluck('property_id')->toArray();

        $residenceAll = Residence::whereNull("status")->get();

        return [$property, $propertyInfo, $booking, $propertyInfoRooms, $userInfo, $propertyInfoRoomsAll, $residenceAll, $inSelectedCash, $inSelectedBursary, $inSelectedNsfas];
    }

    public function get_booking_info(Request $request){

        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        $bookingData = Booking::where('userId', $request->bookingId)->where('year', $this_year)->first();

        $property = Property::where('user_id_room', $request->bookingId)->first();

        //Get current booking year
        $this_year = Lookups::where('group_id', '4')->first();

        if(!empty($property)){
            $selected = $property;
        }else{
            $selected = [];
        }

        if (isset($request->getType) && $request->getType == "floor") {
            // $propertyInfo = Property::where('residence', $request->property)->where('room_type', $request->roomType)->get();

            $propertyInfo = Property::where('residence', $request->property)->where('room_type', $request->roomType)->whereNull('room_available')->where('inventory', '1')->get();

            // // $bed_used = Booking::with('users')->where('property', $request->property)->where('roomType', $request->roomType)->where('floor', $request->floor)->where('unit', $request->unit)->where('bed_code', $pi->bed_code)->get();

            //Create a unique Floor list
            $floors = [];

            foreach($propertyInfo as $pi) {
                if ($pi->year <= $pi->year_end || $pi->year_end == 0) {
                    if (($this_year->lookup_name >= $pi->year && $this_year->lookup_name <= $pi->year_end) || ($this_year->lookup_name >= $pi->year && $pi->year_end == 0)) {
                        $new_floor = $pi->floor;

                        if (!empty($pi->meta_info_1)) {
                            $new_floor .= $pi->meta_info_1;
                        }

                        if (!isset($floors[$new_floor])) {
                            $floors[$new_floor] = $pi;
                        }
                    }
                }
            }

            return ["true", $selected, $floors];

        } elseif (isset($request->getType) && $request->getType == "unit") {
            // $propertyInfo = Property::where('residence', $request->property)->where('room_type', $request->roomType)->where('floor', $request->floor)->where('meta_info_1', $request->meta)->get();

            $propertyInfo = Property::where('residence', $request->property)->where('room_type', $request->roomType)->where('floor', $request->floor)->where('meta_info_1', $request->meta)->whereNull('room_available')->where('inventory', '1')->get();

            //Create a unique Unit list
            $units = [];

            foreach($propertyInfo as $pi) {
                if ($pi->year <= $pi->year_end || $pi->year_end == 0) {
                    if (($this_year->lookup_name >= $pi->year && $this_year->lookup_name <= $pi->year_end) || ($this_year->lookup_name >= $pi->year && $pi->year_end == 0)) {
                        if (!isset($units[$pi->unit])) {
                            $units[$pi->unit] = $pi;
                        }                                 
                    }
                }
            }

            return ["true", $selected, $units];

        } elseif (isset($request->getType) && $request->getType == "bed_code") {
            $propertyInfo = Property::where('residence', $request->property)->where('room_type', $request->roomType)->where('floor', $request->floor)->where('unit', $request->unit)->whereNull('room_available')->where('inventory', '1')->get();

            //Create a unique Unit list
            $bed_code = [];
            $booked_bed_codes = [];

            foreach($propertyInfo as $pi) {

                if ($pi->year <= $pi->year_end || $pi->year_end == 0) {
                    if (($this_year->lookup_name >= $pi->year && $this_year->lookup_name <= $pi->year_end) || ($this_year->lookup_name >= $pi->year && $pi->year_end == 0)) {
                        if (!isset($units[$pi->bed_code])) {

                            $bed_used = Booking::with('users')->where('property', $request->property)->where('roomType', $request->roomType)->where('floor', $request->floor)->where('unit', $request->unit)->where('bed_code', $pi->bed_code)->where('year', $this_year)->get();
        
                            if (count($bed_used) == 0) {
                                $bed_code[$pi->bed_code] = $pi;
                            }else{
        
                                if($bed_used[0]->users->rob_50_approve == "2" || $bed_used[0]->users->rob_60_cancelled == "1"){
                                    $bed_code[$pi->bed_code] = $pi;
                                }else{
                                    $booked_bed_codes[$pi->bed_code] = $pi;
                                }
                            }
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

        if(Auth::user()->role_lookup == 1){
            $residences = Residence::withTrashed()->get();
            $roomTypes = RoomTypes::groupBy('property_id')->pluck('property_id')->toArray();

            return view('admin.property_address', ["headerTitle" => "Residense Info", "title" => "Residense Info", "residences" => $residences, "roomTypes" => $roomTypes]);
        }else{
            return "404 Error";
        }
        
    }

    public function residence_edit(Request $request, $residenceId){

        if(Auth::user()->role_lookup == 1){
            if($residenceId == "0"){
                $head = "Add";
                $propertyData = "";
            }else{
                $head = "Edit";
                $propertyData = Residence::where('id', $residenceId)->first();
            }

            $allProperties = Residence::whereNull('status')->get();

            // $curl = curl_init();

            // curl_setopt_array($curl, array(
            // CURLOPT_URL => 'https://api.mdapropsys.com/api/v2/token',
            // CURLOPT_RETURNTRANSFER => true,
            // CURLOPT_ENCODING => '',
            // CURLOPT_MAXREDIRS => 10,
            // CURLOPT_TIMEOUT => 0,
            // CURLOPT_FOLLOWLOCATION => true,
            // CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            // CURLOPT_CUSTOMREQUEST => 'GET',
            // CURLOPT_POSTFIELDS => 'grant_type=password&username=mri.api%40conurban.co.za&password=3ub4%5E2t2Y9PhPTEq',
            // CURLOPT_HTTPHEADER => array(
            //     'cache-control: no-cache',
            //     'Authorization: Basic your-API-key',
            //     'Content-Type: application/x-www-form-urlencoded',
            //     'Cookie: NSC_ESNS=0a80b654-6595-1303-9678-00505691daed_3897103846_3196003446_00000000000176021283'
            // ),
            // ));
    
            // $response = curl_exec($curl);
    
            // curl_close($curl);

            // //login Token
            // $loginToken = json_decode($response)->access_token;

            // $curl = curl_init();

            // curl_setopt_array($curl, array(
            // CURLOPT_URL => 'https://api.mdapropsys.com/api/v2/Properties/SearchProperties?PropertySearchFilter=',
            // CURLOPT_RETURNTRANSFER => true,
            // CURLOPT_ENCODING => '',
            // CURLOPT_MAXREDIRS => 10,
            // CURLOPT_TIMEOUT => 0,
            // CURLOPT_FOLLOWLOCATION => true,
            // CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            // CURLOPT_CUSTOMREQUEST => 'POST',
            // CURLOPT_POSTFIELDS => 'PropertySearchFilter=',
            // CURLOPT_HTTPHEADER => array(
            //     'DatabaseIdentifier: CC84465A-301E-4C19-AFA0-1939AB658425',
            //     'Authorization: Bearer '.$loginToken,
            //     'Cookie: NSC_ESNS=0a82f70c-6ac3-1303-9678-00505691daed_0229467849_1536900378_00000000000176163305'
            // ),
            // ));

            // $response = curl_exec($curl);

            // curl_close($curl);

            // return view('admin.residence_edit', ["headerTitle" => $head." Residence", "title" => $head." Residence", "propertyData" => $propertyData, "allProperties" => $allProperties, "mda" => json_decode($response)->ResultSet]);

            return view('admin.residence_edit', ["headerTitle" => $head." Residence", "title" => $head." Residence", "propertyData" => $propertyData, "allProperties" => $allProperties]);
        }else{
            return "404 Error";
        }
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
            "building_prefix" => $request->building_prefix,

            "account_name" => $request->account_name,
            "bank_name" => $request->bank_name,
            "account_number" => $request->account_number,
            // "branch" => $request->branch,
            "branch_code" => $request->branch_code,
            // "mda_id" => $request->mda_id,
            "company_reg" => $request->company_reg,
            'company_vat' => $request->company_vat,
            "status" => $request->status == 0 ? null : $request->status,
            "female" => $request->female == 0 ? null : $request->female,
            "male" => $request->male == 0 ? null : $request->male,
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

    // public function updateInfoRes($studentType){

    //     $students = User::where('studentType', $studentType)->get();
    //     foreach($students as $student){

    //         $this_year = Lookups::where('group_id', '4')->first();
    //         $this_year = $this_year->lookup_name;

    //         $file = UserFiles::where('userUid', $student['id'])->where('fileId', 'proof_of_payment')->where('year', $this_year)->first();

    //         if(!empty($file)){
    //             UserFiles::where('userUid', $student['id'])->where('fileId', 'proof_of_payment')->where('year', $this_year)->update(['status' => 'Approved']);
    //         }else{
    //             UserFiles::create([
    //                 'userUid'=> $student->id,
    //                 'fileId' => 'proof_of_payment',
    //                 'status' => 'Approved',
    //                 'year' => $this_year
    //             ]);
    //         }

    //         ActionList::create([
    //             "userUid" => $student['id'],
    //             "action" => "Booking Fee Waived",
    //             "actionUserUid" => Auth::user()->id,
    //             "actionUserDisplayName" => Auth::user()->userFirstName,
    //             "actionUserRole" => Auth::user()->userRole,
    //             "year" => $this_year,
    //         ]);
    //     }
    // }

    // public function updateInfoResRedo($studentType){
    //     $students = User::where('studentType', $studentType)->get();

    //     $this_year = Lookups::where('group_id', '4')->first();
    //     $this_year = $this_year->lookup_name;

    //     foreach($students as $student){
    //         $file = UserFiles::where('userUid', $student['id'])->where('fileId', 'proof_of_payment')->where('year', $this_year)->first();

    //         if(!empty($file) && $file->fileUrl !== null){
    //             UserFiles::where('userUid', $student['id'])->where('fileId', 'proof_of_payment')->where('year', $this_year)->update(['status' => 'Rejected']);
    //         }

    //         ActionList::create([
    //             "userUid" => $student['id'],
    //             "action" => "Booking Fee Added",
    //             "actionUserUid" => Auth::user()->id,
    //             "actionUserDisplayName" => Auth::user()->userFirstName,
    //             "actionUserRole" => Auth::user()->userRole,
    //             "year" => $this_year,
    //         ]);
    //     }
    // }

    public function action_data(Request $request){
        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        $student = User::where('id', $request->userId)->first();
        $studentInfo = StudentsInfo::where('userUid', $request->userId)->where('year', $this_year)->first();

        if($request->FileNameForApproval == "studentCard"){
            $name = 'STUDENT CARD';
            $field = "student_card_approved";
        }else if($request->FileNameForApproval == "copyID"){
            $name = 'COPY OF ID DOCUMENT';
            $field = "id_data_approved";
        }else if($request->FileNameForApproval == "proofRegistration"){
            $name = 'PROOF OF REGISTRATION';
            $field = "registration_approved";
        }else if($request->FileNameForApproval == "proof_of_payment"){
            $name = 'PROOF OF PAYMENT';
            $field = "fee_approved";
        }else if($request->FileNameForApproval == "bursaryApproval"){
            if($studentInfo->studentType == "NSFAS"){
                $name = 'NSFAS';
            }else{
                $name = 'BURSARY';
            }
            $field = "bursary_approved";
            $field2 = "burser_approved";

            if($request->type == "1"){
                // User::where('id', $request->userId)->update(['burser_approved' => 1]);
                StudentsInfo::where('userUid', $request->userId)->where('year', $this_year)->update(['burser_approved' => 1]);
            }else{
                // User::where('id', $request->userId)->update(['burser_approved' => null]);
                StudentsInfo::where('userUid', $request->userId)->where('year', $this_year)->update(['burser_approved' => null]);
            }
        }else if($request->FileNameForApproval == "bankStatement"){
            $name = 'BANK STATEMENT';
            $field = "bank_approved";
            $field2 = "parent_approved";

            if($request->type == "1"){
                // User::where('id', $request->userId)->update(['parent_approved' => 1]);
                StudentsInfo::where('userUid', $request->userId)->where('year', $this_year)->update(['parent_approved' => 1]);
            }else{
                // User::where('id', $request->userId)->update(['parent_approved' => null]);
                StudentsInfo::where('userUid', $request->userId)->where('year', $this_year)->update(['parent_approved' => null]);
            }
        }

        $dataToUpdate = [
            $field => $request->type,
        ];

        if(isset($field2)){
            $dataToUpdate[$field2] = $request->type;
        }
        
        // User::where('id', $request->userId)->update($dataToUpdate);
        StudentsInfo::where('userUid', $request->userId)->where('year', $this_year)->update($dataToUpdate);

        if($request->type == "1"){
            $action = "Approved";
        }else{
            $action = "Rejected";
        }

        // $status_data = User::where('id', $request->userId)->first();
        $status_data = StudentsInfo::where('userUid', $request->userId)->where('year', $this_year)->first();

        if($status_data->rob_10_fee == 2 && $status_data->rob_15_id == 2 && $status_data->rob_20_card == 2 && $status_data->rob_25_reg == 2 && $status_data->rob_30_funds == 2 && $status_data->rob_35_info == 2 && $status_data->rob_40_bed == 2 && ($status_data->rob_45_other == 3 || $status_data->rob_45_other == 2) && $status_data->rob_50_approve == 0){
            User::where('id', $request->userId)->update(['rob_50_approve' => 1]);
        }

        ActionList::create([
            "userUid" => $request->userId,
            "action" => $name." Data ".$action,
            "actionUserUid" => Auth::user()->id,
            "actionUserDisplayName" => Auth::user()->userFirstName,
            "actionUserRole" => Auth::user()->userRole,
            "year" => $this_year,
          ]);

        return "true";
    }

    public function approve_app_data(Request $request){

        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        if($request->type == "Address"){
            User::where('id', $request->userId)->update(["address_approved" => "2"]);

            ActionList::create([
                "userUid" => $request->userId,
                "action" => "Physical Address Data Approved",
                "actionUserUid" => Auth::user()->id,
                "actionUserDisplayName" => Auth::user()->userFirstName,
                "actionUserRole" => Auth::user()->userRole,
                "year" => $this_year,
              ]);
        }else{
            User::where('id', $request->userId)->update(["kin_approved" => "2"]);

            ActionList::create([
                "userUid" => $request->userId,
                "action" => "Next of Kin Data Approved",
                "actionUserUid" => Auth::user()->id,
                "actionUserDisplayName" => Auth::user()->userFirstName,
                "actionUserRole" => Auth::user()->userRole,
                "year" => $this_year,
              ]);
        }
    }

    public function room_types(Request $request){

        if(Auth::user()->role_lookup == 1){

            $room_types = RoomTypes::get();
            $residences = Residence::whereNull('status')->get();
            $property = Property::get();

            $roomDetails = [];

            $jsonDataRooms = json_decode(file_get_contents('https://www.campusafrica.co.za/wp-json/wp/v2/rooms?per_page=100'));

            $jsonDataResidence = json_decode(file_get_contents('https://www.campusafrica.co.za/wp-json/wp/v2/residences'));

            foreach($jsonDataRooms as $room){
                $booking_fee = 0;
                foreach($jsonDataResidence as $res){
                    if($res->id == $room->acf->residence){
                        $booking_fee = $res->acf->booking_fee;

                        if($room->acf->booking_fee_override !== ""){
                            $booking_fee = $room->acf->booking_fee_override;
                        }
                    }
                }
                array_push($roomDetails, (object)array("id" => $room->acf->residence, "slug" => $room->slug, "title" => $room->title->rendered, "roomId" => $room->id, "booking_fee" => $booking_fee, "price_from" => $room->acf->price_from, "applies_to" => $room->acf->booking_fee_applies_to));
            }

            return view('admin.room_types', ["headerTitle" => "Room Types", "room_types" => $room_types, "title" => "Room Types", "residences" => $residences, 'property' => $property, 'roomDetails' => $roomDetails]);
        }else{
            return "404 Error";
        }
    }

    public function get_room_types(Request $request){

        $property = $request->residence;

        $residence = Residence::where('id', $property)->first();

        $rooms = RoomTypes::where('property_id', $residence->id)->get();

        return $rooms;
    }

    public function get_room_types_id(Request $request){
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

    public function room_type_edit(Request $request, $id){

        if(Auth::user()->role_lookup == 1){
            if($id == "0"){
                $head = "Add";
                $roomTypeData = "";
            }else{
                $head = "Edit";
                $roomTypeData = RoomTypes::where('id', $id)->first();
            }

            $residence = Residence::whereNull('status')->get();

            $roomDetails = [];
            $propertyDetails = [];

            $jsonDataResidence = json_decode(file_get_contents('https://www.campusafrica.co.za/wp-json/wp/v2/residences'));
            $residenceId = "";

            foreach($jsonDataResidence as $res){
                foreach($residence as $prop){
                    if(strtoupper($prop->residence) === strtoupper($res->title->rendered)){
                        array_push($propertyDetails, (object)array("name" => $res->title->rendered, "propId" => $prop->id, "id" => $res->id));
                    }
                }
            }

            $jsonDataRooms = json_decode(file_get_contents('https://www.campusafrica.co.za/wp-json/wp/v2/rooms?per_page=100'));

            foreach($jsonDataRooms as $room){
                array_push($roomDetails, (object)array("id" => $room->acf->residence, "slug" => $room->slug, "title" => $room->title->rendered, "roomId" => $room->id));
            }

            // dd($roomDetails);

            return view('admin.room_type_edit', ["headerTitle" => $head." Room Type", "title" => $head." Room Type", "roomTypeData" => $roomTypeData, "residence" => $residence, 'roomDetails' => $roomDetails, 'propertyDetails' => $propertyDetails]);
        }else{
            return "404 Error";
        }
    }

    public function save_room_type (Request $request){

        $roomTypeId = $request->id;

        $data = [
            "property_id" => $request->property_id,
            "name" => $request->name,
            "room_map_slug" => $request->room_map,
            // "room_map_price" => $request->room_map_price,
            "pull_price" => $request->pull_price,
            "use_nsfas" => $request->use_nsfas,
            "booking_price" => $request->booking_price,
            "rent_price" => $request->rent_price,
            "apply_nsfas" => $request->apply_nsfas,
            "apply_bursary" => $request->apply_bursary,
            "apply_cash" => $request->apply_cash,
        ];

        if($roomTypeId == "0"){
            $data['online'] = 0;
            RoomTypes::create($data);
        }else{
            RoomTypes::where('id', $roomTypeId)->update($data);
        }

        return redirect("/room-types");
    }

    public function nsfas_approval_upload(Request $request){

        $this_year = Lookups::where('group_id', '4')->first();
        $uploads = ImportNSFASApproval::with('users')->where('year', $this_year->lookup_name)->get();
        $active = ImportNSFASApproval::where('status', '1')->first();
        if($active){
            $students = ImportNSFASStudentAuditLog::where('importId', $active->id)->where('status', '0')->get();
        }else{
            $students = ImportNSFASStudentAuditLog::where('status', '0')->get();
        }
        
        return view('admin.nsfas_upload', ["headerTitle" => "NSFAS Approval Upload", "title" => "NSFAS Approval Upload", 'uploads' => $uploads, 'students' => $students, 'active' => $active]);
    }

    public function new_import_data(){
        $this_year = Lookups::where('group_id', '4')->first();
        $uploads = ImportNSFASApproval::with('users')->where('year', $this_year->lookup_name)->get();
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
        $this_year = Lookups::where('group_id', '4')->first();
        

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

                // $allStudents = User::with('files')->where("userRole", "0")->get();
                $allStudents = StudentsInfo::where('year', $this_year->lookup_name)->get();

                foreach($schdeules as $user){
                    
                    if($i > 0){

                        if($user[0] !== null){
                            $totalStudents++;

                            $idFound = 0;

                            foreach($allStudents as $thisStudent){
                                $booking = Booking::where('userId', $thisStudent->id)->where('year', $this_year->lookup_name)->first();
                                if($this_year->lookup_name == $booking->year){
                                    //
                                    if($thisStudent->studentNo == $user[0]){
                                        $idFound++;
                                        $this->update_user_status($thisStudent->id);
                                        if($thisStudent->studentType == "NSFAS"){
                                            $files = $thisStudent->files;
                                            $approvalFound = 0;
    
                                            foreach($files as $file){
                                                if($file->fileId == "bursaryApproval"){
                                                    $approvalFound++;
    
                                                    if($file->status !== "Approved"){
                                                        $this_year = Lookups::where('group_id', '4')->first();
                                                        $this_year = $this_year->lookup_name;

                                                        UserFiles::where('userUid', $thisStudent->id)->where('fileId', 'bursaryApproval')->where('year', $this_year)->update(['status' => 'Approved', 'fileName' => null, 'fileUrl' => null, 'uploaded_by' => 'Import']);
                                                        array_push($actionLogList, ["userUid" => $thisStudent->id, "year" => $this_year, "action" => "NSFAS Approval has been Approved via import", "actionUserUid" => Auth::user()->id, "actionUserDisplayName" => Auth::user()->userFirstName." ".Auth::user()->userLastname, "actionUserRole" => Auth::user()->userRole, "created_at" => date('Y-m-d H:i:s'), "updated_at" => date('Y-m-d H:i:s')]);
                                                    }
                                                }
                                            }
    
                                            if($approvalFound == 0){
                                                $this_year = Lookups::where('group_id', '4')->first();
                                                $this_year = $this_year->lookup_name;
                                                array_push($fileList, ['userUid'=> $thisStudent->id, 'fileId' => 'bursaryApproval', 'status' => 'Approved', 'uploaded_by' => 'Import', 'year' => $this_year]);
                                                array_push($actionLogList, ["userUid" => $thisStudent->id, "year" => $this_year, "action" => "NSFAS Approval has been Approved via import", "actionUserUid" => Auth::user()->id, "actionUserDisplayName" => Auth::user()->userFirstName." ".Auth::user()->userLastname, "actionUserRole" => Auth::user()->userRole, "created_at" => date('Y-m-d H:i:s'), "updated_at" => date('Y-m-d H:i:s')]);
                                            }
    
                                            //Update robots
                                            // User::where('id', $thisStudent->id)->update(['rob_30_funds' => 2, 'bursary_approved' => 1]);
                                            StudentsInfo::where('userUid', $thisStudent->id)->where('year', $this_year->lookup_name)->update(['rob_30_funds' => 2, 'bursary_approved' => 1]);
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

        foreach($allStudents as $thisStudent){
            $this->update_user_status($thisStudent->id);
        }

        ImportNSFASApproval::where('status', '1')->update(["status" => 0]);

        $this_year = Lookups::where('group_id', '4')->first();
        $newImport = ImportNSFASApproval::create([ 'file_name' => $inputFileName, 'actionUserId' => Auth::user()->id, "matched" => $matched, "unmatched" => $unmatched, "total_records" => $totalStudents, "status" => 1, 'year' => $this_year->lookup_name]);

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

        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        $allFilesArr = [];
        $files = UserFiles::where('userUid', $userId)->where('year', $this_year)->where('status', 'Approved')->get();
        // $userData = User::where('id', $userId)->first();
        $userData = StudentsInfo::where('userUid', $userId)->where('year', $this_year)->first();

        foreach($files as $file){
            if($file->fileUrl !== '' && $file->fileUrl !== null){
                array_push($allFilesArr, "storage/".$file->fileUrl);
            }
        }

        if($userData->userId == ""){
            $userIdentification = $userData->userPassport;
        }else{
            $userIdentification = $userData->userId;
        }

        $booking = Booking::where('id', $userData->bookingId)->first();
        $lookups = Lookups::get();
        $bookingRes = Residence::where('id', $booking->property)->first();
        $bookingRoom = RoomTypes::where('id', $booking->roomType)->first();
        $bookingBed = property::where('id', $booking->property_data_id)->first();
        $paymentBooking = BookingPayment::where('bookingId', $booking->id)->first();

        $all = ['copyID', 'studentCard', 'proofRegistration', 'proof_of_payment'];
        $uploaded = [];

        if($userData->rob_50_approve == 2 && $booking->lease_from_date !== null && $booking->lease_from_time !== null && $booking->lease_to_date !== null && $booking->lease_to_time !== null){
            array_push($all, "moveIn");
        }
        if($userData->studentType == "Cash / Direct"){
            array_push($all, "bankStatement");
        }
        if($userData->studentType !== "Cash / Direct"){
            array_push($all, "bursaryApproval");
        }

        $approvedLease = UserFiles::where('userUid', $userData->userUid)->where('year', $this_year)->where('fileId', 'moveIn')->first();
        $files = UserFiles::where('userUid', $userData->userUid)->where('year', $this_year)->get();

        $data = [];

        foreach($files as $file){
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
            array_push($data, ["field" => $file->fileId, "status" => $file->status, "fileName" => $file->fileName, "url" => $file->fileUrl, "uploaded_by" => $file->uploaded_by, "created_at" => $file->created_at, "id" => $file->id]);
        }

        $left = array_diff($all, $uploaded);
        foreach($left as $dataLeft){
            array_push($data, ["field" => $dataLeft, "status" => "Rejected"]);
        }

        usort($data, function($a, $b) {
            return $a['status'] <=> $b['status'];
        });

        $comments =  Comments::with('users')->where('user_uid', $userData->userUid)->where('bookingId', $userData->bookingId)->orderBy('created_at', 'desc')->get();

        $previousBookings = Booking::where('userId', $userData->userUid)->where('year', '!=', $this_year)->orderBy('year', 'desc')->get();
        $actions = ActionList::with('users')->where('userUid', $userId)->where('year', $this_year)->orderBy('created_at', 'DESC')->get();

        // dd($comments[0]['users']['email']);

        //here
        

        $pdf = PDF::loadView('PDF.appView', compact('userData', 'booking', 'lookups', 'bookingRes', 'bookingRoom', 'bookingBed', 'paymentBooking', 'data', 'comments', 'previousBookings', 'actions'));
        $fileNameToSave = $booking->booking_code."_".$userData->userLastname."_".$userData->userFirstName."_Application_".$userData->year.".pdf";
        $content = $pdf->download($fileNameToSave)->getOriginalContent();
        \Storage::put('public/files/'.$userIdentification.'/'.$fileNameToSave, $content);

        array_push($allFilesArr, "storage/files/".$userIdentification."/".$fileNameToSave);


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

    public function waive_action(Request $request){

        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        BookingPayment::where('bookingId', $request->bookingId)->update([$request->changeField => 2]);

        if($request->changeField == "rob_b_waived"){
            $payment = BookingPayment::where('bookingId', $request->bookingId)->first();
            $booking = Booking::where('id', $request->bookingId)->update(['room_rate' => '0', 'waive_reason' => $payment->reason_booking_fee]);

            $file = UserFiles::where('userUid', $request->userId)->where('fileId', 'proof_of_payment')->where('year', $this_year)->first();

            $userData = StudentsInfo::where('userUid', $request->userId)->where('year', $this_year)->first();            

            if(!$file){
                UserFiles::create([
                    'userUid' => $request->userId,
                    'fileId' => 'proof_of_payment',
                    'status' => 'Approved',
                    'year' => $this_year,
                ]);
                if($userData->rob_40_bed != 2){
                    StudentsInfo::where('userUid', $request->userId)->where('year', $this_year)->update(["rob_40_bed" => 1]);
                }
            }else{
                UserFiles::where('userUid', $request->userId)->where('fileId', 'proof_of_payment')->where('year', $this_year)->update([
                    'status' => 'Approved'
                ]);
                if($userData->rob_40_bed != 2){
                    StudentsInfo::where('userUid', $request->userId)->where('year', $this_year)->update(["rob_40_bed" => 1]);
                }
            }

            StudentsInfo::where('userUid', $request->userId)->where('year', $this_year)->update(["rob_10_fee" => "2"]);
        }

        

        ActionList::create([
            "userUid" => $request->userId,
            "action" => "Fee Waiver approved for ".$request->textChange,
            "actionUserUid" => Auth::user()->id,
            "actionUserDisplayName" => Auth::user()->userFirstName,
            "actionUserRole" => Auth::user()->userRole,
            "year" => $this_year
        ]);

        //find other files
        $allOtherFiles = 0;
        $status = 2;
        $allSearchable = UserFiles::where("userUid", $request->userId)->where('year', $this_year)->get();
        foreach($allSearchable as $search){
            if(explode("_", $search->fileId)[0] == "other"){
                $allOtherFiles++;
                if($search->status == "Uploaded"){
                    $status = 1;
                }else if($search->status == "rejected"){
                    $status = 0;
                }
            }
        }

        $payment = BookingPayment::where('bookingId', $request->bookingId)->first();

        if($payment->rob_b_waived == '1' || $payment->rob_loan_waived == '1' || $payment->rob_deposit_waived == '1' || $status == 1){
            StudentsInfo::where('userUid', $request->userId)->where('year', $this_year)->update(["rob_45_other" => "1"]);
        }else if($status == 0){
            StudentsInfo::where('userUid', $request->userId)->where('year', $this_year)->update(["rob_45_other" => "0"]);
        }else{
            StudentsInfo::where('userUid', $request->userId)->where('year', $this_year)->update(["rob_45_other" => "2"]);
        }


        return UserFiles::where('userUid', $request->userId)->where('fileId', 'proof_of_payment')->pluck('id')->first();
    }

    public function waive_reject(Request $request){
        BookingPayment::where('bookingId', $request->bookingId)->update([$request->changeField => 0, $request->rejField => $request->reason, $request->field => 0]);

        if($request->changeField == "rob_b_waived"){

            $lookup = Lookups::where('group_id', '7')->first();

            $booking = Booking::where('userId', $request->userId)->first();

            User::where('id', $request->userId)->update(["rob_10_fee" => "0"]);

            $booking_data = [];

            $roomTypeNewId = $booking->roomType;

            $booking->property = Residence::where('id', $booking->property)->pluck('residence')->first();
            $request->roomType = RoomTypes::where('id', $booking->roomType)->pluck('name')->first();

            
            $roomTypeData = RoomTypes::where('id', $roomTypeNewId)->first();
            $roomTypeId = $roomTypeData->room_map_slug;

            $allResidences = Residence::whereNull('status')->get();
            $allRoomTypes = RoomTypes::get();


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

                $roomType = "";
                $roomName = "";

                foreach($jsonDataRooms as $room){
                    if($residenceId == $room->acf->residence){


                        if($room->id == $roomTypeId){
                            $roomType = $request->roomType;
                            $roomName = $room->slug;

                            $appliesTo = $room->acf->booking_fee_applies_to;

                            if($room->acf->booking_fee_override !== ""){
                                $booking_data['room_rate'] = $room->acf->booking_fee_override;
                            }

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
            }

            if($roomTypeData->pull_price == null){
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

            $booking = Booking::where('userId', $request->userId)->update($booking_data);

            UserFiles::where('userUid', $request->userId)->where('fileId', 'proof_of_payment')->update([
                'status' => 'Rejected'
            ]);

            BookingPayment::where('bookingId', $request->bookingId)->update(["booking_fee" => $booking_data['room_rate']]);
        }

        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;


        ActionList::create([
            "userUid" => $request->userId,
            "action" => "Fee Waiver rejected for ".$request->textChange." with reason: ".$request->reason,
            "actionUserUid" => Auth::user()->id,
            "actionUserDisplayName" => Auth::user()->userFirstName,
            "actionUserRole" => Auth::user()->userRole,
            "year" => $this_year
        ]);

        //find other files
        $allOtherFiles = 0;
        $status = 3;
        $allSearchable = UserFiles::where("userUid", $request->userId)->where('year', $this_year)->get();
        foreach($allSearchable as $search){
            if(explode("_", $search->fileId)[0] == "other"){
                $allOtherFiles++;
                if($search->status == "Uploaded"){
                    $status = 1;
                }else if($search->status == "Rejected"){
                    $status = 0;
                }else if($search->status == "Approved"){
                    $status = 2;
                }
            }
        }

        $payment = BookingPayment::where('bookingId', $request->bookingId)->first();

        if($payment->rob_b_waived == '0' && $payment->rob_loan_waived == '0' && $payment->rob_deposit_waived == '0' && $status == 3){
            StudentsInfo::where('userUid', $request->userId)->where('year', $this_year)->update(["rob_45_other" => "3"]);
        }else if($payment->rob_b_waived == '1' || $payment->rob_loan_waived == '1' || $payment->rob_deposit_waived == '1' || $status == 1){
            StudentsInfo::where('userUid', $request->userId)->where('year', $this_year)->update(["rob_45_other" => "1"]);
        }else if($status == 0){
            StudentsInfo::where('userUid', $request->userId)->where('year', $this_year)->update(["rob_45_other" => "0"]);
        }else{
            StudentsInfo::where('userUid', $request->userId)->where('year', $this_year)->update(["rob_45_other" => "2"]);
        }

        return BookingPayment::where('bookingId', $request->bookingId)->first();
    }

    public function waive_request(Request $request){
        BookingPayment::where('bookingId', $request->bookingId)->update([$request->waiveField => 1, $request->changeField => 1, $request->reasonField => $request->reason]);

        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        StudentsInfo::where('bookingId', $request->bookingId)->where('year', $this_year)->update(["rob_45_other" => "1"]);

        ActionList::create([
            "userUid" => $request->userId,
            "action" => "Fee Waiver requested for ".$request->textChange." with reason: ".$request->reason,
            "actionUserUid" => Auth::user()->id,
            "actionUserDisplayName" => Auth::user()->userFirstName,
            "actionUserRole" => Auth::user()->userRole,
            "year" => $this_year
        ]);
        
    }

    public function waive_booking_fee(Request $request){

        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;
        
        $booking = Booking::where('userId', $request->userId)->where('year', $this_year)->update(['room_rate' => '0', 'waive_reason' => $request->reason]);

        BookingPayment::where('bookingId', $request->bookingId)->update(['booking_fee_waived' => 1, 'rob_b_waived' => 1]);
        $file = UserFiles::where('userUid', $request->userId)->where('fileId', 'proof_of_payment')->where('year', $this_year)->first();

        $userData = StudentsInfo::where('userUid', $request->userId)->where('year', $this_year)->first();
            

        if(!$file){
            UserFiles::create([
                'userUid' => $request->userId,
                'fileId' => 'proof_of_payment',
                'status' => 'Approved',
                'year' => $this_year,
            ]);
            if($userData->rob_40_bed != 2){
                StudentsInfo::where('userUid', $request->userId)->where('year', $this_year)->update(["rob_40_bed" => 1]);
            }
        }else{
            UserFiles::where('userUid', $request->userId)->where('fileId', 'proof_of_payment')->where('year', $this_year)->update([
                'status' => 'Approved'
            ]);
            if($userData->rob_40_bed != 2){
                StudentsInfo::where('userUid', $request->userId)->where('year', $this_year)->update(["rob_40_bed" => 1]);
            }
        }

        // User::where('id', $request->userId)->update(["rob_10_fee" => "2"]);
        StudentsInfo::where('userUid', $request->userId)->where('year', $this_year)->update(["rob_10_fee" => "2"]);

    
        ActionList::create([
            "userUid" => $request->userId,
            "action" => "Booking Fee Waived with reason: ".$request->reason,
            "actionUserUid" => Auth::user()->id,
            "actionUserDisplayName" => Auth::user()->userFirstName,
            "actionUserRole" => Auth::user()->userRole,
            "year" => $this_year,
        ]);

        return UserFiles::where('userUid', $request->userId)->where('fileId', 'proof_of_payment')->where('year', $this_year)->pluck('id')->first();
        
    }

    public function undo_waive_booking_fee(Request $request){

        $lookup = Lookups::where('group_id', '7')->first();

        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        $booking = Booking::where('userId', $request->userId)->where('year', $this_year)->first();

        // User::where('id', $request->userId)->update(["rob_10_fee" => "0"]);
        StudentsInfo::where('userUid', $request->userId)->where('year', $this_year)->update(["rob_10_fee" => "0"]);
        $user = StudentsInfo::where('userUid', $request->userId)->where('year', $this_year)->first();

        $booking_data = [];

        $roomTypeNewId = $booking->roomType;

        $booking->property = Residence::where('id', $booking->property)->pluck('residence')->first();
        $request->roomType = RoomTypes::where('id', $booking->roomType)->pluck('name')->first();

        
        $roomTypeData = RoomTypes::where('id', $roomTypeNewId)->first();
        $roomTypeId = $roomTypeData->room_map_slug;

        $allResidences = Residence::whereNull('status')->get();
        $allRoomTypes = RoomTypes::get();


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

            $roomType = "";
            $roomName = "";

            foreach($jsonDataRooms as $room){
                if($residenceId == $room->acf->residence){

                    if($room->id == $roomTypeId){
                        $roomType = $request->roomType;
                        $roomName = $room->slug;

                        $appliesTo = $room->acf->booking_fee_applies_to;

                        if($room->acf->booking_fee_override !== ""){
                            $booking_data['room_rate'] = $room->acf->booking_fee_override;
                        }

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
        }

        if($roomTypeData->pull_price == null){
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

        $booking = Booking::where('userId', $request->userId)->where('year', $this_year)->update($booking_data);

        UserFiles::where('userUid', $request->userId)->where('fileId', 'proof_of_payment')->where('year', $this_year)->update([
            'status' => 'Rejected'
        ]);
    
        ActionList::create([
            "userUid" => $request->userId,
            "action" => "Booking Fee Waiver Undone",
            "actionUserUid" => Auth::user()->id,
            "actionUserDisplayName" => Auth::user()->userFirstName,
            "actionUserRole" => Auth::user()->userRole,
            "year" => $this_year,
        ]);
        
    }

    public function update_booking_fees(Request $request){
        
        $bookingList = Booking::whereNotNull('property')->whereNotNull('roomType')->get();

        $lookup = Lookups::where('group_id', '7')->first();

        $roomTypeNewId = $booking->roomType;

        $booking->property = Residence::where('id', $booking->property)->pluck('residence')->first();
        $booking->roomType = RoomTypes::where('id', $booking->roomType)->pluck('name')->first();
        
        $roomTypeData = RoomTypes::where('id', $roomTypeNewId)->first();
        $roomTypeId = $roomTypeData->room_map_slug;

        $allResidences = Residence::whereNull('status')->get();
        $allRoomTypes = RoomTypes::get();

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

                        if($room->id == $roomTypeId){
                            $roomType = $booking->roomType;
                            $roomName = $room->slug;

                            $appliesTo = $room->acf->booking_fee_applies_to;

                            if($room->acf->booking_fee_override !== ""){
                                $booking_data['room_rate'] = $room->acf->booking_fee_override;
                            }

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

                        // $slugString = str_replace("-", " ", $room->slug);
                        // $roomType = "";
                        // $roomName = "";

                        // foreach($allResidences as $residence){
                        //     if(strtoupper($booking->property) == strtoupper($residence->residence)){
                        //         $postSlugStringName = trim(str_replace("room", "", str_replace(strtolower($residence->residence), "",$slugString)))."\r\n";
                        //         $requiredName = $booking->roomType;

                        //         foreach($jsonDataRooms as $roomInfo){
                        //             if($residenceId == $roomInfo->acf->residence){
                        //                 if(strtolower(trim(str_replace("room", "", str_replace(strtolower($residence->residence), "",str_replace("-", " ", $roomInfo->slug))))) == strtolower($booking->roomType)){
                        //                     $roomType = $booking->roomType;
                        //                     $roomName = $roomInfo->slug;
                        //                 }
                        //             }
                        //         }
                        //     }
                        // }

                        // if($booking->property == "YMCA"){
                        //     if($room->slug == "ymca-3-sharing"){
                        //         $roomType = "3 Sharing";
                        //         $roomName = "ymca-3-sharing";
                        //     }
                        //     if($room->slug == "ymca-2-sharing"){
                        //         $roomType = "2 Sharing";
                        //         $roomName = "ymca-2-sharing";
                        //     }
                        //     if($room->slug == "ymca-single-room-large"){
                        //         $roomType = "Single Large";
                        //         $roomName = "ymca-single-room-large";
                        //     }
                        //     if($room->slug == "ymca-single-room-medium"){
                        //         $roomType = "Single Medium";
                        //         $roomName = "ymca-single-room-medium";
                        //     }
                        //     if($room->slug == "ymca-single-room-small"){
                        //         $roomType = "Single Small";
                        //         $roomName = "ymca-single-room-small";
                        //     }
                        // }

                        // if($booking->property == "126 Siemert"){
                        //     if($room->slug == "126-siemert-single-room"){
                        //         $roomType = "Single";
                        //         $roomName = "126-siemert-single-room";
                        //     }
                        //     if($room->slug == "126-siemert-2-sharing"){
                        //         $roomType = "2 Sharing";
                        //         $roomName = "126-siemert-2-sharing";
                        //     }
                        // }

                        // if($booking->property == "Wynton Joy"){
                        //     if($room->slug == "wynton-joy-single-room"){
                        //         $roomType = "Single";
                        //         $roomName = "wynton-joy-single-room";
                        //     }
                        //     if($room->slug == "wynton-joy-2-sharing"){
                        //         $roomType = "2 Sharing";
                        //         $roomName = "wynton-joy-2-sharing";
                        //     }
                        // }

                        // if($booking->property == "Dunvista Mansions"){
                        //     if($room->slug == "dunvista-mansions-single-room"){
                        //         $roomType = "Single";
                        //         $roomName = "dunvista-mansions-single-room";
                        //     }
                        //     if($room->slug == "dunvista-mansions-2-sharing"){
                        //         $roomType = "2 Sharing";
                        //         $roomName = "dunvista-mansions-2-sharing";
                        //     }
                        // }

                        // if($booking->property == "49 Jorissen"){
                        //     if($room->slug == "49-jorissen-single-room-2-bedroom-apartment"){
                        //         $roomType = "Single 2 bedroom";
                        //         $roomName = "49-jorissen-single-room-2-bedroom-apartment";
                        //     }
                        //     if($room->slug == "49-jorissen-single-room-3-bedroom-apartment"){
                        //         $roomType = "Single 3 bedroom";
                        //         $roomName = "49-jorissen-single-room-3-bedroom-apartment";
                        //     }
                        //     if($room->slug == "49-jorissen-single-room-4-bedroom-apartment"){
                        //         $roomType = "Single 4 bedroom";
                        //         $roomName = "49-jorissen-single-room-4-bedroom-apartment";
                        //     }
                        //     if($room->slug == "49-jorissen-2-sharing-hotel"){
                        //         $roomType = "2 Sharing 2 Bedroom | 2 Sharing";
                        //         $roomName = "49-jorissen-2-sharing-hotel";
                        //     }
                        // }

                        // if($booking->property == "80 Jorissen"){
                        //     if($room->slug == "80-jorissen-single-room"){
                        //         $roomType = "Single";
                        //         $roomName = "80-jorissen-single-room";
                        //     }
                        //     if($room->slug == "80-jorissen-2-sharing"){
                        //         $roomType = "2 Sharing";
                        //         $roomName = "80-jorissen-2-sharing";
                        //     }
                        // }

                        // if($booking->property == "Braamlofts"){
                        //     if($room->slug == "braamlofts-single-room"){
                        //         $roomType = "Single";
                        //         $roomName = "braamlofts-single-room";
                        //     }
                        //     if($room->slug == "braamlofts-2-sharing"){
                        //         $roomType = "2 Sharing";
                        //         $roomName = "braamlofts-2-sharing";
                        //     }
                        //     if($room->slug == "braamlofts-3-sharing"){
                        //         $roomType = "3 Sharing";
                        //         $roomName = "braamlofts-3-sharing";
                        //     }
                        // }

                        

                        
                    }
                }
            }

            if($roomTypeData->pull_price == null){
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

            Booking::where('userId', $booking->userId)->update($booking_data);

            $this_year = Lookups::where('group_id', '4')->first();
            $this_year = $this_year->lookup_name;
    
        
            ActionList::create([
                "userUid" => $booking->userId,
                "action" => "Booking Fee Updated (Bulk Update)",
                "actionUserUid" => Auth::user()->id,
                "actionUserDisplayName" => Auth::user()->userFirstName,
                "actionUserRole" => Auth::user()->userRole,
                "year" => $this_year
            ]);
        }
    }

    public function update_processing_status(){
        $userList = User::get();

        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        foreach($userList as $user) {

            $Lease = UserFiles::where("userUid", $user->id)->where('fileId', 'moveIn')->where('year', $this_year)->first();

            $overall = "Incomplete";

            if($user->bookingStatus == "Approved" || $user->applicationStatus == "Approved Confirmed" || $user->applicationStatus == "Approved") {
                $overall = "Approved";
            }
            
            if($user->bookingStatus == "Empty" || $user->bookingStatus == "Rejected" || $user->applicationStatus == "Empty" || $user->applicationStatus == "Rejected"){
                $overall = "Incomplete";
            }

            if($user->bookingStatus == "Action" || $user->applicationStatus == "Action"){
                $overall = "Pending";
            }

            if ($user->bookingStatus == "Cancelled" && $user->applicationStatus == "Cancelled") {
                $overall = "Cancelled";
            }

            //Check for a lease
            if ($overall == "Approved") {
                if (empty($Lease) && $user->applicationStatus == "Approved Confirmed") {
                    $overall = "Incomplete";
                } elseif (empty($Lease) && $user->applicationStatus == "Approved") {
                    $overall = "Pending";
                } elseif ($Lease->status == "Uploaded") {                 
                    $overall = "Pending";
                }
            }
            

            User::where('id', $user->id)->update(['overall_status' => $overall]);
        }
    }

    public function update_lease_dates(Request $request){
        $this_year = $request->value;
        // $allUsers = User::where('rob_60_cancelled', '0')->get();
        $allUsers = StudentsInfo::where('rob_60_cancelled', '0')->where('year', $this_year)->get();

        foreach($allUsers as $user){

            if($user->rob_50_approve == "0" || $user->rob_50_approve == "1"){

                    $booking = Booking::where('userId', $user->userUid)->where('year', $this_year)->first();

                    if(!empty($booking) && $booking->lease_unique !== 1){

                        $dateFrom = $request->value."-".$request->month_from."-".$request->day_from;
                        $dateTo = $request->value."-".$request->month_to."-".$request->day_to;
                        
                        $updatedDates['lease_from_date'] = date("Y-m-d", strtotime($dateFrom));
                        $updatedDates['lease_from_time'] = $request->lease_from_time;
                        $updatedDates['lease_to_date'] = date("Y-m-d", strtotime($dateTo));
                        $updatedDates['lease_to_time'] = $request->lease_to_time;

                        Booking::where('id', $booking->id)->update($updatedDates);

                        $roomTypeData = RoomTypes::where('id', $booking->roomType)->first();
                        $is_nsfas = false;
                        if($roomTypeData->use_nsfas == 1){
                            $is_nsfas = true;
                        }

                        $paymentBooking = BookingPayment::where('bookingId', $user->bookingId)->first();

                        $diff = 0;
                        for($i = new DateTime($updatedDates['lease_from_date']); $i <= new DateTime($updatedDates['lease_to_date']); $i->modify('+1 month')){
                            $diff++;
                        }
                        
                        $yearly_rent = floatval($paymentBooking->monthly_rental) * floatval($diff);

                        $institution = Lookups::where('id', $user->institutionStudy)->first();
                        $funded = floatval($institution->nsfas_amount) * floatval($diff);
                        
                        $topup = floatval($yearly_rent) - floatval($funded);
                        $monthlyFunded = $institution->nsfas_amount;

                        if(($user->studentType == "Cash / Direct") || ($user->studentType == "Bursary" && $user->nsfas_bursar !== 1)){
                            $funded = $yearly_rent;
                            $topup = '0';
                            $monthlyFunded = $paymentBooking->monthly_rental;
                        }else{
                            $funded = $funded;
                            $topup = $topup;

                            if($is_nsfas === true){
                                $topup = 0;
                            }
                        }

                        BookingPayment::where('bookingId', $user->bookingId)->update(["monthly_rental_total" => $yearly_rent, "total_funded" => $funded, "total_top_up" => $topup, "funded_monthly" => $monthlyFunded]);


                        if($booking->lease_from_date !== $updatedDates['lease_from_date']){
                            ActionList::create([
                                "userUid" => $user->userUid,
                                "action" => "Bulk Lease From Date Updated From ".$booking->lease_from_date." to ".$updatedDates['lease_from_date'],
                                "actionUserUid" => Auth::user()->id,
                                "actionUserDisplayName" => Auth::user()->userFirstName,
                                "actionUserRole" => Auth::user()->userRole,
                                "year" => $this_year,
                            ]);
                        }

                        if($booking->lease_from_time !== $request->lease_from_time){
                            ActionList::create([
                                "userUid" => $user->userUid,
                                "action" => "Bulk Lease From Time Updated From ".$booking->lease_from_time." to ".$request->lease_from_time,
                                "actionUserUid" => Auth::user()->id,
                                "actionUserDisplayName" => Auth::user()->userFirstName,
                                "actionUserRole" => Auth::user()->userRole,
                                "year" => $this_year,
                            ]);
                        }

                        if($booking->lease_to_date !== $updatedDates['lease_to_date']){
                            ActionList::create([
                                "userUid" => $user->userUid,
                                "action" => "Bulk Lease To Date Updated From ".$booking->lease_to_date." to ".$updatedDates['lease_to_date'],
                                "actionUserUid" => Auth::user()->id,
                                "actionUserDisplayName" => Auth::user()->userFirstName,
                                "actionUserRole" => Auth::user()->userRole,
                                "year" => $this_year,
                            ]);
                        }

                        if($booking->lease_to_time !== $request->lease_to_time){
                            ActionList::create([
                                "userUid" => $user->userUid,
                                "action" => "Bulk Lease To Time Updated From ".$booking->lease_to_time." to ".$request->lease_to_time,
                                "actionUserUid" => Auth::user()->id,
                                "actionUserDisplayName" => Auth::user()->userFirstName,
                                "actionUserRole" => Auth::user()->userRole,
                                "year" => $this_year,
                            ]);
                        }
                    }
            }
        }
    }

    public function get_comment(Request $request){
        $commentList = Comments::with('users')->where('user_uid', $request->userId)->where('bookingId', $request->bookingId)->orderBy('created_at', 'desc')->get();

        return $commentList;
    }

    public function add_comment(Request $request){
        
        Comments::create([
            'user_uid' => $request->userId,
            'comment' => $request->comment,
            'action_user_uid' => Auth::user()->id,
            'bookingId' => $request->bookingId,
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

        $xlsx = new \Shuchkin\SimpleXLSXGen();
		$xlsx->addSheet( $users, 'Students');
		$xlsx->saveAs('NSFAS Approval Export.xlsx');
        // $xlsx->downloadAs('NSFAS Approval Export.xlsx');

		return "true";
        // $this->download_nsfas_report();
        // return $users;
    }

    public function delete_nsfas($id){
        //
        ImportNSFASApproval::where('id', $id)->delete();
        ImportNSFASStudentAuditLog::where('importId', $id)->delete();

        return 'true';
    }

    public function download_nsfas_report(Request $request){

        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        // $studentArr = User::where('studentType', 'NSFAS')->get();
        $studentArr = StudentsInfo::where('studentType', 'NSFAS')->get();

        $users = [
			['<b>Student Number</b>', '<b>First Name</b>', '<b>Last Name</b>', '<b>ID Number</b>', '<b>Institution</b>', '<b>Status</b>', '<b>Approved By</b>'],
		];

        $lookups = Lookups::where('group_id', '1')->get();

        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        foreach($studentArr as $student){
            $file = UserFiles::where('userUid', $student->id)->where('fileId', 'bursaryApproval')->where('year', $this_year)->first();

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

        $xlsx = new \Shuchkin\SimpleXLSXGen();
		$xlsx->addSheet( $users, 'Students');
		$xlsx->saveAs('NSFAS Approval Type Export.xlsx');

		// return "true";
        return $users;
    }

    public function update_nsfas(Request $request){
        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        $files = UserFiles::where('fileId', "bursaryApproval")->where('fileUrl', null)->where('year', $this_year)->get();

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

    public function check_user_status($student_id) {

        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        // $user = User::where('id', $student_id)->first();
        $user = StudentsInfo::where('userUid', $student_id)->where('year', $this_year)->first();

        if (!empty($user)) {
            return ["rob_10_fee" => $user->rob_10_fee, "rob_15_id" => $user->rob_15_id, "rob_20_card" => $user->rob_20_card , "rob_25_reg" => $user->rob_25_reg , "rob_30_funds" => $user->rob_30_funds , "rob_35_info" => $user->rob_35_info , "rob_40_bed" => $user->rob_40_bed, "rob_45_other" => $user->rob_45_other,  "rob_50_approve" => $user->rob_50_approve, "rob_60_cancelled" => $user->rob_60_cancelled, 'parent_approved' => $user->parent_approved, 'burser_approved' => $user->burser_approved, 'burCompanyName' => $user->burCompanyName, 'sponFirstName' => $user->sponFirstName, 'kinFirstName' => $user->kinFirstName ];
        } else {
            return false;
        }
    }

    public function check_user_payment($student_id){
        
        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        // $user = User::where('id', $student_id)->first();
        $user = StudentsInfo::where('userUid', $student_id)->where('year', $this_year)->first();

        if (!empty($user)) {
            return ["burCompanyName" => $user->burCompanyName, "burRegNo" => $user->burRegNo, "burContactPerson" => $user->burContactPerson , "burContactCode" => $user->burContactCode ,"burContactNo" => $user->burContactNo , "burEmail" => $user->burEmail , "bAddress1" => $user->bAddress1 , "bAddress2" => $user->bAddress2, "bAddressCity" => $user->bAddressCity,  "bAddressPostal" => $user->bAddressPostal, "nsfas_bursar" => $user->nsfas_bursar, "studentType" => $user->studentType, 'bPayment' => $user->bPayment, "bint" => $user->bint, "bIntName" => $user->bIntName, "sponFirstName" => $user->sponFirstName, "sponLastName" => $user->sponLastName, "sponCell" => $user->sponCell, "sponCellCode" => $user->sponCellCode, "sponEmail" => $user->sponEmail, "sponId" => $user->sponId, "pNationality" => $user->pNationality, "pPassportNo" => $user->pPassportNo, "pAddress1" => $user->pAddress1, "pAddress2" => $user->pAddress2, "pAddressCity" => $user->pAddressCity, "pAddressPostal" => $user->pAddressPostal];
        } else {
            return false;
        }
    }

    public function unallocate_bed(Request $request) {
        $userId = $request->userId;

        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        $booking_old = Booking::where('userId', $userId)->where('year', $this_year)->first();

        Booking::where('userId', $userId)->where('year', $this_year)->update(['property_data_id' => null, 'floor' => null, 'unit' => null, 'room' => null, 'bed' => null, 'bed_code' => null]);
        Property::where('id', $booking_old->property_data_id)->update(["user_id_room" => null, "room_available" => null]);

        //Update the User robot
        // User::where('id', $userId)->update(['rob_40_bed' => 1, 'rob_50_approve' => 0]);
        StudentsInfo::where('userUid', $userId)->where('year', $this_year)->update(['rob_40_bed' => 1, 'rob_50_approve' => 0]);

        //Delete the Lease
        UserFiles::where('userUid', $userId)->where('fileId', 'moveIn')->where('year', $this_year)->delete();

        // $hasMda = UserFiles::where('userUid', $userId)->where('year', $this_year)->where('fileId', 'MDA')->first();

        // if(!empty($hasMda)){
        //     UserFiles::where('userUid', $userId)->where('year', $this_year)->where('fileId', 'MDA')->delete();
        // }
        

        ActionList::create([
            "userUid" => $userId,
            "action" => "Unallocated Bed ".$booking_old->bed_code." on the ".$booking_old->floor." floor, unit ".$booking_old->unit,
            "actionUserUid" => Auth::user()->id,
            "actionUserDisplayName" => Auth::user()->userFirstName,
            "actionUserRole" => Auth::user()->userRole,
            "year" => $this_year,
        ]);

        // $status_data = User::where('id', $userId)->first();

        return 'true';
    }

    public function change_nsfsbursar(Request $request){

        // $user = User::where('id', $request->userId)->first();
        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        $user = StudentsInfo::where('userUid',  $request->userId)->where('year', $this_year)->first();

        if($user->nsfas_bursar == 1){
            $bursar = null;
            $oldValue = "Yes";
            $newValue = "No";
        }else{
            $bursar = 1;
            $oldValue = "No";
            $newValue = "Yes";
        }

        StudentsInfo::where('userUid',  $request->userId)->where('year', $this_year)->update(['nsfas_bursar' => $bursar]);

        ActionList::create([
            "userUid" => $user->userUid,
            "action" => "Updated NSFAS Bursar from ".$oldValue." to ".$newValue,
            "actionUserUid" => Auth::user()->id,
            "actionUserDisplayName" => Auth::user()->userFirstName,
            "actionUserRole" => Auth::user()->userRole,
            "year" => $this_year,
        ]);

        return 'done';

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

    public function all_student_update_status(){
        $students = User::where('userRole', '0')->get();

        foreach($students as $student){
            $this->update_user_status($student->id);
        }
    }

    public function edit_inline_data(Request $request) {

        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        $user = User::find($request->userUid);
        $userInfo = StudentsInfo::where('userUid', $request->userUid)->where('year', $this_year)->first();

        if ($request->formId == "inline_edit_address") {
            if ($request->type == "form") {
                ?>
                <form class="edit_info_inline">
                    <div class="form-field">    
                        <span style='font-weight:bold'>Address 1:</span>
                        <input class="noedit required" name="userAddress1" id="userAddress1" type="text" value="<?php echo $userInfo->userAddress1; ?>">
                        <span id="userAddress1_error" class="hidden" style="color:red;width: 90%;padding-left: 40%;"><small>This field is required</small></span>
                    </div> 
                    
                    <div class="form-field">    
                        <span style='font-weight:bold'>Address 2:</span>
                        <input class="noedit required" name="userAddress2" id="userAddress2" type="text" value="<?php echo $userInfo->userAddress2; ?>">
                        <span id="userAddress2_error" class="hidden" style="color:red;width: 90%;padding-left: 40%;"><small>This field is required</small></span>
                    </div> 
                    </div> 
                    
                    <div class="form-field">    
                        <span style='font-weight:bold'>City:</span>
                        <input class="noedit required" name="userAddressCity" id="userAddressCity" type="text" value="<?php echo $userInfo->userAddressCity; ?>">
                        <span id="userAddressCity_error" class="hidden" style="color:red;width: 90%;padding-left: 40%;"><small>This field is required</small></span>
                    </div> 
                    </div> 
                    
                    <div class="form-field">    
                        <span style='font-weight:bold'>Postal:</span>
                        <input class="noedit required" name="userAddressPostal" id="userAddressPostal" type="text" value="<?php echo $userInfo->userAddressPostal; ?>">
                        <span id="userAddressPostal_error" class="hidden" style="color:red;width: 90%;padding-left: 40%;"><small>Not a valid postal code</small></span>
                    </div> 

                    <input type="hidden" name="task" value="<?php echo $request->formId; ?>">
                    <input type="hidden" name="userUid" value="<?php echo $request->userUid; ?>">

                    <button type="button" class="approve button btn_save_inline" formid="<?php echo $request->formId; ?>" edittype="data">Update</button>
                    <button type="button" class="rejected button btn_edit_inline" formid="<?php echo $request->formId; ?>" edittype="data">Cancel</button>
                </form>
                <?php
            } else {
                echo $userInfo->userAddress1 . "<br/>";
                echo $userInfo->userAddress2 . "<br/>";
                echo $userInfo->userAddressCity . "<br/>";
                echo $userInfo->userAddressPostal . "<br/>";

                ?>
                <!-- <button type="button" class="approve button btn_approve_inline" formid="inline_edit_address" approvefield="address_approved" style="height:40px;">Approve</button>
                <button type="button" class="amend button btn_edit_inline" formid="<?php echo $request->formId; ?>" edittype="form">Edit</button> -->
                <div class="address_btn"></div>
                <?php
            }

        } elseif ($request->formId == "inline_edit_kin") {
            if ($request->type == "form") {
                ?>
                <form class="edit_info_inline">
                    <div class="form-field">    
                        <span style='font-weight:bold'>Title:</span>
                        <select name="kinTitle" id='kinTitle' class="noedit required">
                            <option value="" disabled selected>Select one</option>
                            <option value="Mr" <?php if($userInfo->kinTitle == "Mr"){echo "selected";} ?>>Mr</option>
                            <option value="Ms" <?php if($userInfo->kinTitle == "Ms"){echo "selected";} ?>>Ms</option>
                            <option value="Mrs" <?php if($userInfo->kinTitle == "Mrs"){echo "selected";} ?>>Mrs</option>
                            <option value="Miss" <?php if($userInfo->kinTitle == "Miss"){echo "selected";} ?>>Miss</option>
                        </select>
                    </div> 
                    
                    <div class="form-field">    
                        <span style='font-weight:bold'>First Name *</span>
                        <input class="noedit required" name="kinFirstName" id="kinFirstName" type="text" value="<?php echo $userInfo->kinFirstName; ?>">
                        <span id="kinFirstName_error" class="hidden" style="color:red;width: 90%;padding-left: 40%;"><small>This field is required</small></span>
                    </div> 
                    </div> 
                    
                    <div class="form-field">    
                        <span style='font-weight:bold'>Last Name *</span>
                        <input class="noedit required" name="kinLastName" id="kinLastName" type="text" value="<?php echo $userInfo->kinLastName; ?>">
                        <span id="kinLastName_error" class="hidden" style="color:red;width: 90%;padding-left: 40%;"><small>This field is required</small></span>
                    </div> 
                    </div> 
                    
                    <div class="form-field">    
                        <span style='font-weight:bold'>Relationship *</span>
                        <input class="noedit required" name="kinRelationship" id="kinRelationship" type="text" value="<?php echo $userInfo->kinRelationship; ?>">
                        <span id="kinRelationship_error" class="hidden" style="color:red;width: 90%;padding-left: 40%;"><small>This field is required</small></span>
                    </div> 

                    <div class="form-field">    
                        <span style='font-weight:bold'>Cellphone Number *</span>
                        <input class="noedit required" name="kinCellCode" id="kinCellCode" type="text" value="<?php echo $userInfo->kinCellCode; ?>" style="width:13%; margin-right:2%">
                        <input class="noedit required" name="kinCellNo" id="kinCellNo" type="text" value="<?php echo $userInfo->kinCellNo; ?>" style="width:35%">
                        <span id="kinCellNo_error" class="hidden" style="color:red;width: 90%;padding-left: 40%;"><small>Not a valid cellphone number</small></span>
                        <span id="kinCellNo_duplicate" class="hidden" style="color:red;width: 90%;padding-left: 40%;"><small>Duplicate detected!</small></span>
                        <!-- <input name="userCell" id="userCell" type="hidden" value="<?php echo $userInfo->userCell; ?>"> -->
                    </div> 

                    <div class="form-field">    
                        <span style='font-weight:bold'>Email Address</span>
                        <input class="noedit" name="kinEmail" id="kinEmail" type="text" value="<?php echo $userInfo->kinEmail; ?>">
                        <span id="kinEmail_error" class="hidden" style="color:red;width: 90%;padding-left: 40%;"><small>Not a valid email</small></span>
                    </div> 
                    
                    <div class="form-field">    
                        <span style='font-weight:bold'>Physical Address Street *</span>
                        <input class="noedit required" name="kinAddressStr" id="kinAddressStr" type="text" value="<?php echo $userInfo->kinAddressStr; ?>">
                        <span id="kinAddressStr_error" class="hidden" style="color:red;width: 90%;padding-left: 40%;"><small>This field is required</small></span>
                    </div> 

                    <div class="form-field">    
                        <span style='font-weight:bold'>Physical Address Suburb *</span>
                        <input class="noedit required" name="kinAddressSuburb" id="kinAddressSuburb" type="text" value="<?php echo $userInfo->kinAddressSuburb; ?>">
                        <span id="kinAddressSuburb_error" class="hidden" style="color:red;width: 90%;padding-left: 40%;"><small>This field is required</small></span>
                    </div> 

                    <div class="form-field">    
                        <span style='font-weight:bold'>Physical Address City *</span>
                        <input class="noedit required" name="kinAddressCity" id="kinAddressCity" type="text" value="<?php echo $userInfo->kinAddressCity; ?>">
                        <span id="kinAddressCity_error" class="hidden" style="color:red;width: 90%;padding-left: 40%;"><small>This field is required</small></span>
                    </div> 

                    <div class="form-field">    
                        <span style='font-weight:bold'>Physical Address Postal *</span>
                        <input class="noedit required" name="kinAddressPost" id="kinAddressPost" type="text" value="<?php echo $userInfo->kinAddressPost; ?>">
                        <span id="kinAddressPost_error" class="hidden" style="color:red;width: 90%;padding-left: 40%;"><small>Not a valid postal code</small></span>
                    </div> 

                    <input type="hidden" name="task" value="<?php echo $request->formId; ?>">
                    <input type="hidden" name="userUid" value="<?php echo $request->userUid; ?>">

                    <button type="button" class="approve button btn_save_inline" formid="<?php echo $request->formId; ?>" edittype="data">Update</button>
                    <button type="button" class="rejected button btn_edit_inline" formid="<?php echo $request->formId; ?>" edittype="data">Cancel</button>
                </form>
                <?php
            } else {
                echo $userInfo->kinTitle . " " . $userInfo->kinFirstName . " " . $userInfo->kinLastName . "<br/>";
                echo $userInfo->kinRelationship . "<br/>";
                echo $userInfo->kinCellCode.$user->kinCellNo . "<br/>";
                echo $userInfo->kinEmail . "<br/>";
                echo $userInfo->kinAddressStr . ", " . $userInfo->kinAddressSuburb . "<br/>";
                echo $userInfo->kinAddressCity . ", " . $userInfo->kinAddressPost . "<br/>";

                ?>
                <!-- <button type="button" class="approve button btn_approve_inline" formid="inline_edit_address" approvefield="kin_approved" style="height:40px;">Approve</button>
                <button type="button" class="amend button btn_edit_inline" formid="<?php echo $request->formId; ?>" edittype="form">Edit</button> -->
                <div class="kin_btn"></div>
                <?php
            }

        } elseif ($request->formId == "inline_app_details") {
            if ($request->type == "form") {
                ?>
                <form class="edit_info_inline">
                    <div class="form-field">    
                        <span style='font-weight:bold'>Email *</span>
                        <input class="noedit required" name="userEmail" id="userEmail" type="text" value="<?php echo $userInfo->userEmail; ?>">
                        <span id="userEmail_error" class="hidden" style="color:red;width: 90%;padding-left: 40%;"><small>Not a valid email</small></span>
                    </div> 
                    
                    <div class="form-field">    
                        <span style='font-weight:bold'>Cellphone Number *</span>
                        <input class="noedit required" name="userCellCode" id="userCellCode" type="text" value="<?php echo $userInfo->userCellCode; ?>" style="width:8%; margin-right:2%">
                        <input class="noedit required" name="userCell" id="userCell" type="text" value="<?php echo $userInfo->userCell; ?>" style="width:40%">
                        <span id="userCell_error" class="hidden" style="color:red;width: 90%;padding-left: 40%;"><small>Not a valid cellphone Number</small></span>
                        <span id="cell_no_duplicate" class="hidden" style="color:red;width: 90%;padding-left: 40%;"><small>Duplicate detected!</small></span>
                        <!-- <input type="hidden" name="kinCellNo" id="kinCellNo" value="<?php echo $userInfo->kinCellNo; ?>"> -->
                    </div>

                    <input type="hidden" name="task" value="<?php echo $request->formId; ?>">
                    <input type="hidden" name="userUid" value="<?php echo $request->userUid; ?>">

                    <button type="button" class="approve button btn_save_inline" formid="<?php echo $request->formId; ?>" edittype="data">Update</button>
                    <button type="button" class="rejected button btn_edit_inline" formid="<?php echo $request->formId; ?>" edittype="data">Cancel</button>
                </form>
                <?php
            } else {
                $this_year = Lookups::where('group_id', '4')->first();
                $this_year = $this_year->lookup_name;

                $booking = Booking::where('userId', $user->id)->where('year', $this_year)->first();
                if($userInfo->email_verified == '1'){
                    echo '<a href="mailto:'.$userInfo->userEmail.'?subject='.$booking->booking_code.'">'.$userInfo->userEmail . "</a><i class='fas fa-check' style='color:green'></i><br/>"; 
                    echo '<input type="hidden" id="emailverifiedStatus" value="1"/>';
                }else{
                    $mailVerificationEmail = MailerList::where("mailer_name", "Password Validation From Admin")->first();
                    
                    if($mailVerificationEmail->status == '1'){
                        echo '<a href="mailto:'.$userInfo->userEmail.'?subject=Campus Africa Ref '.$booking->booking_code.'">'.$userInfo->userEmail . "</a> &#10060; &nbsp;&nbsp; <button type='button' class='unallocateBed' onClick='reVerifyEmail()' id='reVerify'>Resend Email verification</button><br/>";
                    }
                    echo '<input type="hidden" id="emailverifiedStatus" value="0"/>';
                }

                $number = substr($userInfo->userCellCode, 1).$userInfo->userCell;
                
                
                echo '<a href="https://wa.me/'.$number.'?text=Campus%20Africa%20Ref%20{{$booking->booking_code}}%0A%0A%0A%0A" target="_blank">'.$userInfo->userCellCode.$userInfo->userCell . "<br/>";

                ?>
                <!-- <button type="button" class="approve button btn_approve_inline" formid="<?php echo $request->formId; ?>" approvefield="email_data_approved" style="height:40px;">Approve</button>
                <button type="button" class="amend button btn_edit_inline" formid="<?php echo $request->formId; ?>" edittype="form">Edit</button> -->
                <div class="app_details_btn"></div>
                <?php
            }
        }else if($request->formId == "inline_edit_student_type"){
            if ($request->type == "form") {
                ?>
                <form class="edit_info_inline">
                    <span style='font-weight:bold'>Student Type</span>
                    <div class="form-field">    
                        <br/>
                        <label> <span id="studentType_error" class="hidden" style="color:red"><small>This field is required</small></span></label>
                            <select name="studentType" id='studentType' class="required">
                                <option value="" selected disabled>Select a Student Type</option>
                                <option value="NSFAS" <?php if($userInfo->studentType == "NSFAS"){echo "selected";} ?>>NSFAS</option>
                                <option value="Bursary" <?php if($userInfo->studentType == "Bursary"){echo "selected";} ?>>Bursary</option>
                                <option value="Cash / Direct" <?php if($userInfo->studentType == "Cash / Direct"){echo "selected";} ?>>Cash</option>
                            </select>
                    </div> 
                    
                    <input type="hidden" name="task" value="<?php echo $request->formId; ?>">
                    <input type="hidden" name="userUid" value="<?php echo $request->userUid; ?>">

                    <button type="button" class="approve button btn_save_inline" formid="<?php echo $request->formId; ?>" edittype="data">Update</button>
                    <button type="button" class="rejected button btn_edit_inline" formid="<?php echo $request->formId; ?>" edittype="data">Cancel</button>
                </form>
                <?php
            } else {
                echo '<p class="listHeader">Student Type</p>';

                if($userInfo->studentType == "Cash / Direct"){
                    echo '<span id="studentUserType" class="Cash / Direct">Cash</span><br/>';
                }else{
                    echo '<span id="studentUserType" class="'.$userInfo->studentType.'">'.$userInfo->studentType.'</span><br/>';
                }

                ?>

                <div class="studentType_btn"></div>
                <?php
            }
        }
    }

    public function save_inline_data(Request $request) {
        $oldData = User::find($request->userUid);

        if ($request->task == "inline_edit_address") {
            $newData = [
                'userAddress1|Address 1' => $request->userAddress1,
                'userAddress2|Address 2' => $request->userAddress2,
                'userAddressCity|City' => $request->userAddressCity,
                'userAddressPostal|Postal' => $request->userAddressPostal
            ];

            $updateData = [
                'userAddress1' => $request->userAddress1,
                'userAddress2' => $request->userAddress2,
                'userAddressCity' => $request->userAddressCity,
                'userAddressPostal' => $request->userAddressPostal
            ];

        }elseif($request->task == 'inline_edit_student_type'){
            $newData = [
                'studentType|Student Type' => $request->studentType,
            ];

            $updateData = [
                'studentType' => $request->studentType,
                'rob_30_funds' => 0,
                'rob_50_approve' => 0,
                'rob_55_lease' => 3,
                'burser_approved' => null,
                'parent_approved' => null,
            ];

            

            $this_year = Lookups::where('group_id', '4')->first();
            $this_year = $this_year->lookup_name;

            $lease = UserFiles::where('userUid', $request->userUid)->where('year', $this_year)->where('fileId', 'moveIn')->first();
            // $mda = UserFiles::where('userUid', $request->userUid)->where('year', $this_year)->where('fileId', 'MDA')->first();
            $bursary = UserFiles::where('userUid', $request->userUid)->where('year', $this_year)->where('fileId', 'bursaryApproval')->first();
            $bankStatement = UserFiles::where('userUid', $request->userUid)->where('year', $this_year)->where('fileId', 'bankStatement')->first();

            if(!empty($lease)){
                UserFiles::where('userUid', $request->userUid)->where('year', $this_year)->where('fileId', 'moveIn')->delete();
            }
            // if(!empty($mda)){
            //     UserFiles::where('userUid', $request->userUid)->where('year', $this_year)->where('fileId', 'MDA')->delete();
            // }
            if(!empty($bursary)){
                UserFiles::where('userUid', $request->userUid)->where('year', $this_year)->where('fileId', 'bursaryApproval')->update(['status' => 'Rejected']);
            }
            if(!empty($bankStatement)){
                UserFiles::where('userUid', $request->userUid)->where('year', $this_year)->where('fileId', 'bankStatement')->update(['status' => 'Rejected']);
            }

            $userInfo = StudentsInfo::where('userUid', $request->userUid)->where('year', $this_year)->first();

            // if($request->studentType == "NSFAS"){
            //     $booking = Booking::where('id', $userInfo->bookingId)->first();
            //     // $ts1 = strtotime($booking->lease_from_date);
            //     // $ts2 = strtotime($booking->lease_to_date);
            //     // $month1 = date('m', $ts1);
            //     // $month2 = date('m', $ts2);
            //     // $diff = $month2 - $month1;
            //     $diff = 0;
            //     for($i = new DateTime($booking->lease_from_date); $i <= new DateTime($booking->lease_to_date); $i->modify('+1 month')){
            //         $diff++;
            //     }

            //     $institution = Lookups::where('id', $userInfo->institutionStudy)->first();
            //     $funded = floatval($institution->nsfas_amount) * floatval($diff);
            //     $thisBookingPayment = BookingPayment::where('bookingId', $userInfo->bookingId)->first();
            //     BookingPayment::where('bookingId', $userInfo->bookingId)->update([
            //         'deposit' => '0',
            //         'loan_admin_fee' => '0',
            //         'total_funded' => $funded,
            //         'total_top_up' => floatval($thisBookingPayment->monthly_rental_total) - floatval($funded),
            //         'funded_monthly' => $institution->nsfas_amount
            //     ]);
            // }
        } elseif ($request->task == "inline_edit_kin") {
            $newData = [
                'kinTitle|Title' => $request->kinTitle,
                'kinFirstName|First Name' => $request->kinFirstName,
                'kinLastName|Last Name' => $request->kinLastName,
                'kinRelationship|Relationship' => $request->kinRelationship,
                // 'kinNationality|Postal' => $request->kinNationality,
                // 'kinId|Postal' => $request->kinId,
                // 'kinPassportNo|Postal' => $request->kinPassportNo,
                'kinCellNo|Cellphone Number' => $request->kinCellNo,
                'kinCellCode|Country Code' => $request->kinCellCode,
                'kinEmail|Email' => $request->kinEmail,
                'kinAddressStr|Physical Address Street' => $request->kinAddressStr,
                'kinAddressSuburb|Physical Address Suburb' => $request->kinAddressSuburb,
                'kinAddressCity|Physical Address City' => $request->kinAddressCity,
                'kinAddressPost|Physical Address Postal' => $request->kinAddressPost,
            ];

            $updateData = [
                'kinTitle' => $request->kinTitle,
                'kinFirstName' => $request->kinFirstName,
                'kinLastName' => $request->kinLastName,
                'kinRelationship' => $request->kinRelationship,
                // 'kinNationality' => $request->kinNationality,
                // 'kinId' => $request->kinId,
                // 'kinPassportNo' => $request->kinPassportNo,
                'kinCellNo' => $request->kinCellNo,
                'kinCellCode' => $request->kinCellCode,
                'kinEmail' => $request->kinEmail,
                'kinAddressStr' => $request->kinAddressStr,
                'kinAddressSuburb' => $request->kinAddressSuburb,
                'kinAddressSuburb' => $request->kinAddressSuburb,
                'kinAddressCity' => $request->kinAddressCity,
                'kinAddressPost' => $request->kinAddressPost,
                'rob_35_info' => 1,
            ];

            $this_year = Lookups::where('group_id', '4')->first();
            $this_year = $this_year->lookup_name;

            $userInfoUse = StudentsInfo::where('userUid', $request->userUid)->where('year', $this_year)->first();
        
        } elseif ($request->task == "inline_app_details") {
            $newData = [
                'userEmail|Email' => $request->userEmail,
                'userCell|Cellphone Number' => $request->userCell,
                'userCellCode|Country Code' => $request->userCellCode,
            ];

            $updateData = [
                'userEmail' => $request->userEmail,
                // 'email' => $request->userEmail,
                'userCell' => $request->userCell,
                'userCellCode' => $request->userCellCode,
            ];

            User::where('id', $request->userUid)->update(['email' => $request->userEmail]);

            $this_year = Lookups::where('group_id', '4')->first();
            $this_year = $this_year->lookup_name;

            $userBefore = User::where('id', $request->userUid)->first();
            $userInfo = StudentsInfo::where('userUid', $request->userUid)->where('year', $this_year)->first();

            if($request->userEmail !== $userInfo->userEmail){
                // User::where('id', $request->userUid)->update(["email_verified" => 0]);
                StudentsInfo::where('userUid', $request->userUid)->where('year', $this_year)->update(["email_verified" => 0]);

                $booking = Booking::where('userId', $request->userUid)->where('year', $this_year)->first();

                $generator = new LoginUrl($userBefore);
                $generator->setRedirectUrl('/verify-new-email-account');
                $url = $generator->generate();

                $message_replace['bookingRef'] = $booking->booking_code;
                $message_replace['name'] = $userInfo->userFirstName;
                $message_replace['application_link'] = "<a href='".$url."' target='_blank'>";
                $message_replace['close_application_link']="</a>";

                $mailerForMessage = MailerList::where("mailer_name", "Password Validation From Admin")->first();

                if($mailerForMessage->status == '1'){

                    $mailerMessage = $mailerForMessage->message;
    
                    $newMessage = str_replace("[application_number]", "#".$message_replace['bookingRef'], $mailerMessage);
                    $newMessage = str_replace("[name]", $message_replace['name'], $newMessage);
                    $newMessage = str_replace("[application_link]", $message_replace['application_link'], $newMessage);
                    $newMessage = str_replace("[close_application_link]", $message_replace['close_application_link'], $newMessage);
    
                    $data['name'] = $userInfo->userFirstName;
                    $data['email'] = $request->userEmail;
                    $data['messageForMail'] = $newMessage;
                    $data['subject'] = str_replace("[application_number]", $message_replace['bookingRef'], $mailerForMessage->subject);

                    

                    $allowedMailsCC = Lookups::where('group_id', '11')->pluck('lookup_name')->first();
                    $data['cc'] = explode(";", $allowedMailsCC);

                    $allowedMails = Lookups::where('group_id', '10')->pluck('lookup_name')->first();

                    if(!empty($allowedMails)){

                        $mailArr = explode(";", $allowedMails);
    
                        if(in_array(explode("@", $data['email'])[1], $mailArr)){
                            $data['email'] = $request->userEmail;
                        }else{
                            $data['email'] = "conurbandigital@gmail.com";
                        }
                    }else{
                        $data['email'] = $request->userEmail;
                    }
                    
                    
                    
                    
    
                    // if(explode("@", $data['email'])[1] == "conurban.co.za"){
                    //     $data['email'] = $request->userEmail;
                    // }else{
                    //     $data['email'] = "conurbandigital@gmail.com";
                    // }
    
                    Mail::send('email.yellow_back', $data, function($message) use ($data) {
                        // $message->to($data['email'], $data['name'])->subject
                        //     ('Campus Africa User Application');
                        $message->to($data['email'])->subject($data['subject']);
                        $message->bcc($data['cc']);
                        // $message->cc(['conurbandigital@gmail.com', 'roger@whitehart.co.za']);
                        $message->from('noreply@campusafrica.co.za','Campus Africa');
                    });
                }
            }
        }

        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        if (isset($newData)) {
            foreach($newData as $key => $new) {                
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
                        "year" => $this_year,
                    ]);
                }
            }

            User::where('id', $request->userUid)->update($updateData);

            StudentsInfo::where('userUid', $request->userUid)->where('year', $this_year)->update($updateData);
            
        }

        if($request->task == 'inline_edit_student_type'){
            $this->paymentDeatilsInternal($request->userUid);
        }

        echo "done";
    }
    
    public function approve_inline_data(Request $request) {
        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        // $oldData = User::find($request->userUid);    
        $oldUserInfo = StudentsInfo::where('userUid', $request->userUid)->where('year', $this_year)->first();

        if ($request->formId == "inline_edit_address") {
            $newData = [
                $request->approvefield . '|Physical Address' => $request->task == 1 ? "Approved" : "Rejected",
            ];

        } elseif ($request->formId == "inline_edit_kin") {
            $newData = [
                $request->approvefield . '|Next of Kin' => $request->task == 1 ? "Approved" : "Rejected",
            ];

        } elseif ($request->formId == "inline_app_details") {
            $newData = [
                $request->approvefield . '|Application Details' => $request->task == 1 ? "Approved" : "Rejected",
            ];
        }

        if (isset($newData)) {
            $updateData = [
                $request->approvefield => $request->task
            ];

            foreach($newData as $key => $new) {                
                $info = explode("|", $key);
                $key = $info[0];
                $changedField = $info[1];

                if($oldUserInfo->$key !== $new) {
                    ActionList::create([
                        "userUid" => $request->userUid,
                        "action" => $new . " " . $changedField,
                        "actionUserUid" => Auth::user()->id,
                        "actionUserDisplayName" => Auth::user()->userFirstName,
                        "actionUserRole" => Auth::user()->userRole,
                        "year" => $this_year,
                    ]);
                }
            }

            // User::where('id', $request->userUid)->update($updateData);
            StudentsInfo::where('userUid', $request->userUid)->where('year', $this_year)->update($updateData);
        }

        //Update Info robot based on data approved
        // $checkData = User::find($request->userUid);
        $checkData = StudentsInfo::where('userUid', $request->userUid)->where('year', $this_year)->first();
        
        if ($checkData->address_approved == 1 && $checkData->kin_approved == 1 && $checkData->email_data_approved == 1) {
            $rob_35_info = 2;
        } else {
            $rob_35_info = 1;
        }

        // User::where('id', $request->userUid)->update([
        //     "rob_35_info" => $rob_35_info
        // ]);

        StudentsInfo::where('userUid', $request->userUid)->where('year', $this_year)->update([
            "rob_35_info" => $rob_35_info
        ]);
    

        // $status_data = User::where('id', $request->userUid)->first();
        $status_data = StudentsInfo::where('userUid', $request->userUid)->where('year', $this_year)->first();

        if($status_data->rob_10_fee == 2 && $status_data->rob_15_id == 2 && $status_data->rob_20_card == 2 && $status_data->rob_25_reg == 2 && $status_data->rob_30_funds == 2 && $status_data->rob_35_info == 2 && $status_data->rob_40_bed == 2 && ($status_data->rob_45_other == 3 || $status_data->rob_45_other == 2) && $status_data->rob_50_approve == 0){
            // User::where('id', $request->userUid)->update(['rob_50_approve' => 1]);
            StudentsInfo::where('userUid', $request->userUid)->where('year', $this_year)->update(['rob_50_approve' => 1]);
        }else{
            // User::where('id', $request->userUid)->update(['rob_50_approve' => 0]);
            StudentsInfo::where('userUid', $request->userUid)->where('year', $this_year)->update(['rob_50_approve' => 0]);
        }

        echo "done";
    }


    public function properties_filter(Request $request){

        //Get current booking year
        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;
        
        $search_value = [];

        
        if ($request->firstLoad !== "1") {
            Session::put('property', $request->property);
            Session::put('year', $request->year);

        } else {
            $request->property = Session::get('property');
            $request->year = Session::get('year');
        }        

        if($request->property !== "" && !empty($request->property)){
            if($request->property !== "All"){
                $property = 'residence = "'.$request->property.'"';
                array_push($search_value, $property);
            }
        }       

        $query = [];
        

        foreach($search_value as $value) {
            $statement = " and " . $value;
            array_push($query, $statement);
        }

        //Default Query
        $sql = 'SELECT id, residence, floor, unit, room, bed, bed_code, inventory, year, year_end, room_available, room_type, room_rate
            FROM property_data
            WHERE 1=1 ' . implode($query) . '
            
            order by residence, cast(floor as unsigned), cast(unit as unsigned), room, bed, year, year_end ASC';

        //Get all of our students
        $searched_data = DB::select($sql);

        $userData = [];

        foreach($searched_data as $data) {            
            //Filter data based on the users roles
            if(
                (Auth::user()->role_braamlofts == "1" && ($data->residence == "BRAAMLOFTS")) ||
                (Auth::user()->role_80_jorissen == "1" && $data->residence == "80 JORISSEN") ||
                (Auth::user()->role_49_jorissen == "1" && $data->residence == "49 JORISSEN") ||
                (Auth::user()->role_dunvista_mansions == "1" && $data->residence == "DUNVISTA MANSIONS") ||
                (Auth::user()->role_wynton_joy == "1" && $data->residence == "WYNTON JOY") ||
                (Auth::user()->role_126_siemert == "1" && $data->residence == "126 SIEMERT") ||
                (Auth::user()->role_ymca == "1" && $data->residence == "YMCA")
            ) {

                if ($data->year <= $data->year_end || $data->year_end == 0) {
                    if($request->year !== "All"){
                        if ($request->year == "next") {
                            $this_year++;
                        }

                        if (($this_year >= $data->year && $data->year_end == 0) || ($this_year >= $data->year && $this_year <= $data->year_end)) {
                            array_push($userData, (array) $data);
                        }
                    } else {
                        array_push($userData, (array) $data);
                    }
                    
                }                
            }
        }

        return json_encode([$userData, Session::get('sortDirection'), Session::get('sortId') , Session::get('page')]);
    }


    public function properties_filter_v2(Request $request) {        

        //Get current booking year
        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;
        
        $search_value = [];

        
        // if ($request->firstLoad !== "1") {
        //     Session::put('manage_property', $request->property);
        //     Session::put('manage_year', $request->year);

        // } else {
        //     $request->property = Session::get('manage_property');
        //     $request->year = Session::get('manage_year');
        // }  

        Session::put('manage_property', $request->columns[0]['search']['value']);
        Session::put('manage_year', $request->columns[9]['search']['value']);
        
        if (!empty($request->columns[0]['search']['value']) && $request->columns[0]['search']['value'] != "All") {
            $property = 'pd.residence = "'.$request->columns[0]['search']['value'].'"';
            array_push($search_value, $property);
        }    
        
        if (!empty($request->columns[9]['search']['value']) && $request->columns[9]['search']['value'] != "All") {
            if ($request->columns[9]['search']['value'] == "next") {
                $this_year++;
            }

            $year = '(';
            $year .= '("'.$this_year.'" >= year and year_end = 0)';
            $year .= 'or ("'.$this_year.'" >= year and "'.$this_year.'" <= year_end)';
            $year .= ')';

            array_push($search_value, $year);
        }    

        if (!empty($request->search['value'])) {
            // $other_search = '(residence LIKE "%'.$request->search['value'].'%"';
            // $other_search .= ' or floor LIKE "%'.$request->search['value'].'%"';
            // $other_search .= ' or unit LIKE "%'.$request->search['value'].'%"';
            // $other_search .= ' or room LIKE "%'.$request->search['value'].'%"';
            // $other_search .= ' or bed LIKE "%'.$request->search['value'].'%"';
            // $other_search .= ' or bed_code LIKE "%'.$request->search['value'].'%"';
            $other_search = 'bed_code LIKE "%'.$request->search['value'].'%"';
            // $other_search .= ' or year LIKE "%'.$request->search['value'].'%"';
            // $other_search .= ' or year_end LIKE "%'.$request->search['value'].'%"';
            // $other_search .= ')';

            array_push($search_value, $other_search);
        }

        $query = [];
        

        foreach($search_value as $value) {
            $statement = " and " . $value;
            array_push($query, $statement);
        }

        /** Paging */
        $sLimit = "";
        if (isset($request->start) && $request->length != '-1') {
            $sLimit = " LIMIT " . intval($request->start) . ", " . intval($request->length);
        }

        $allowed = [];

        $allProp = Residence::whereNull('status')->get();
        // $permissionProp = PropertyPermission::where('userUid', Auth::user()->id)->get();

        foreach($allProp as $prop){
            // foreach($permissionProp as $permission){
                // if($permission->property == $prop->id){
                    array_push($allowed, $prop);
                // }
            // }
        }

        //Auth Location Checks

        $auth_loc_check = "";

        $i = 0;
        foreach($allowed as $a){
            if($i == 0){
                $sep = "";
            }else{
                $sep = ",";
            }
            $auth_loc_check .= $sep ."'".$a->id."'";
            $i++;
        }
        // $auth_loc_check = $sep = "";
        // if (Auth::user()->role_braamlofts == "1") {
        //     $auth_loc_check = "'BRAAMLOFTS'";
        //     $sep = ",";
        // }
        // if (Auth::user()->role_80_jorissen == "1") {
        //     $auth_loc_check .= $sep . "'80 JORISSEN'";
        //     $sep = ",";            
        // }
        // if (Auth::user()->role_49_jorissen == "1") {
        //     $auth_loc_check .= $sep . "'49 JORISSEN'";
        //     $sep = ",";            
        // }
        // if (Auth::user()->role_dunvista_mansions == "1") {
        //     $auth_loc_check .= $sep . "'DUNVISTA MANSIONS'";
        //     $sep = ",";            
        // }
        // if (Auth::user()->role_wynton_joy == "1") {
        //     $auth_loc_check .= $sep . "'WYNTON JOY'";
        //     $sep = ",";            
        // }
        // if (Auth::user()->role_126_siemert == "1") {
        //     $auth_loc_check .= $sep . "'126 SIEMERT'";
        //     $sep = ",";            
        // }
        // if (Auth::user()->role_ymca == "1") {
        //     $auth_loc_check .= $sep . "'YMCA'";
        //     $sep = ",";            
        // }

        //Default Query
        $sql = 'SELECT pd.id, p.residence, pd.floor, pd.unit, pd.room, pd.bed, pd.bed_code, pd.inventory, rt.name as room_type, pd.room_rate, pd.year, pd.year_end, pd.room_available
            FROM property_data pd
            LEFT OUTER JOIN property p ON p.id = pd.residence
            LEFT OUTER JOIN room_types rt ON rt.id = pd.room_type
            WHERE (`year` <= pd.year_end or pd.year_end = 0)
            ' . implode($query) . '
            and pd.residence in ('.$auth_loc_check.')
            
            order by pd.residence, cast(pd.floor as unsigned), cast(pd.unit as unsigned), pd.room, pd.bed, pd.year, pd.year_end ASC

            ' . $sLimit;

        //Get all of our students
        $searched_data = DB::select($sql);

        // return json_encode([$userData, Session::get('sortDirection'), Session::get('sortId') , Session::get('page')]);

        /* Total data set length */
        $sQuery = "SELECT COUNT(pd.id) as totcount FROM property_data pd where (`year` <= pd.year_end or pd.year_end = 0)" . implode($query) . "and pd.residence in (".$auth_loc_check.")";
        $rResultTotal = DB::select($sQuery);
        $aResultTotal = $rResultTotal[0]->totcount;
        $iTotal = $aResultTotal;

        $output = (object) array(
            "recordsTotal" => $iTotal,
            "recordsFiltered" => $iTotal,
            "data" => (array) $searched_data,
        );

        echo json_encode($output);
    }

    public function check_building_prefix(Request $request){
        $building = $request->building;
        $allPrefixes = Residence::whereNull('status')->get();

        $available = "true";
        foreach($allPrefixes as $prefix){
            if($prefix->building_prefix !== null && strtoupper($prefix->residence) !== strtoupper($request->residence) && strtoupper($prefix->building_prefix) == strtoupper($building)){
                $available = "false";
                break;
            }
        }

        return $available;
    }

    public function update_sponsor_details(Request $request){

        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        // $userCurr = User::where('id', $request->userId)->first();
        $userCurr = StudentsInfo::where('userUid', $request->userId)->where('year', $this_year)->first();

        if($request->app_address_p == '1'){
            $request->pAddress1 = $userCurr->userAddress1;
            $request->pAddress2 = $userCurr->userAddress2;
            $request->pAddressCity = $userCurr->userAddressCity;
            $request->pAddressPostal = $userCurr->userAddressPostal;
        }
        
        $updateUserArr = [
            'Sponsor First Name|sponFirstName' => $request->pFirstName,
            'Sponsor Last Name|sponLastName' => $request->pLastName,
            'Sponser Cell|sponCell' => $request->pCell_no,
            'Sponser Country Code|sponCellCode' => $request->sponCellCode,
            'Sponsor Email|sponEmail' => $request->pEmail,
            'Sponsor ID Number|sponId' => $request->pIdNumber,
            'Sponsor Nationality|pNationality' => $request->pNationality,
            'Sponsor Passport Number|pPassportNo' => $request->pPassportNo,
            'Sponsor Street No|pAddress1' => $request->pAddress1,
            'Sponsor Suburb|pAddress2' => $request->pAddress2,
            'Sponsor City|pAddressCity' => $request->pAddressCity,
            'Sponsor Postal Code|pAddressPostal' => $request->pAddressPostal,
            'Sponsor Address same as Applicant Home Address|app_address_p' => $request->app_address_p,
        ];
        

        foreach($updateUserArr as $key => $value){
            $actionName = explode("|", $key)[0];
            $keyNameName = explode("|", $key)[1];

            if($userCurr->$keyNameName !== $value){
                ActionList::create([
                    "userUid" => $request->userId,
                    "action" => "Updated ".$actionName." from ".$userCurr->$keyNameName." to ".$value,
                    "actionUserUid" => Auth::user()->id,
                    "actionUserDisplayName" => Auth::user()->userFirstName,
                    "actionUserRole" => Auth::user()->userRole,
                    "year" => $this_year,
                ]);

                // User::where('id', $request->userId)->update([$keyNameName => $value]);
                StudentsInfo::where('userUid', $request->userId)->where('year', $this_year)->update([$keyNameName => $value]);
            }
        }

        // $booking = Booking::where('id', $userCurr->bookingId)->first();
        // // $ts1 = strtotime($booking->lease_from_date);
        // // $ts2 = strtotime($booking->lease_to_date);
        // // $month1 = date('m', $ts1);
        // // $month2 = date('m', $ts2);
        // // $diff = $month2 - $month1;
        // $diff = 0;
        // for($i = new DateTime($booking->lease_from_date); $i <= new DateTime($booking->lease_to_date); $i->modify('+1 month')){
        //     $diff++;
        // }

        // $institution = Lookups::where('id', $userCurr->institutionStudy)->first();
        // $admin_fee = Lookups::where('group_id', '8')->first();
        // $funded = floatval($institution->nsfas_amount) * floatval($diff);
        // $thisBookingPayment = BookingPayment::where('bookingId', $userCurr->bookingId)->first();
        // BookingPayment::where('bookingId', $userCurr->bookingId)->update([
        //     'deposit' => $thisBookingPayment->monthly_rental,
        //     'loan_admin_fee' => $admin_fee->lookup_name,
        //     'total_funded' => $thisBookingPayment->monthly_rental_total,
        //     'total_top_up' => '0',
        // ]);

        $this->paymentDeatilsInternal($request->userId);


    }

    public function update_bursar_details(Request $request){
        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        // $userCurr = User::where('id', $request->userId)->first();
        $userCurr = StudentsInfo::where('userUid', $request->userId)->where('year', $this_year)->first();
        
        $updateUserArr = [
            'Bursar Company Name|burCompanyName' => $request->bCompanyName,
            'Bursar Registration Number|burRegNo' => $request->bRegistrationNo,
            'Bursar Contact Person|burContactPerson' => $request->bContactPerson,
            'Bursar Contact number|burContactNo' => $request->bContactNumber,
            'Bursar Country Code|burContactCode' => $request->burContactCode,
            'Bursar Email|burEmail' => $request->bEmail,
            'Bursar Street Number|bAddress1' => $request->bAddress1,
            'Bursar Suburb|bAddress2' => $request->bAddress2,
            'Bursar City|bAddressCity' => $request->bAddressCity,
            'Bursar postal Address|bAddressPostal' => $request->bAddressPostal,
            'Bursar Payment|bPayment' => $request->bPayment,
            'Bursar Intermediary|bint' => $request->bint,
            'Bursar Intermediary Name|bIntName' => $request->bIntName,
        ];
        

        foreach($updateUserArr as $key => $value){
            $actionName = explode("|", $key)[0];
            $keyNameName = explode("|", $key)[1];

            if($userCurr->$keyNameName !== $value){
                ActionList::create([
                    "userUid" => $request->userId,
                    "action" => "Updated ".$actionName." from ".$userCurr->$keyNameName." to ".$value,
                    "actionUserUid" => Auth::user()->id,
                    "actionUserDisplayName" => Auth::user()->userFirstName,
                    "actionUserRole" => Auth::user()->userRole,
                    "year" => $this_year,
                ]);

                // User::where('id', $request->userId)->update([$keyNameName => $value]);
                StudentsInfo::where('userUid', $request->userId)->where('year', $this_year)->update([$keyNameName => $value]);
            }
        }

        // $booking = Booking::where('id', $userCurr->bookingId)->first();
        // // $ts1 = strtotime($booking->lease_from_date);
        // // $ts2 = strtotime($booking->lease_to_date);
        // // $month1 = date('m', $ts1);
        // // $month2 = date('m', $ts2);
        // // $diff = $month2 - $month1;
        // $diff = 0;
        // for($i = new DateTime($booking->lease_from_date); $i <= new DateTime($booking->lease_to_date); $i->modify('+1 month')){
        //     $diff++;
        // }

        // $institution = Lookups::where('id', $userCurr->institutionStudy)->first();
        // $admin_fee = Lookups::where('group_id', '8')->first();
        // $funded = floatval($institution->nsfas_amount) * floatval($diff);
        // $thisBookingPayment = BookingPayment::where('bookingId', $userCurr->bookingId)->first();
        // BookingPayment::where('bookingId', $userCurr->bookingId)->update([
        //     'deposit' => $thisBookingPayment->monthly_rental,
        //     'loan_admin_fee' => $admin_fee->lookup_name,
        //     'total_funded' => $thisBookingPayment->monthly_rental_total,
        //     'total_top_up' => '0',
        // ]);

        $this->paymentDeatilsInternal($request->userId);
    }

    public function restore_student_booking(Request $request){

        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        // User::where('id', $request->userUid)->update(['rob_60_cancelled' => 0, "rob_10_fee" => 0]);
        StudentsInfo::where('userUid', $request->userUid)->where('year', $this_year)->update(['rob_60_cancelled' => 0, "rob_10_fee" => 0, "rob_40_bed" => 0]);
        Booking::where('userId', $request->userUid)->where('year', $this_year)->update(['property_data_id' => null, 'floor' => null, 'unit' => null, 'room' => null, 'bed' => null, 'bed_code' => null, 'room_rate' => null, 'cancelled' => null]);

        $student = StudentsInfo::where('userUid', $request->userUid)->where('year', $this_year)->first();
        
        ActionList::create([
            "userUid" => $request->userUid,
            "action" => "Restored Booking",
            "actionUserUid" => Auth::user()->id,
            "actionUserDisplayName" => $student->userFirstName,
            "actionUserRole" => $student->userRole,
            "year" => $this_year,
        ]);

        $lookup = Lookups::where('group_id', '7')->first();
        $booking = Booking::where('userId', $request->userUid)->where('year', $this_year)->first();
        $user = User::where('id', $request->userUid)->first();
        $booking_data = [];
        $booking_data['property'] = $request->property;
        $booking_data['roomType'] = $request->roomType;
       

        $bookingYear = substr($booking->year, -2);
        $bookingPrefix = Residence::where('id', $request->property)->pluck('building_prefix')->first();
        $id_booking = $booking->id;
        $placeholder = "";

        for($i = 0; $i < 5 - strlen($id_booking); $i++){
            $placeholder .= "0";
        }

        $bookingID = $placeholder.$id_booking;

        $booking_data['booking_code'] = $bookingYear.$bookingPrefix.$bookingID;

        $roomTypeNewId = $request->roomType;

        $request->property = Residence::where('id', $request->property)->pluck('residence')->first();
        $request->roomType = RoomTypes::where('id', $request->roomType)->pluck('name')->first();

        $roomTypeData = RoomTypes::where('id', $roomTypeNewId)->first();
        $roomTypeId = $roomTypeData->room_map_slug;

        $allResidences = Residence::whereNull('status')->get();
        $allRoomTypes = RoomTypes::get();

        if($lookup && $lookup->lookup_name == "1"){
            $booking_data['room_rate'] = "0";
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

                        if($room->acf->booking_fee_override !== ""){
                            $booking_data['room_rate'] = $room->acf->booking_fee_override;
                        }
    
                        if($room->slug == $roomName && $roomType == $request->roomType){
    
                            if($room->acf->booking_fee_override !== ""){
                                $booking_data['room_rate'] = $room->acf->booking_fee_override;
                            }
    
                            if($student->studentType == "NSFAS"){
                                if(!in_array("NSFAS Students", $appliesTo)){
                                    $booking_data['room_rate'] = "0";
                                }
                            }
    
                            if($student->studentType == "Bursary"){
                                if(!in_array("Bursary Students", $appliesTo)){
                                    $booking_data['room_rate'] = "0";
                                }
                            }
    
                            if($student->studentType == "Cash / Direct"){
                                if(!in_array("Cash Students", $appliesTo)){
                                    $booking_data['room_rate'] = "0";
                                }
                            }
                        }
                    }


                    
                }
            }
        }

        if($roomTypeData->pull_price == null){
            $booking_data['room_rate'] = $roomTypeData->booking_price;
        
            if($student->studentType == "NSFAS"){
                if($roomTypeData->apply_nsfas == null){
                    $booking_data['room_rate'] = "0";
                    $actionFileData['action'] = "Booking Fee is R0";
                }
            }
        
            if($student->studentType == "Bursary"){
                if($roomTypeData->apply_bursary == null){
                    $booking_data['room_rate'] = "0";
                    $actionFileData['action'] = "Booking Fee is R0";
                }
            }
        
            if($student->studentType == "Cash / Direct"){
                if($roomTypeData->apply_cash == null){
                    $booking_data['room_rate'] = "0";
                    $actionFileData['action'] = "Booking Fee is R0";
                }
            }
        }

        Booking::where('userId', $request->userUid)->where('year', $this_year)->update($booking_data);

        if($booking_data['room_rate'] == "0"){

            // User::where('id', $user->id)->update(["rob_10_fee" => 2]);
            StudentsInfo::where('userUid', $request->userUid)->where('year', $this_year)->update(["rob_10_fee" => 2]);

            $this_year = Lookups::where('group_id', '4')->first();
            $this_year = $this_year->lookup_name;

            $bookingfee = UserFiles::where('userUid', $request->userUid)->where('fileId', 'proof_of_payment')->where('year', $this_year)->first();

            $userData = StudentsInfo::where('userUid', $request->userUid)->where('year', $this_year)->first();        

            if(!empty($bookingfee)){
                UserFiles::where('userUid', $request->userUid)->where('fileId', 'proof_of_payment')->where('year', $this_year)->update(['status' => 'Approved']);
                if($userData->rob_40_bed != 2){
                    StudentsInfo::where('userUid', $request->userUid)->where('year', $this_year)->update(["rob_40_bed" => 1]);
                }
            }else{
                UserFiles::create([
                    'userUid'=> $request->userUid,
                    'fileId' => 'proof_of_payment',
                    'status' => 'Approved',
                    'year' => $this_year
                ]);
                if($userData->rob_40_bed != 2){
                    StudentsInfo::where('userUid', $request->userUid)->where('year', $this_year)->update(["rob_40_bed" => 1]);
                }
            }


            $actionFileData['userUid'] = $user->id;
            $actionFileData['action'] =  "Booking Fee is R0";
            $actionFileData['actionUserUid'] = Auth::user()->id;
            $actionFileData['actionUserDisplayName'] = $student->userFirstName;
            $actionFileData['actionUserRole'] = $student->userRole;
            $actionFileData['year'] = $this_year;
        
            ActionList::create($actionFileData);
        }else{

            $this_year = Lookups::where('group_id', '4')->first();
            $this_year = $this_year->lookup_name;

            $fileUploaded = UserFiles::where('userUid', $request->userUid)->where('fileId', 'proof_of_payment')->where('year', $this_year)->first();

            if($fileUploaded && $fileUploaded->fileUrl !== null){
                if($booking->room_rate != $booking_data['room_rate']){

                    UserFiles::where('userUid', $request->userUid)->where('fileId', 'proof_of_payment')->where('year', $this_year)->update([
                        'status' => 'Uploaded'
                    ]);
                    // User::where('id', $request->userUid)->update(['rob_10_fee' => 1]);
                    StudentsInfo::where('userUid', $request->userUid)->where('year', $this_year)->update(['rob_10_fee' => 1]);
                    
                }
            }else{
                UserFiles::where('userUid', $request->userUid)->where('fileId', 'proof_of_payment')->where('year', $this_year)->update([
                    'status' => 'Rejected'
                ]);
                // User::where('id', $request->userUid)->update(['rob_10_fee' => 0]);
                StudentsInfo::where('userUid', $request->userUid)->where('year', $this_year)->update(['rob_10_fee' => 0]);

                $userData = StudentsInfo::where('userUid', $request->userUid)->where('year', $this_year)->first();
                if($userData->rob_40_bed != 2){
                    StudentsInfo::where('userUid', $request->userUid)->where('year', $this_year)->update(['rob_40_bed' => 0]);
                }
            }

            //Recalculate booking status
            // User::where('id', $request->userUid)->update([
            //     'bookingStatus' => 'Action'
            // ]);
            StudentsInfo::where('userUid', $request->userUid)->where('year', $this_year)->update([
                'bookingStatus' => 'Action'
            ]);

            $student = StudentsInfo::where('userUid', $request->userUid)->where('year', $this_year)->first();

            Booking::where('userId', $request->userUid)->where('year', $this_year)->update(['room_rate' => $booking_data['room_rate']]);

            $actionFileData['userUid'] = $request->userUid;
            $actionFileData['action'] = "Booking Fee updated to R".$booking_data['room_rate'];
            $actionFileData['actionUserUid'] = Auth::user()->id;
            $actionFileData['actionUserDisplayName'] = $student->userFirstName;
            $actionFileData['actionUserRole'] = $student->userRole;
            $actionFileData['year'] = $this_year;
        
            ActionList::create($actionFileData);
        }

        foreach($booking_data as $key => $value){
            if($key == 'property'){
                $keyName = "Property";
                $newValue = Residence::where('id', $booking_data['property'])->pluck('residence')->first();
                $oldValue = Residence::where('id', $booking->property)->pluck('residence')->first();
            }elseif($key == 'roomType'){
                $keyName = "Room Type";
                $newValue = RoomTypes::where('id', $booking_data['roomType'])->pluck('name')->first();
                $oldValue = RoomTypes::where('id', $booking->roomType)->pluck('name')->first();
            }elseif($key == 'room_rate'){
                $keyName = "Booking Fee";
                $newValue = $value;
                $oldValue = $booking->room_rate;
            }elseif($key == 'booking_code'){
                $keyName = "Booking Reference";
                $newValue = $value;
                $oldValue = $booking->booking_code;
            }

            if($key == 'room_rate' && $newValue == '0'){
                //
            }else{

                $student = StudentsInfo::where('userUid', $request->userUid)->where('year', $this_year)->first();
                ActionList::create([
                    "userUid" => Auth::user()->id,
                    "action" => "Updated ".$keyName." from ".$oldValue." to ".$newValue,
                    "actionUserUid" => Auth::user()->id,
                    "actionUserDisplayName" => $student->userFirstName,
                    "actionUserRole" => $student->userRole,
                    "year" => $this_year,
                ]);
            }

        }


        
    }

    public function change_student_room(Request $request){

        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;


        Booking::where('userId', $request->userUid)->where('year', $this_year)->update(['property_data_id' => null, 'floor' => null, 'unit' => null, 'room' => null, 'bed' => null, 'bed_code' => null, 'room_rate' => null]);
        //Update the User robot
        // User::where('id',  $request->userUid)->update(['rob_40_bed' => 1, 'rob_50_approve' => 0, 'rob_55_lease' => 3]);
        StudentsInfo::where('userUid', $request->userUid)->where('year', $this_year)->update(['rob_40_bed' => 1, 'rob_50_approve' => 0, 'rob_55_lease' => 3]);
        Property::where('user_id_room', $request->userUid)->update(["user_id_room" => null, "room_available" => null]);

        $lookup = Lookups::where('group_id', '7')->first();
        $booking = Booking::where('userId', $request->userUid)->where('year', $this_year)->first();
        $user = User::where('id', $request->userUid)->first();
        $student = StudentsInfo::where('userUid', $request->userUid)->where('year', $this_year)->first();
        $booking_data = [];
        $booking_data['property'] = $request->property;
        $booking_data['roomType'] = $request->roomType;
       

        $bookingYear = substr($booking->year, -2);
        $bookingPrefix = Residence::where('id', $request->property)->pluck('building_prefix')->first();
        $id_booking = $booking->id;
        $placeholder = "";

        for($i = 0; $i < 5 - strlen($id_booking); $i++){
            $placeholder .= "0";
        }

        $bookingID = $placeholder.$id_booking;

        $booking_data['booking_code'] = $bookingYear.$bookingPrefix.$bookingID;

        $roomTypeNewId = $request->roomType;

        $request->property = Residence::where('id', $request->property)->pluck('residence')->first();
        $request->roomType = RoomTypes::where('id', $request->roomType)->pluck('name')->first();

        $roomTypeData = RoomTypes::where('id', $roomTypeNewId)->first();
        $roomTypeId = $roomTypeData->room_map_slug;
        
        $is_nsfas = false;
        if($roomTypeData->use_nsfas == 1){
            $is_nsfas = true;
        }

        $allResidences = Residence::whereNull('status')->get();
        $allRoomTypes = RoomTypes::get();

        if($lookup && $lookup->lookup_name == "1"){
            $booking_data['room_rate'] = "0";
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

                    // $slugString = str_replace("-", " ", $room->slug);
                    // $roomType = "";
                    // $roomName = "";

                    // foreach($allResidences as $residence){
                    //     if(strtoupper($request->property) == strtoupper($residence->residence)){
                    //         $postSlugStringName = trim(str_replace("room", "", str_replace(strtolower($residence->residence), "",$slugString)))."\r\n";
                    //         $requiredName = $request->roomType;

                    //         foreach($jsonDataRooms as $roomInfo){
                    //             if($residenceId == $roomInfo->acf->residence){
                    //                 if(strtolower(trim(str_replace("room", "", str_replace(strtolower($residence->residence), "",str_replace("-", " ", $roomInfo->slug))))) == strtolower($request->roomType)){
                    //                     $roomType = $request->roomType;
                    //                     $roomName = $roomInfo->slug;
                    //                 }
                    //             }
                    //         }
                    //     }
                    // }

                    if($room->id == $roomTypeId){
                        $roomType = $request->roomType;
                        $roomName = $room->slug;
                        $appliesTo = $room->acf->booking_fee_applies_to;
                        $deposit = $room->acf->price_from;

                        if($room->acf->booking_fee_override !== ""){
                            $booking_data['room_rate'] = $room->acf->booking_fee_override;
                        }
    
                        if($room->slug == $roomName && $roomType == $request->roomType){
    
                            if($room->acf->booking_fee_override !== ""){
                                $booking_data['room_rate'] = $room->acf->booking_fee_override;
                            }
    
                            if($student->studentType == "NSFAS"){
                                if(!in_array("NSFAS Students", $appliesTo)){
                                    $booking_data['room_rate'] = "0";
                                }
                            }
    
                            if($student->studentType == "Bursary"){
                                if(!in_array("Bursary Students", $appliesTo)){
                                    $booking_data['room_rate'] = "0";
                                }
                            }
    
                            if($student->studentType == "Cash / Direct"){
                                if(!in_array("Cash Students", $appliesTo)){
                                    $booking_data['room_rate'] = "0";
                                }
                            }
                        }
                    }


                    
                }
            }
        }

        if($roomTypeData->pull_price == null){
            $booking_data['room_rate'] = $roomTypeData->booking_price;
            $deposit = $roomTypeData->rent_price;
        
            if($student->studentType == "NSFAS"){
                if($roomTypeData->apply_nsfas == null){
                    $booking_data['room_rate'] = "0";
                    $actionFileData['action'] = "Booking Fee is R0";
                }
            }
        
            if($student->studentType == "Bursary"){
                if($roomTypeData->apply_bursary == null){
                    $booking_data['room_rate'] = "0";
                    $actionFileData['action'] = "Booking Fee is R0";
                }
            }
        
            if($student->studentType == "Cash / Direct"){
                if($roomTypeData->apply_cash == null){
                    $booking_data['room_rate'] = "0";
                    $actionFileData['action'] = "Booking Fee is R0";
                }
            }
        }

        Booking::where('userId', $request->userUid)->where('year', $this_year)->update($booking_data);

        if($booking_data['room_rate'] == "0"){

            // User::where('id', $user->id)->update(["rob_10_fee" => 2]);
            StudentsInfo::where('userUid', $request->userUid)->where('year', $this_year)->update(["rob_10_fee" => 2]);

            $this_year = Lookups::where('group_id', '4')->first();
            $this_year = $this_year->lookup_name;

            $bookingfee = UserFiles::where('userUid', $request->userUid)->where('fileId', 'proof_of_payment')->where('year', $this_year)->first();

            $userData = StudentsInfo::where('userUid', $request->userUid)->where('year', $this_year)->first();            

            if(!empty($bookingfee)){
                UserFiles::where('userUid', $request->userUid)->where('fileId', 'proof_of_payment')->where('year', $this_year)->update(['status' => 'Approved']);
                if($userData->rob_40_bed != 2){
                    StudentsInfo::where('userUid', $request->userUid)->where('year', $this_year)->update(["rob_40_bed" => 1]);
                }
            }else{
                UserFiles::create([
                    'userUid'=> $request->userUid,
                    'fileId' => 'proof_of_payment',
                    'status' => 'Approved',
                    'year' => $this_year
                ]);
                if($userData->rob_40_bed != 2){
                    StudentsInfo::where('userUid', $request->userUid)->where('year', $this_year)->update(["rob_40_bed" => 1]);
                }
            }


            $actionFileData['userUid'] = $user->id;
            $actionFileData['action'] =  "Booking Fee is R0";
            $actionFileData['actionUserUid'] = Auth::user()->id;
            $actionFileData['actionUserDisplayName'] = $student->userFirstName;
            $actionFileData['actionUserRole'] = Auth::user()->userRole;
            $actionFileData['year'] = $this_year;
        
            ActionList::create($actionFileData);
        }else{

            $this_year = Lookups::where('group_id', '4')->first();
            $this_year = $this_year->lookup_name;

            $fileUploaded = UserFiles::where('userUid', $request->userUid)->where('fileId', 'proof_of_payment')->where('year', $this_year)->first();

            if($fileUploaded && $fileUploaded->fileUrl !== null){
                if($booking->room_rate !== $booking_data['room_rate']){

                    // UserFiles::where('userUid', $request->userUid)->where('fileId', 'proof_of_payment')->update([
                    //     'status' => 'Uploaded'
                    // ]);
                    // User::where('id', $request->userUid)->update(['rob_10_fee' => 1]);
                    UserFiles::where('userUid', $request->userUid)->where('fileId', 'proof_of_payment')->where('year', $this_year)->update([
                        'status' => 'Rejected'
                    ]);
                    // User::where('id', $request->userUid)->update(['rob_10_fee' => 0]);
                    StudentsInfo::where('userUid', $request->userUid)->where('year', $this_year)->update(['rob_10_fee' => 0]);
                    $userData = StudentsInfo::where('userUid', $request->userUid)->where('year', $this_year)->first();
                    if($userData->rob_40_bed != 2){
                        StudentsInfo::where('userUid', $request->userId)->where('year', $this_year)->update(['rob_40_bed' => 0]);
                    }
                }
            }else{
                UserFiles::where('userUid', $request->userUid)->where('fileId', 'proof_of_payment')->where('year', $this_year)->update([
                    'status' => 'Rejected'
                ]);
                $userData = StudentsInfo::where('userUid', $request->userUid)->where('year', $this_year)->first();
                    if($userData->rob_40_bed != 2){
                        StudentsInfo::where('userUid', $request->userId)->where('year', $this_year)->update(['rob_40_bed' => 0]);
                    }
                // User::where('id', $request->userUid)->update(['rob_10_fee' => 0]);
                StudentsInfo::where('userUid', $request->userUid)->where('year', $this_year)->update(['rob_10_fee' => 0]);
            }

            //Recalculate booking status
            // User::where('id', $request->userUid)->update([
            //     'bookingStatus' => 'Action'
            // ]);
            StudentsInfo::where('userUid', $request->userUid)->where('year', $this_year)->update([
                'bookingStatus' => 'Action'
            ]);

            Booking::where('userId', $request->userUid)->where('year', $this_year)->update(['room_rate' => $booking_data['room_rate']]);

            $actionFileData['userUid'] = $request->userUid;
            $actionFileData['action'] = "Booking Fee updated to R".$booking_data['room_rate'];
            $actionFileData['actionUserUid'] = Auth::user()->id;
            $actionFileData['actionUserDisplayName'] = $student->userFirstName;
            $actionFileData['actionUserRole'] = Auth::user()->userRole;
            $actionFileData['year'] = $this_year;
        
            ActionList::create($actionFileData);
        }

        foreach($booking_data as $key => $value){
            if($key == 'property'){
                $keyName = "Property";
                $newValue = Residence::where('id', $booking_data['property'])->pluck('residence')->first();
                $oldValue = Residence::where('id', $booking->property)->pluck('residence')->first();
            }elseif($key == 'roomType'){
                $keyName = "Room Type";
                $newValue = RoomTypes::where('id', $booking_data['roomType'])->pluck('name')->first();
                $oldValue = RoomTypes::where('id', $booking->roomType)->pluck('name')->first();
            }elseif($key == 'room_rate'){
                $keyName = "Booking Fee";
                $newValue = $value;
                $oldValue = $booking->room_rate;
            }elseif($key == 'booking_code'){
                $keyName = "Booking Reference";
                $newValue = $value;
                $oldValue = $booking->booking_code;
            }

            if($key == 'room_rate' && $newValue == '0'){
                //
            }else{
                ActionList::create([
                    "userUid" => Auth::user()->id,
                    "action" => "Updated ".$keyName." from ".$oldValue." to ".$newValue,
                    "actionUserUid" => Auth::user()->id,
                    "actionUserDisplayName" => $student->userFirstName,
                    "actionUserRole" => Auth::user()->userRole,
                    "year" => $this_year,
                ]);
            }

        }

        $admin_fee = Lookups::where('group_id', '8')->first();
        $lookup_lease_dates = Lookups::where('group_id', '4')->first();
        $booking = Booking::where('id', $booking->id)->first();

        $alreadyPayment = BookingPayment::where("bookingId", $booking->id)->first();

        if(!empty($alreadyPayment)){


            $bookingPrice = $booking_data['room_rate'];
            $residenceId = 0;
            $roomTypeData = RoomTypes::where('id', $booking->roomType)->first();
            $booking->property = Residence::where('id', $booking->property)->pluck('residence')->first();

            $userInfo = StudentsInfo::where('bookingId', $booking->id)->orderBy('year', 'desc')->first();

            if(!empty($userInfo)){

                $diff = 0;
                for($i = new DateTime($lookup_lease_dates['lease_from_date']); $i <= new DateTime($lookup_lease_dates['lease_to_date']); $i->modify('+1 month')){
                    $diff++;
                }
                $yearly_rent = floatval($deposit) * floatval($diff);
    
                $institution = Lookups::where('id', $userInfo->institutionStudy)->first();
                $funded = floatval($institution->nsfas_amount) * floatval($diff);
    
                $topup = floatval($yearly_rent) - floatval($funded);
                $monthlyFunded = $institution->nsfas_amount;
                $nsfas_rate = null;

    
                if(($userInfo->studentType == "Cash / Direct") || ($userInfo->studentType == "Bursary" && $userInfo->nsfas_bursar !== 1)){
                    $admin_fee = '1000';
                    $deposit = $monthly = $monthlyFunded = $deposit;
                    $funded = $yearly_rent;
                    $topup = '0';
                }else{
                    $monthly = $deposit;
                    $deposit = 0;
                    $admin_fee = 0;

                    if($is_nsfas === true){
                        $monthly = $monthlyFunded;
                        $deposit = 0;
                        $admin_fee = 0;
                        $yearly_rent = $funded;
                        $topup = 0;
                        $nsfas_rate = 1;
                    }
                }

                BookingPayment::where("bookingId", $booking->id)->update([
                    "bookingId" => $booking->id, 
                    "booking_fee" => $bookingPrice, 
                    "booking_fee_waived" => 0, 
                    "rob_b_waived" => 0, 
                    "loan_admin_fee" => $admin_fee, 
                    "loan_admin_fee_waived" => 0, 
                    "rob_loan_waived" => 0, 
                    "deposit" => floatval($deposit), 
                    "deposit_waived" => 0, 
                    "rob_deposit_waived" => 0, 
                    "monthly_rental" => $monthly, 
                    "monthly_rental_total" => $yearly_rent, 
                    "total_funded" => $funded, 
                    "total_top_up" => $topup,
                    "funded_monthly" => $monthlyFunded,
                    "nsfas_rate" => $nsfas_rate
                ]);
            }

        }

        $newBookingData = Booking::where('userId', $request->userUid)->where('year', $this_year)->first();

        return $newBookingData;
    }

    public function bed_import(Request $request){
        return view('outSession.bed_imports', ["headerTitle" => "Import Beds", "title" => "Import Beds"]);
    }

    public function import_beds(Request $request){
        $files = $request->file('file');

        $inputFileName  = $request->file('file')->getClientOriginalName();

        $propertyList = [];
        $errorFound = 0;
        $errors = ["error"];

        if ($request->hasFile('file')) {
            
            if ($request->file('file')->isValid()) {

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

                $residence = Residence::whereNull('status')->get();
                $rooms = RoomTypes::get();


                $i = 0;

                foreach($schdeules as $beds){
                    
                    if($i > 0){
                        // $beds[0]

                        $property = [];

                        foreach($residence as $res){
                            if(strtoupper($res->residence) == strtoupper($beds[0])){
                                $property['residence'] = $res->id;
                            }
                        }

                        if(empty($property) || $property['residence'] == null || $property['residence'] == ""){
                            if($beds[8] !== "" && $beds[8] !== null){
                                $errorFound = 1;
                                if(!in_array("Residence <b>".$beds[0]."</b> not found", $errors)){
                                    array_push($errors, "Residence <b>".$beds[0]."</b> not found");
                                }
                            }
                            // return ["error", "Residence ".$beds[0]." not found"];
                        }

                        foreach($rooms as $room){
                            if(strtoupper($room->name) == strtoupper($beds[1]) && array_key_exists('residence', $property) && $room->property_id == $property['residence']){
                                $property['room_type'] = $room->id;
                            }
                        }

                        if(empty($property) || !array_key_exists('room_type', $property) || $property['room_type'] == null || $property['room_type'] == ""){
                            // return ["error", "Room Type ".$beds[1]." not found"];
                            if($beds[8] !== "" && $beds[8] !== null){
                                $errorFound = 1;
                                if(array_key_exists('residence', $property)){
                                    if(!in_array("Room Type <b>".$beds[1]."</b> not found", $errors)){
                                        array_push($errors, "Room Type <b>".$beds[1]."</b> not found");
                                    }
                                }else{
                                    if(!in_array("Room Type <b>".$beds[1]."</b> not found <i>Because property ".$beds[0]." is Missing</i>", $errors)){
                                        array_push($errors, "Room Type <b>".$beds[1]."</b> not found <i>Because property ".$beds[0]." is Missing</i>");
                                    }
                                }
                            }
                        }

                        $floor = "";

                        if(substr($beds[4], -1) == "1" && (floatval($beds[4]) < 10 || floatval($beds[4]) > 20)){
                            $floor = $beds[4]."ST";
                        }else if(substr($beds[4], -1) == "2" && (floatval($beds[4]) < 10 || floatval($beds[4]) > 20)){
                            $floor = $beds[4]."ND";
                        }else if(substr($beds[4], -1) == "3" && (floatval($beds[4]) < 10 || floatval($beds[4]) > 20)){
                            $floor = $beds[4]."RD";
                        }else if(is_numeric($beds[4])){
                            $floor = $beds[4]."TH";
                        }else{
                            $floor = $beds[4];
                        }

                        $property['meta_info_1'] = $beds[2];
                        $property['meta_info_2'] = $beds[3];
                        $property['floor'] = $floor;
                        $property['unit'] = $beds[5];
                        $property['room'] = $beds[6];
                        $property['bed'] = $beds[7];
                        $property['bed_code'] = $beds[8];
                        $property['inventory'] = $beds[9];
                        $property['year'] = $beds[10];
                        $property['year_end'] = $beds[11] == "" || $beds[11] == null ? "0" : $beds[11];

                        if($beds[8] !== "" && $beds[8] !== null){
                            array_push($propertyList, $property);
                        }

                        

                    }
                    $i++;
                }
            }
        }

        if($errorFound === 0){
            foreach($propertyList as $list){
    
                Property::create($list);
            }
        }else{
            return $errors;
        }


        echo "done";
    }

    public function UpdatePaymentRef(Request $request){

        Booking::where('id', $request->bookingId)->update(['booking_payment_ref' => $request->reason, 'payment_type' => 'Manual EFT']);
        $booking = Booking::where('id', $request->bookingId)->first();

        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        if(Auth::user()->userFirstName == null){
            $name = StudentsInfo::where('userUid', Auth::user()->id)->where('year', $this_year)->pluck('userFirstName')->first();
        }else{
            $name = Auth::user()->userFirstName;
        }

        $actionFileData['userUid'] = $booking->userId;
        $actionFileData['action'] =  "Updated Payment Reference to: ".$request->reason;
        $actionFileData['actionUserUid'] = Auth::user()->id;
        $actionFileData['actionUserDisplayName'] = $name;
        $actionFileData['actionUserRole'] = Auth::user()->userRole;
        $actionFileData['year'] = $this_year;
    
        ActionList::create($actionFileData);
        return 'true';
    }

    public function create_new_booking(Request $request, $userID){
        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        $userMain = User::where('id', $userID)->first();

        // $user = StudentsInfo::where('userUid', $userID)->where('year', $this_year)->first();
        $user = StudentsInfo::where('userUid', $userID)->orderBy('year', 'desc')->first();
        // $inSelected = InstitutionProperties::where("institution_id", $user->institutionStudy)->pluck('property_id')->toArray();

        $inSelectedCash = InstitutionProperties::where("institution_id", $user->institutionStudy)->where('cash', '1')->pluck('property_id')->toArray();
        $inSelectedBursary = InstitutionProperties::where("institution_id", $user->institutionStudy)->where('bursary', '1')->pluck('property_id')->toArray();
        $inSelectedNsfas = InstitutionProperties::where("institution_id", $user->institutionStudy)->where('nsfas', '1')->pluck('property_id')->toArray();


        $lookups = Lookups::get();

        $allowed = [];
        $allProp = Residence::whereNull('status')->get();
        $permissionProp = PropertyPermission::where('userUid', Auth::user()->id)->get();

        foreach($allProp as $prop){
            foreach($permissionProp as $permission){
                if($permission->property == $prop->id){
                    array_push($allowed, $prop);
                }
            }
        }

        $cancelled = 0;


        if($user->rob_60_cancelled == 1 || $user->year < $this_year - 1){
            $cancelled = 1;
        }
        

        return view('admin.newBookingForm', ["headerTitle" => "New Booking", "title" => "new Booking", "user" => $user, "lookups" => $lookups, "PropertyList" => $allowed, "cancelled" => $cancelled, "inSelectedCash" => $inSelectedCash, "inSelectedBursary" => $inSelectedBursary, "inSelectedNsfas" => $inSelectedNsfas]);
    }

    public function new_booking(Request $request){

        $updateData = [
            'First Name|userFirstName' => ucwords(strtolower($request->firstName)),
            'Last Name|userLastname' => ucwords(strtolower($request->lastName)),
            'Title|userTitle' => $request->title,
            'Date of Birth|userDob' => $request->dob,
            'Cellphone Number|userCell' => $request->cell_no,
            'Email address|userEmail' => $request->email,
            'ID Number|userId' => $request->idNumber,
            'Nationality|userNationality' => $request->nationality,
            'Passport Number|userPassport' => $request->passportNo,
            'institution|institutionStudy' => $request->institStudy,
            'Student Number|studentNo' => $request->studNumber,
            'Faculty|faculty' => $request->faculty,
            'Year|year' => $request->year,
            'Sponsor First Name|sponFirstName' => $request->pFirstName,
            'Sponsor Last Name|sponLastName' => $request->pLastName,
            'Sponsor Cellphone Number|sponCell' => $request->pCell_no,
            'Sponsor Email address|sponEmail' => $request->pEmail,
            'Sponsor ID Number|sponId' => $request->pIdNumber,
            'Bursar Company Name|burCompanyName' => $request->bCompanyName,
            'Bursar Registration Number|burRegNo' => $request->bRegistrationNo,
            'Bursar Contact Person|burContactPerson' => $request->bContactPerson,
            'Bursar Contact Number|burContactNo' => $request->bContactNumber,
            'Bursar Email|burEmail' => $request->bEmail,
            'Next of Kin First Name|kinFirstName' => $request->kFirstName,
            'Next of Kin Last Name|kinLastName' => $request->kLastName,
            'Next of Kin Title|kinTitle' => $request->kTitle,
            'Next of Kin Street|kinAddressStr' => $request->kAddress1,
            'Next of Kin Suburb|kinAddressSuburb' => $request->kAddress2,
            'Next of Kin City|kinAddressCity' => $request->kAddressCity,
            'Next of Kin Postal Code|kinAddressPost' => $request->kAddressPostal,
            'Next of Kin relationship|kinRelationship' => $request->kRelationship,
            'Next of Kin Telephone|kinTelNo' => $request->kTeleNo,
            'Next of Kin Cellphone|kinCellNo' => $request->kCellNo,
            'Next of Kin Work Number|kinWorkNo' => $request->kWorkNo,
            'Next of Kin Email|kinEmail' => $request->kEmail,
            'Student Type|studentType' => $request->studentType,
            'Year of Study|yearOfStudy' => $request->yearOfStudy,
            'Sponsor Nationality|pNationality' => $request->pNationality,
            'Sponsor passport Number|pPassportNo' => $request->pPassportNo,
            'Sponsor Street Address|pAddress1' => $request->pAddress1,
            'Sponsor Suburb|pAddress2' => $request->pAddress2,
            'Sponsor City|pAddressCity' => $request->pAddressCity,
            'Sponsor Postal Code|pAddressPostal' => $request->pAddressPostal,
            'Street Address|userAddress1' => $request->userAddress1,
            'Suburb|userAddress2' => $request->userAddress2,
            'City|userAddressCity' => $request->userAddressCity,
            'Postal Code|userAddressPostal' => $request->userAddressPostal,
            'Bursar Street Address|bAddress1' => $request->bAddress1,
            'Bursar Suburb|bAddress2' => $request->bAddress2,
            'Bursar City|bAddressCity' => $request->bAddressCity,
            'Bursar Postal Code|bAddressPostal' => $request->bAddressPostal,
            'Bursar Payment|bPayment' => $request->bPayment,
            'Bursar Intemediary|bint' => $request->bint,
            'Intermediary Name|bIntName' => $request->bIntName,
            'Cellphone Country Code|userCellCode' => $request->cell_no_code,
            'Next of Kin Cellphone Country Code|kinCellCode' => $request->kCellNo_code,
            'Next of Kin Work Number Country Code|kinWorkCode' => $request->kWorkNo_code,
            'Next of Kin Telephone Country Code|kinTelCode' => $request->kTeleNo_code,
            'Bursar Contact Country Code|burContactCode' => $request->bContactNumber_code,
            'Sponsor Cellphone Country Code|sponCellCode' => $request->pCell_no_code,
            'Gender|gender' => $request->gender,
        ];

        // $userInfo = User::where('id', $request->userUid)->first();
        $userInfo = StudentsInfo::where('userUid', $request->userUid)->orderBy('year', 'desc')->first();

        foreach($updateData as $key => $new){
            $info = explode("|", $key);
            $key = $info[1];
            $changedField = $info[0];

            if($changedField === "Gender"){
                if($userInfo->$key == '1'){
                    $userInfo->$key = "Male";
                }elseif($userInfo->$key == '2'){
                    $userInfo->$key = "Female";
                }

                if($new == '1'){
                    $new = "Male";
                }elseif($new == '2'){
                    $new = "Female";
                }
            }

            if($userInfo->$key !== $new){
                ActionList::create([
                    "userUid" => $request->userUid,
                    "action" => "Updated ".$changedField." From ".$userInfo->$key." to ".$new,
                    "actionUserUid" => Auth::user()->id,
                    "actionUserDisplayName" => Auth::user()->userFirstName,
                    "actionUserRole" => Auth::user()->userRole,
                    "year" => $request->year,
                ]);
            }
        }

        // $checkStudent = StudentsInfo::where('userUid', $request->userUid)->where('year', $request->year)->first();

        $newStudentData = [
            'userUid' => $request->userUid,
            'userFirstName' => ucwords(strtolower($request->firstName)),
            'userLastname' => ucwords(strtolower($request->lastName)),
            'userTitle' => $request->title,
            'userDob' => $request->dob,
            'userCell' => $request->cell_no,
            'userEmail' => $request->email,
            'userId' => $request->idNumber,
            'userNationality' => $request->nationality,
            'userPassport' => $request->passportNo,
            'institutionStudy' => $request->institStudy,
            'studentNo' => $request->studNumber,
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
            'rob_10_fee' => 0,
            'rob_15_id' => 0,
            'rob_20_card' => 0,
            'rob_25_reg' => 0,
            'rob_30_funds' => 0,
            'rob_35_info' => 1,
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
            'userCellCode' => $request->cell_no_code,
            'kinCellCode' => $request->kCellNo_code,
            'kinWorkCode' => $request->kWorkNo_code,
            'kinTelCode' => $request->kTeleNo_code,
            'burContactCode' => $request->bContactNumber_code,
            'sponCellCode' => $request->pCell_no_code,
            'app_source' => 0,
            'gender' => $request->gender,
        ];

        // if(empty($checkStudent)){
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
            'email' => $request->email,
            'userId' => $request->idNumber,
            'userNationality' => $request->nationality,
            'userPassport' => $request->passportNo,
            'year' => $request->year,
        ]);

        // $user = User::where('id', $request->userUid)->first();
        // $user = StudentsInfo::where('userUid', $request->userUid)->where('year', $request->year)->first();

        //Booking data        
        $booking_data = array(
            "userId" => $request->userUid, 
            "property" => $request->property, 
            "roomType" => $request->roomType,
            "year" => $request->year
        );

        $lookup = Lookups::where('group_id', '7')->first();

        $oldPropReq = $request->property;
        $roomId = $request->roomType;

        // $request->property = Residence::where('id', $request->property)->pluck('residence')->first();
        // $request->roomType = RoomTypes::where('id', $request->roomType)->pluck('name')->first();

        // $roomTypeData = RoomTypes::where('id', $roomId)->first();
        // $roomTypeId = $roomTypeData->room_map_slug;

        $allResidences = Residence::whereNull('status')->get();
        $allRoomTypes = RoomTypes::get();

        foreach($allResidences as $res){
            if($res->id == $request->property){
                $request->property = $res->residence;
                $bookingPrefix = $res->building_prefix;
            }
        }

        foreach($allRoomTypes as $rt){
            if($rt->id == $request->roomType){
                $request->roomType = $rt->name;
                $roomTypeData = $rt;
                $roomTypeId = $rt->room_map_slug;
            }
        }


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
                        $deposit = $room->acf->price_from;
    
                        $appliesTo = $room->acf->booking_fee_applies_to;

                        if($room->acf->booking_fee_override !== ""){
                            $booking_data['room_rate'] = $room->acf->booking_fee_override;
                        }
    
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

        if($roomTypeData && $roomTypeData->pull_price == null){
            $booking_data['room_rate'] = $roomTypeData->booking_price;
            $deposit = $roomTypeData->rent_price;

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

        

        $booking_search = Booking::where('userId', $request->userUid)->where('year', $request->year)->first();

        if(empty($booking_search)){
            $booking = Booking::create($booking_data);
        }else{
            Booking::where('userId', $request->userUid)->where('year', $request->year)->update($booking_data);
            $booking = $booking_search;
        }


        $bookingYear = substr($request->year, -2);
        // $bookingPrefix = Residence::where('id', $oldPropReq)->pluck('building_prefix')->first();
        $id_booking = $booking->id;
        $placeholder = "";
        for($i = 0; $i < 5 - strlen($id_booking); $i++){
            $placeholder .= "0";
        }
        $bookingID = $placeholder.$id_booking;
        Booking::where('id', $booking->id)->update(['booking_code' => $bookingYear.$bookingPrefix.$bookingID]);

        //Link booking to User
        // User::where('id', $user->id)->update(["bookingRef" => $booking->id]);
        StudentsInfo::where('userUid', $request->userUid)->where('year', $request->year)->update(["bookingRef" => $id_booking, "bookingId" => $id_booking]);

        $residence = Residence::where('residence', $request->property)->first();

        if($booking_data['room_rate'] == "0"){

            // User::where('id', $user->id)->update(["rob_10_fee" => 2]);
            StudentsInfo::where('userUid', $request->userUid)->where('year', $request->year)->update(["rob_10_fee" => 2]);

            $userData = StudentsInfo::where('userUid', $request->userUid)->where('year', $this_year)->first();
            

            UserFiles::create([
                'userUid'=> $user->id,
                'fileId' => 'proof_of_payment',
                'status' => 'Approved',
                'year' => $request->year
            ]);

            if($userData->rob_40_bed != 2){
                StudentsInfo::where('userUid', $request->userUid)->where('year', $this_year)->update(["rob_40_bed" => 1]);
            }

            $actionFileData['userUid'] = $request->userUid;
            $actionFileData['actionUserUid'] = Auth::user()->id;
            $actionFileData['actionUserDisplayName'] = Auth::user()->userFirstName;
            $actionFileData['actionUserRole'] = Auth::user()->userRole;
            $actionFileData['year'] = $request->year;
        
            ActionList::create($actionFileData);
        }

        if($request->studentType == "NSFAS"){
            
            $nsfasStudentsApproval = ImportNSFASStudentAuditLog::where('student_number', $request->studNumber)->orderBy('created_at', 'desc')->first();

            if(!empty($nsfasStudentsApproval)){

                $yearOfImport = ImportNSFASApproval::where('id', $nsfasStudentsApproval->importId)->first();
    
                if(!empty($nsfasStudentsApproval) && $yearOfImport->year == $request->year){
                    
                    UserFiles::create([
                        'userUid' => $request->userUid,
                        'fileId' => 'bursaryApproval',
                        'status' => 'Approved',
                        'year' => $request->year,
                    ]);
    
                    // User::where('id', $user->id)->update(["rob_30_funds" => 2]);
                    StudentsInfo::where('userUid', $request->userUid)->where('year', $request->year)->update(["rob_30_funds" => 2]);
    
                    ActionList::create([
                        "userUid" => $request->userUid,
                        // "action" => "NSFAS / Bursary has been Approved via import",
                        "action" => "NSFAS Approval has been Approved via import",
                        "actionUserUid" => Auth::user()->id,
                        "actionUserDisplayName" => Auth::user()->userFirstName,
                        "actionUserRole" => Auth::user()->userRole,
                        "year" => $request->year,
                    ]);
                }
            }
        }

        $genUser = User::where('id', $request->userUid)->first();

        $generator = new LoginUrl($genUser);
        $generator->setRedirectUrl('/verify-email-account');
        $url = $generator->generate();

        $newBooking = Booking::where('id', $booking->id)->first();

        $message_replace['bookingRef'] = $newBooking->booking_code;
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
            $data['email'] = $genUser->email;
            $data['messageForMail'] = $newMessage;
            $data['subject'] = str_replace("[application_number]", $message_replace['bookingRef'], $mailerForMessage->subject);

            

            $allowedMailsCC = Lookups::where('group_id', '11')->pluck('lookup_name')->first();
            $data['cc'] = explode(";", $allowedMailsCC);

            $allowedMails = Lookups::where('group_id', '10')->pluck('lookup_name')->first();

            if(!empty($allowedMails)){

                $mailArr = explode(";", $allowedMails);
    
                if(in_array(explode("@", $data['email'])[1], $mailArr)){
                    $data['email'] = $genUser->email;
                }else{
                    $data['email'] = "conurbandigital@gmail.com";
                }
            }else{
                $data['email'] = $genUser->email;
            }
    
            Mail::send('email.yellow_back', $data, function($message) use ($data) {
                $message->to($data['email'])->subject($data['subject']);
                $message->bcc($data['cc']);
                $message->from('noreply@campusafrica.co.za','Campus Africa');
            });
        }

        $admin_fee = Lookups::where('group_id', '8')->first();
        $lookup_lease_dates = Lookups::where('group_id', '4')->first();
        $booking = Booking::where('id', $booking->id)->first();

        $alreadyPayment = BookingPayment::where("bookingId", $booking->id)->first();

        if(empty($alreadyPayment)){


            $bookingPrice = $booking_data['room_rate'];
            $residenceId = 0;
            $roomTypeData = RoomTypes::where('id', $booking->roomType)->first();
            $booking->property = Residence::where('id', $booking->property)->pluck('residence')->first();

            $is_nsfas = false;
            if($roomTypeData->use_nsfas == 1){
                $is_nsfas = true;
            }

            $userInfo = StudentsInfo::where('bookingId', $booking->id)->orderBy('year', 'desc')->first();

            if(!empty($userInfo)){

                $diff = 0;
                for($i = new DateTime($lookup_lease_dates['lease_from_date']); $i <= new DateTime($lookup_lease_dates['lease_to_date']); $i->modify('+1 month')){
                    $diff++;
                }
                $yearly_rent = floatval($deposit) * floatval($diff);
    
                $institution = Lookups::where('id', $userInfo->institutionStudy)->first();
                $funded = floatval($institution->nsfas_amount) * floatval($diff);
    
                $topup = floatval($yearly_rent) - floatval($funded);
                $monthlyFunded = $institution->nsfas_amount;

                $nsfas_rate = null;

    
                if(($userInfo->studentType == "Cash / Direct") || ($userInfo->studentType == "Bursary" && $userInfo->nsfas_bursar !== 1)){
                    $admin_fee = '1000';
                    $deposit = $monthly = $monthlyFunded = $deposit;
                    $funded = $yearly_rent;
                    $topup = '0';
                }else{
                    $monthly = $deposit;
                    $deposit = 0;
                    $admin_fee = 0;

                    if($is_nsfas === true){
                        $monthly = $monthlyFunded;
                        $deposit = 0;
                        $admin_fee = 0;
                        $yearly_rent = $funded;
                        $topup = 0;
                        $nsfas_rate = 1;
                    }
                }

                    BookingPayment::create([
                        "bookingId" => $booking->id, 
                        "booking_fee" => $bookingPrice, 
                        "booking_fee_waived" => 0, 
                        "rob_b_waived" => 0, 
                        "loan_admin_fee" => $admin_fee, 
                        "loan_admin_fee_waived" => 0, 
                        "rob_loan_waived" => 0, 
                        "deposit" => floatval($deposit), 
                        "deposit_waived" => 0, 
                        "rob_deposit_waived" => 0, 
                        "monthly_rental" => $monthly, 
                        "monthly_rental_total" => $yearly_rent, 
                        "total_funded" => $funded, 
                        "total_top_up" => $topup,
                        "funded_monthly" => $monthlyFunded,
                        "nsfas_rate" => $nsfas_rate
                    ]);
                
            }

        }

        return redirect('/application-profile/'.$genUser->id);
    }

    public function getPaymentInfo(Request $request){
        $payments = BookingPayment::where('bookingId', $request->bookingId)->first();

        return $payments;
    }

    public function all_properties(Request $request){
        $allRes = Residence::whereNull('status')->get();

        $inSelectedCash = InstitutionProperties::where("institution_id", $request->institStudy)->where('cash', '1')->pluck('property_id')->toArray();
        $inSelectedBursary = InstitutionProperties::where("institution_id", $request->institStudy)->where('bursary', '1')->pluck('property_id')->toArray();
        $inSelectedNsfas = InstitutionProperties::where("institution_id", $request->institStudy)->where('nsfas', '1')->pluck('property_id')->toArray();


        return [$allRes, $inSelectedCash, $inSelectedBursary, $inSelectedNsfas];

    }

    public function nsfas_properties(Request $request){
        $allRes = Residence::whereNull('status')->get();

        $inSelectedCash = InstitutionProperties::where("institution_id", $request->institStudy)->where('cash', '1')->pluck('property_id')->toArray();
        $inSelectedBursary = InstitutionProperties::where("institution_id", $request->institStudy)->where('bursary', '1')->pluck('property_id')->toArray();
        $inSelectedNsfas = InstitutionProperties::where("institution_id", $request->institStudy)->where('nsfas', '1')->pluck('property_id')->toArray();


        return [$allRes, $inSelectedCash, $inSelectedBursary, $inSelectedNsfas];
    }

    public function send_reminder_mail(Request $request){
        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        $user = User::where('id', $request->userId)->first();
        $studentInfoForMail = StudentsInfo::where('userUid',  $request->userId)->where('year', $this_year)->first();
        $booking = Booking::where('id', $studentInfoForMail->bookingId)->first();

        $generator = new LoginUrl($user);
        $generator->setRedirectUrl('/dashboard');
        $url = $generator->generate();

        $all = ['studentCard', 'proofRegistration', 'copyID', 'proof_of_payment'];
        $outstanding = [];

        if($studentInfoForMail->studentType == "Cash / Direct"){
            array_push($all, "bankStatement");
        }
        if($studentInfoForMail->studentType !== "Cash / Direct"){
            array_push($all, "bursaryApproval");
        }

        $allSearchable = UserFiles::where("userUid", $request->userId)->where('year', $this_year)->get();

        foreach($allSearchable as $search){
            if(explode("_", $search->fileId)[0] == "other"){
                array_push($all, $search->fileId);
            }
        }

        foreach($all as $file){
            $file_search = UserFiles::where("userUid", $request->userId)->where('fileId', $file)->where('year', $this_year)->first();

            if(($studentInfoForMail->studentType == "Cash / Direct" && $file !== 'bursaryApproval') || $studentInfoForMail->studentType != "Cash / Direct" && $file !== 'bankStatement'){

                if($file == "studentCard"){
                    $fileName = "Student Card";
                }elseif($file == "copyID"){
                    $fileName = "ID Document";
                }elseif($file == "proof_of_payment"){
                    $fileName = "Proof of Payment";
                }elseif($file == "proofRegistration"){
                    $fileName = "Proof of Registration";
                }elseif($file == "bankStatement"){
                    $fileName = "Bank Statements";
                }elseif($file == "bursaryApproval"){
                    if($studentInfoForMail->studentType != "NSFAS"){
                        $fileName = "Bursary Approval";
                    }else{
                        $fileName = "NSFAS Approval";
                    }
                }elseif(explode("_", $file)[0] == "other"){
                    $fileName = strtoupper(str_replace("_", " ", str_replace("other", "other |", $file)));
                }

                if($file_search && !empty($file_search)){
                    if($file_search->status !== "Approved" && $file_search->status !== "Uploaded"){
                        array_push($outstanding, $fileName);
                    }
                }else{
                    array_push($outstanding, $fileName);
                }

            }

        }

        $outstandingDocsText = "<ol>";
        $outDocs = "";

        foreach($outstanding as $out){
            $outstandingDocsText .= "<li style='color: #000; font-size: 21px; font-weight: 400; line-height: 30px;'>".$out."</li>";
            $outDocs .= " ".$out.",";
        }

        $outstandingDocsText .= "</ol>";


        $message_replace['bookingRef'] = $booking->booking_code;
        $message_replace['name'] = $studentInfoForMail->userFirstName;
        $message_replace['application_link'] = "<a href='".$url."' target='_blank'>";
        $message_replace['close_application_link']="</a>";
        $message_replace['missing'] = $outstandingDocsText;

        $mailerForMessage = MailerList::where("mailer_name", "Reminder Mail")->first();

        if($mailerForMessage->status == '1'){

            $mailerMessage = $mailerForMessage->message;

            $newMessage = str_replace("[application_number]", "#".$message_replace['bookingRef'], $mailerMessage);
            $newMessage = str_replace("[name]", $message_replace['name'], $newMessage);
            $newMessage = str_replace("[outstanding_document_list]", $message_replace['missing'], $newMessage);
            $newMessage = str_replace("[application_link]", $message_replace['application_link'], $newMessage);
            $newMessage = str_replace("[close_application_link]", $message_replace['close_application_link'], $newMessage);

            $mailData['name'] = $studentInfoForMail->userFirstName;
            $mailData['email'] = $user->email;
            $mailData['messageForMail'] = $newMessage;
            $mailData['subject'] =str_replace("[application_number]", $message_replace['bookingRef'], $mailerForMessage->subject);
            
            $allowedMailsCC = Lookups::where('group_id', '11')->pluck('lookup_name')->first();
            $mailData['cc'] = explode(";", $allowedMailsCC);

            $allowedMails = Lookups::where('group_id', '10')->pluck('lookup_name')->first();

            if(!empty($allowedMails)){

                $mailArr = explode(";", $allowedMails);

                if(in_array(explode("@", $mailData['email'])[1], $mailArr)){
                    $mailData['email'] = $studentInfoForMail->userEmail;
                }else{
                    $mailData['email'] = "conurbandigital@gmail.com";
                }
            }else{
                $mailData['email'] = $studentInfoForMail->userEmail;
            }

            Mail::send('email.yellow_back', $mailData, function($message) use ($mailData) {
                $message->to($mailData['email'])->subject($mailData['subject']);
                $message->bcc($mailData['cc']);
                $message->from('noreply@campusafrica.co.za','Campus Africa');
            });

            ActionList::create([
                "userUid" => $studentInfoForMail->userUid,
                "action" => "Reminder Email sent for outstanding documents ".$outDocs,
                "actionUserUid" => Auth::user()->id,
                "actionUserDisplayName" => Auth::user()->userFirstName,
                "actionUserRole" => Auth::user()->userRole,
                "year" => $this_year,
            ]);
        }
        
    }

    public function download_beds(Request $request){
        $property = $request->property;
        $this_year = Lookups::where('group_id', '4')->first();
        $institutions = Lookups::where('group_id', '1')->first();
        $faculties = Lookups::where('group_id', '3')->first();

        $query = "";

        if($property !== "All"){
            $query .= "where pd.residence = '".$property."'";
        }

        if($query !== ""){
            $query .= " and (pd.year_end = '0' or pd.year_end = '".$this_year->lookup_name."') ";
        }else{
            $query .= "where (pd.year_end = '0' or pd.year_end = '".$this_year->lookup_name."') ";
        }

        $beds = [
			['<b>RESIDENCE</b>', '<b>FLOOR PREFIX</b>', '<b>BED CODE PREFIX</b>', '<b>FLOOR</b>', '<b>UNIT</b>', '<b>ROOM</b>', '<b>BED</b>', '<b>BED CODE</b>', '<b>ROOM TYPE</b>',  '<b>INVENTORY</b>', '<b>BOOKING REF</b>', '<b>FIRST NAME</b>' , '<b>LAST NAME</b>' , '<b>EMAIL</b>' , '<b>CELL NUMBER</b>', '<b>NATIONALITY</b>' , '<b>ID NUMBER / PASSPORT</b>' , '<b>GENDER</b>' , '<b>STUDENT TYPE</b>' , '<b>BURSAR COMPANY</b>' , '<b>INSTITUTION</b>' , '<b>STUDENT NUMBER</b>' , '<b>YEAR OF STUDY</b>', '<b>FACULTY</b>', '<b>NEXT OF KIN NAME</b>', '<b>NEXT OF KIN CONTACT</b>', '<b>NEXT OF KIN RELATIONSHIP</b>'],
		];

        $waitlist = [
			['<b>RESIDENCE</b>', '<b>ROOM TYPE</b>', '<b>BOOKING REF</b>', '<b>FIRST NAME</b>' , '<b>LAST NAME</b>' , '<b>EMAIL</b>' , '<b>CELL NUMBER</b>', '<b>NATIONALITY</b>' , '<b>ID NUMBER / PASSPORT</b>' , '<b>GENDER</b>' , '<b>STUDENT TYPE</b>' , '<b>BURSAR COMPANY</b>' , '<b>INSTITUTION</b>' , '<b>STUDENT NUMBER</b>' , '<b>YEAR OF STUDY</b>', '<b>FACULTY</b>', '<b>NEXT OF KIN NAME</b>', '<b>NEXT OF KIN CONTACT</b>', '<b>NEXT OF KIN RELATIONSHIP</b>'],
		];

        if($property !== "All"){
            $allBookingsThisYear = Booking::where('year', $this_year->lookup_name)->where('property_data_id', null)->where('property', $property)->orderBy('id', 'asc')->get();
        }else{
            $allBookingsThisYear = Booking::where('year', $this_year->lookup_name)->where('property_data_id', null)->orderBy('id', 'asc')->get();
        }

        foreach($allBookingsThisYear as $booking){
            $property = Residence::where('id', $booking->property)->pluck('residence')->first();
            $room = RoomTypes::where('id', $booking->roomType)->pluck('name')->first();
            $user = StudentsInfo::where('bookingId', $booking->id)->where('rob_10_fee', '2')->orderBy('year', 'desc')->first();

            if(!empty($user) && $user->rob_60_cancelled == 0){

                $first_name = $user->userFirstName;
                $last_name = $user->userLastname;
                $email = $user->userEmail;
                $cell = $user->userCellCode.$user->userCell;
                $nationality = $user->userNationality;
                $id = $user->userId != "" ? $user->userId : $user->userPassport;
                $gender = "";

                if($nationality == "South Africa" && $user->userId != "" && floatval(substr($user->userId, 6, 1)) >= 0 && floatval(substr($user->userId, 6, 1)) <= 4){
                    $gender = "Female";
                }elseif($nationality == "South Africa" && $user->userId != "" && floatval(substr($user->userId, 6, 1)) >= 5 && floatval(substr($user->userId, 6, 1)) <= 9){
                    $gender = "Male";
                }
                $studentType = $user->studentType == "Cash / Direct" ? "Cash" : $user->studentType;
                $bursarCompany = $user->studentType == "Bursary" ? $user->burCompanyName : "";
                $instit = Lookups::where('id', $user->institutionStudy)->pluck('lookup_name')->first();
                $studNumber = $user->studentNo;
                $yearOfStudy = $user->yearOfStudy;
                $faculty = Lookups::where('id', $user->faculty)->pluck('lookup_name')->first();
                $kinName = $user->kinFirstName." ".$user->kinLastname;
                $relationship = $user->kinRelationship;
                $kinContact = $user->kincellCode.$user->kinCellNo;
    
                $list = [$property, $room, $booking->booking_code, $first_name, $last_name, $email, $cell, $nationality, $id, $gender, $studentType, $bursarCompany, $instit, $studNumber, $yearOfStudy, $faculty, $kinName, $kinContact, $relationship];
                array_push($waitlist, $list);
            }
        }

        $allBeds = DB::select('SELECT pd.id, p.residence, r.name, pd.meta_info_1, pd.meta_info_2, pd.floor, pd.unit, pd.room, pd.bed, pd.bed_code, pd.inventory, pd.year, pd.year_end FROM property_data pd LEFT OUTER JOIN property p on pd.residence = p.id LEFT OUTER JOIN room_types r on pd.room_type = r.id '.$query.' ORDER BY pd.residence ASC, pd.meta_info_1, pd.floor + 0 ASC, pd.floor, pd.unit + 0 ASC, pd.room, pd.bed');

        foreach($allBeds as $bed){
            if($bed->inventory == '1'){
                $bed->inventory = "Yes";
            }else{
                $bed->inventory = "No";
            }

            $booking = Booking::where('property_data_id', $bed->id)->where('year',  $this_year->lookup_name)->first();

            if(!empty($booking)){
                $user = StudentsInfo::where('bookingId', $booking->id)->where('year',  $this_year->lookup_name)->first();

                $booking_ref = $booking->booking_code;
                $first_name = $user->userFirstName;
                $last_name = $user->userLastname;
                $email = $user->userEmail;
                $cell = $user->userCellCode.$user->userCell;
                $nationality = $user->userNationality;
                $id = $user->userId != "" ? $user->userId : $user->userPassport;
                $gender = "";
                if($nationality == "South Africa" && $user->userId != "" && floatval(substr($user->userId, 6, 1)) >= 0 && floatval(substr($user->userId, 6, 1)) <= 4){
                    $gender = "Female";
                }elseif($nationality == "South Africa" && $user->userId != "" && floatval(substr($user->userId, 6, 1)) >= 5 && floatval(substr($user->userId, 6, 1)) <= 9){
                    $gender = "Male";
                }
                $studentType = $user->studentType == "Cash / Direct" ? "Cash" : $user->studentType;
                $bursarCompany = $user->studentType == "Bursary" ? $user->burCompanyName : "";
                $instit = Lookups::where('id', $user->institutionStudy)->pluck('lookup_name')->first();
                $studNumber = $user->studentNo;
                $yearOfStudy = $user->yearOfStudy;
                $faculty = Lookups::where('id', $user->faculty)->pluck('lookup_name')->first();
                $kinName = $user->kinFirstName." ".$user->kinLastname;
                $relationship = $user->kinRelationship;
                $kinContact = $user->kincellCode.$user->kinCellNo;
                
            }else{
                $booking_ref = "";
                $first_name = "";
                $last_name = "";
                $email = "";
                $cell = "";
                $nationality = "";
                $id = "";
                $gender = "";
                $studentType = "";
                $bursarCompany = "";
                $instit = "";
                $studNumber = "";
                $yearOfStudy = "";
                $faculty = "";
                $kinName = "";
                $relationship = "";
                $kinContact = "";
            }

            $bed = [$bed->residence, $bed->meta_info_1, $bed->meta_info_2, $bed->floor, $bed->unit, $bed->room, $bed->bed, $bed->bed_code, $bed->name, $bed->inventory, $booking_ref, $first_name, $last_name, $email, $cell, $nationality, $id, $gender, $studentType, $bursarCompany, $instit, $studNumber, $yearOfStudy, $faculty, $kinName, $kinContact, $relationship];
            array_push($beds, $bed);
        }

        $xlsx = new \Shuchkin\SimpleXLSXGen();
		$xlsx->addSheet( $beds, 'BED CODES');
        $xlsx->addSheet( $waitlist, 'WAITLIST');

        $prop = Residence::where('id', $property)->pluck('residence')->first();

        // if($year !== "All"){

            $downloadUrl = 'Bed_Allocations_Export_'.$this_year->lookup_name.'_'.$prop.'.xlsx';
            $xlsx->saveAs('Bed_Allocations_Export_'.$this_year->lookup_name.'_'.$prop.'.xlsx');
        // }else{
        //     $downloadUrl = 'System_Bed_Allocations_All_Years.xlsx';
        //     $xlsx->saveAs('System_Bed_Allocations_All_Years.xlsx');
        // }

		return $downloadUrl;
    }

    public function download_mda(Request $request){
        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        $allApprovedUsers = StudentsInfo::where('year', $this_year)->where('rob_50_approve', '2')->get();

        $mdaInfo = [["Booking Ref", "Lease Status", "Property ID", "Lessee Name", "List Or Trading As Name", "Lessee Salutation", "General Contact Email Nos", "General Contact Telephone Nos", "General Contact Name", "General Contact Mobile Nos", "Property Rentable Unit Id", "Rent Amounts From Date", "Rent Amounts To Date", "Rent Amounts Amount", "Rent Amounts Rate", "Tenant Id", "Lease Start Date", "Occupation Date", "Lease Expiry Date"]];

        foreach($allApprovedUsers as $user){
            //
        }

        $xlsx = new \Shuchkin\SimpleXLSXGen();
		$xlsx->addSheet( $mdaInfo, 'MDA Students');
        $xlsx->saveAs('MDA download.xlsx');
    }

    // public function residence_delete($residence_id){
    //     residence::where('id', $residence_id)->delete();
    //     return redirect()->back();
    // }

    // public function residence_restore($residence_id){
    //     residence::withTrashed()->where('id', $residence_id)->restore();
    //     return redirect()->back();
    // }

    public function download_apps(Request $request){

        if($request->year !== "All"){
            $allUsers = StudentsInfo::where('year', $request->year)->get();
        }else{
            $allUsers = StudentsInfo::get();
        }

        $users = [
			['<b>Booking Ref</b>','<b>First Name</b>', '<b>Last Name</b>', '<b>Title</b>', '<b>DOB</b>', '<b>Cell</b>', '<b>Email</b>', '<b>Nationality</b>', '<b>I.D Number</b>', '<b>Passport</b>',  '<b>Student Type</b>', '<b>Year</b>', '<b>Institution of Study</b>', '<b>Student Number</b>', '<b>Year of Study</b>', '<b>Faculty</b>',

            '<b>Booking Fee</b>',
            '<b>Copy Of ID</b>',
            '<b>Student Card</b>',
            '<b>Proof of Registration</b>',
            '<b>Funds</b>',
            '<b>Information</b>',
            '<b>Bed Assigned</b>',
            '<b>Other</b>',
            '<b>Application Approved</b>',
            '<b>Lease Signed</b>',
            '<b>Cancelled</b>',

            '<b>Residence</b>', '<b>Room Type</b>', '<b>Floor</b>', '<b>Unit</b>', '<b>Room</b>', '<b>Bed Code</b>',
            
            '<b>Burser Company Name</b>', '<b>Burser Registeration Number</b>', '<b>Burser Contact Person</b>', '<b>Burser Contact Number</b>', '<b>Burser Email</b>', '<b>Parent First Name</b>', '<b>Parent Last Name</b>', '<b>Parent Cellphone Number</b>', '<b>Parent Email</b>', '<b>Parent Nationality</b>', '<b>Parent I.D Number</b>', '<b>Parent Passport</b>', '<b>Parent Street No</b>', '<b>Parent Suburb</b>', '<b>Parent City</b>', '<b>Parent Postal Code</b>', '<b>Next of Kin First Name</b>', '<b>Next of Kin Last Name</b>', '<b>Next of Kin Title</b>', '<b>Next of Kin Relationship</b>', '<b>Next of Kin Nationality</b>', '<b>Next of Kin I.D Number</b>', '<b>Next of Kin Passport</b>', '<b>Next of Kin Cellphone Number</b>', '<b>Next of Kin Email</b>', '<b>Next of Kin Work Number</b>', '<b>Next of Kin Telephone Number</b>', '<b>Next of Kin Street No</b>', '<b>Next of Kin Suburb</b>', '<b>Next of Kin City</b>', '<b>Next of Kin Postal Code</b>',]];

            foreach($allUsers as $user){

                $lookupsInstit = Lookups::where('id', $user->institutionStudy)->first();
                $lookupsFaculty = Lookups::where('id', $user->faculty)->first();

                $booking = Booking::where('id', $user->bookingId)->first();

                if($user->rob_10_fee == 1 && $user->rob_60_cancelled != 1){
                    $rob_10_fee = "Uploaded";
                }elseif($user->rob_10_fee == 2 && $user->rob_60_cancelled != 1){
                    $rob_10_fee = "Approved";
                }else{
                    $rob_10_fee = "";
                }

                if($user->rob_15_id == 1 && $user->rob_60_cancelled != 1){
                    $rob_15_id = "Uploaded";
                }elseif($user->rob_15_id == 2 && $user->rob_60_cancelled != 1){
                    $rob_15_id = "Approved";
                }else{
                    $rob_15_id = "";
                }

                if($user->rob_20_card == 1 && $user->rob_60_cancelled != 1){
                    $rob_20_card = "Uploaded";
                }elseif($user->rob_20_card == 2 && $user->rob_60_cancelled != 1){
                    $rob_20_card = "Approved";
                }else{
                    $rob_20_card = "";
                }

                if($user->rob_25_reg == 1 && $user->rob_60_cancelled != 1){
                    $rob_25_reg = "Uploaded";
                }elseif($user->rob_25_reg == 2 && $user->rob_60_cancelled != 1){
                    $rob_25_reg = "Approved";
                }else{
                    $rob_25_reg = "";
                }

                if($user->rob_30_funds == 1 && $user->rob_60_cancelled != 1){
                    $rob_30_funds = "Uploaded";
                }elseif($user->rob_30_funds == 2 && $user->rob_60_cancelled != 1){
                    $rob_30_funds = "Approved";
                }else{
                    $rob_30_funds = "";
                }

                if($user->rob_35_info == 1 && $user->rob_60_cancelled != 1){
                    $rob_35_info = "Uploaded";
                }elseif($user->rob_35_info == 2 && $user->rob_60_cancelled != 1){
                    $rob_35_info = "Approved";
                }else{
                    $rob_35_info = "";
                }

                if($user->rob_40_bed == 1 && $user->rob_60_cancelled != 1){
                    $rob_40_bed = "Uploaded";
                }elseif($user->rob_40_bed == 2 && $user->rob_60_cancelled != 1){
                    $rob_40_bed = "Approved";
                }else{
                    $rob_40_bed = "";
                }

                if($user->rob_45_other == 1 && $user->rob_60_cancelled != 1){
                    $rob_45_other = "Uploaded";
                }elseif($user->rob_45_other == 2 && $user->rob_60_cancelled != 1){
                    $rob_45_other = "Approved";
                }else{
                    $rob_45_other = "";
                }

                if($user->rob_50_approve == 1 && $user->rob_60_cancelled != 1){
                    $rob_50_approve = "Uploaded";
                }elseif($user->rob_50_approve == 2 && $user->rob_60_cancelled != 1){
                    $rob_50_approve = "Approved";
                }else{
                    $rob_50_approve = "";
                }

                if($user->rob_55_lease == 1 && $user->rob_60_cancelled != 1){ 
                    $rob_55_lease = "Uploaded";
                }elseif($user->rob_55_lease == 2 && $user->rob_60_cancelled != 1){
                    $rob_55_lease = "Approved";
                }else{
                    $rob_55_lease = "";
                }

                if($user->rob_60_cancelled != 1){
                    $cancelled = "No";
                }else{
                    $cancelled = "Yes";
                }

                if($booking){
                    $residence = Residence::where('id', $booking->property)->pluck('residence')->first();
                    $roomType = RoomTypes::where('id', $booking->roomType)->pluck('name')->first();

                    $floor = $booking->floor;
                    $unit = $booking->unit;
                    $room = $booking->room;
                    $bed_code = $booking->bed_code;

                    $booking_ref = $booking->booking_code;

                }else{
                    $residence = "";
                    $roomType = "";
                    $floor = "";
                    $unit = "";
                    $room = "";
                    $bed_code = "";
                    $booking_ref = "";
                }
                

                $studentData = [$booking_ref, $user->userFirstName, $user->userLastname, $user->userTitle, $user->userDob, $user->userCell, $user->userEmail, $user->userNationality, $user->userId, $user->userPassport, $user->studentType, $user->year, $lookupsInstit->lookup_name, $user->studentNo, $user->yearOfStudy, $lookupsFaculty->lookup_name, $rob_10_fee, $rob_15_id, $rob_20_card, $rob_25_reg, $rob_30_funds, $rob_35_info, $rob_40_bed, $rob_45_other, $rob_50_approve, $rob_55_lease, $cancelled,

                $residence, $roomType, $floor, $unit, $room, $bed_code,
                
                $user->burCompanyName, $user->burRegNo, $user->burContactPerson, $user->burContactNo, $user->burEmail, $user->sponFirstName, $user->sponLastName, $user->sponCell, $user->sponEmail, $user->pNationality, $user->sponId, $user->pPassportNo, $user->pAddress1, $user->pAddress2, $user->pAddressCity, $user->pAddressPostal, $user->kinFirstName, $user->kinLastName, $user->kinTitle, $user->kinRelationship, $user->kinNationality, $user->kinId, $user->kinPassportNo, $user->kinCellNo, $user->kinEmail, $user->kinWorkNo, $user->kinTelNo, $user->kinAddressStr, $user->kinAddressSuburb, $user->kinAddressCity, $user->kinAddressPost];

                array_push($users, $studentData);
            }


        $xlsx = new \Shuchkin\SimpleXLSXGen();
		$xlsx->addSheet( $users, 'Students');
        $xlsx->saveAs('All_Applications_'.$request->year.'_download.xlsx');

		return 'All_Applications_'.$request->year.'_download.xlsx';
    }

    public function get_actions(Request $request){

        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        // $allActions = ActionList::where('action', 'like', '%Updated Property%')->where('created_at', '>=', $request->fromDate)->where('created_at', '<=', $request->toDate)->get();

        // // $allActionsCancelled = ActionList::where('action', 'like', '%Cancelled%')->where('created_at', '>=', $request->fromDate)->where('created_at', '<=', $request->toDate)->get();

        // $allPropsFrom = [];
        // $allPropsTo = [];

        // foreach($allActions as $action){

        //     $removeFirst = str_replace('Updated Property From ', '', $action->action);
        //     $removeSecond = str_replace('Updated Property from ', '', $removeFirst);
        //     $myArray = explode(" to ", $removeSecond);

        //     $outputFrom = (object) array(
        //         "property" => $myArray[0],
        //         "userUid" => $action->userUid,
        //         "role" => $action->actionUserRole,
        //         "action_id" => $action->id,
        //     );
        //     array_push($allPropsFrom, $outputFrom);

        //     $outputTo = (object) array(
        //         "property" => $myArray[1],
        //         "userUid" => $action->userUid,
        //         "role" => $action->actionUserRole,
        //         "action_id" => $action->id,
        //     );
        //     array_push($allPropsTo, $outputTo);

        // }

        // foreach($allPropsFrom as $from){
        //     $allUserActionList = ActionList::where('userUid', $from->userUid)->get();

        //     $fee_non = false;

        //     foreach($allUserActionList as $action){

        //         if($action->action == "Booking Fee is R0" || $action->action == "Fee Waiver approved for Booking fee" || $action->action == "Online Payment Status: Approved"){
        //             $fee_non = true;
        //         }
                
        //     }
        // }
        

        // return [$allPropsFrom, $allPropsTo];

        $allBuildings = Residence::get();

        //get all registrations in time frame
        $allActionsRegistered = DB::select("SELECT * FROM action_lists where action like '%Registered%' and created_at >= '".$request->fromDate." 00:00:00' and created_at <= '".$request->toDate." 23:59:59' group by userUid");

        $registeredUsers = [];

        foreach($allActionsRegistered as $registered){
            //get all actions from user
            $allActionsInTime = DB::select("SELECT * FROM action_lists where userUid = '".$registered->userUid."'");

            $registered_building = 0;

            $moved = false;
            $first_move = 0;

            //Check to see if property was updated to get first moved property for accurate first property
            foreach($allActionsInTime as $action){
                $explode_action = explode(" ", $action->action);
                if($explode_action[0] == "Updated" && $explode_action[1] == "Property"){
                    $moved = true;
                    $first_move++;

                    //Get from property of first move to set where property was registered at
                    if($first_move == 1){
                        $removeFirst = str_replace('Updated Property From ', '', $action->action);
                        $removeSecond = str_replace('Updated Property from ', '', $removeFirst);
                        $myArray = explode(" to ", $removeSecond);

                        $registered_building = $myArray[0];
                    }
                }
            }

            //if property wasn't changed get current property
            if($moved === false){
                $booking = Booking::where('userId', $registered->userUid)->where('year', $this_year)->first();
                $building = Residence::where('id', $booking->property)->first();
                $registered_building = $building->residence;
            }

            $outputRegister = (object) array(
                "type" => "registered",
                "userUid" => $registered->userUid,
                "role" => $registered->actionUserRole,
                "registered_building" => $registered_building,
            );
            array_push($registeredUsers, $outputRegister);
        }

        $allActionsCancelled = DB::select("SELECT * FROM action_lists where action like '%cancelled%' and created_at >= '".$request->fromDate." 00:00:00' and created_at <= '".$request->toDate." 23:59:59' group by userUid");

        $cancelled_arr = [];
        $restored_arr = [];

        $cancelled_id_arr = [];
        $restored_id_arr = [];

        foreach($allActionsCancelled as $cancelled){

            //Get all Cancelled / restored in this time frame
            $allActionsInTime = DB::select("SELECT * FROM action_lists where userUid = '".$cancelled->userUid."' and created_at >= '".$request->fromDate." 00:00:00' and created_at <= '".$request->toDate." 23:59:59'");

            $mainActionId = 0;
            $lastAction = "";
            
            foreach($allActionsInTime as $action){
                $explode_action = explode(" ", $action->action);

                //if action is cancelled application
                if($explode_action[0] == "Application" && $explode_action[1] == "Cancelled"){
                    array_push($cancelled_id_arr, $action->userUid);
                    $mainActionId = $action->id;
                    $lastAction = "Cancelled";

                    //if application was restored remove ID from restored
                    if(in_array($action->userUid, $restored_id_arr)){
                        if (($key = array_search($action->userUid, $restored_id_arr)) !== false) {
                            unset($restored_id_arr[$key]);
                        }
                    }
                }

                //if action is restored application
                if($explode_action[0] == "Restored" && $explode_action[1] == "Cancelled" && $explode_action[2] == "Application"){
                    array_push($restored_id_arr, $action->userUid);
                    $mainActionId = $action->id;
                    $lastAction = "Restored";

                    //if application was cancelled remove ID from cancelled
                    if(in_array($action->userUid, $cancelled_id_arr)){
                        if (($key = array_search($action->userUid, $cancelled_id_arr)) !== false) {
                            unset($cancelled_id_arr[$key]);
                        }
                    }
                }
            }

            //get All the action from user
            $allActionsNoTime = ActionList::where('userUid', $cancelled->userUid)->get();
            $registered_building = "";

            //Check if building moved before last action, if so grab name of last building before action
            foreach($allActionsNoTime as $action){
                if($action->id < $mainActionId){
                    $explode_action = explode(" ", $action->action);
                    if($explode_action[0] == "Updated" && $explode_action[1] == "Property"){
                        
                        $removeFirst = str_replace('Updated Property From ', '', $action->action);
                        $removeSecond = str_replace('Updated Property from ', '', $removeFirst);
                        $myArray = explode(" to ", $removeSecond);

                        $registered_building = $myArray[1];
                    }
                }

                //Check if building was moved after action to get from building
                if($action->id > $mainActionId && $registered_building == ""){
                    $explode_action = explode(" ", $action->action);
                    if($explode_action[0] == "Updated" && $explode_action[1] == "Property"){
                        
                        $removeFirst = str_replace('Updated Property From ', '', $action->action);
                        $removeSecond = str_replace('Updated Property from ', '', $removeFirst);
                        $myArray = explode(" to ", $removeSecond);

                        $registered_building = $myArray[0];
                    }
                }
            }

            //If building wasnt moved - get building from location
            if($registered_building == ""){
                $booking = Booking::where('userId', $cancelled->userUid)->where('year', $this_year)->first();
                $building = Residence::where('id', $booking->property)->first();
                $registered_building = $building->residence;
            }

            $outputCancelled = (object) array(
                "type" => $lastAction,
                "userUid" => $cancelled->userUid,
                "role" => $cancelled->actionUserRole,
                "registered_building" => $registered_building,
            );

            if($lastAction == "Cancelled"){
                array_push($cancelled_arr, $outputCancelled);
            }else{
                array_push($restored_arr, $outputCancelled);
            }

        }

        //get all moved properties for period
        $allActionsMoved = DB::select("SELECT * FROM action_lists where action like '%Updated property%' and created_at >= '".$request->fromDate." 00:00:00' and created_at <= '".$request->toDate." 23:59:59' group by userUid");

        $movedFrom_arr = [];
        $movedTo_arr = [];

        $movedFrom_id_arr = [];
        $movedTo_id_arr = [];

        $allBuildings_data = [];

        foreach($allBuildings as $building){
            array_push($allBuildings_data, (object) array(
                "building" => $building->residence,
                "In" => 0,
                "Out" => 0,
                "userUidIn" => [],
                "userUidOut" => [],
            ));
        }

        foreach($allActionsMoved as $moved){

            //get all moves for student in period
            $allActionsInTime = DB::select("SELECT * FROM action_lists where userUid = '".$moved->userUid."' and action like '%Updated property%' and created_at >= '".$request->fromDate." 00:00:00' and created_at <= '".$request->toDate." 23:59:59'");

            $mainActionId = 0;
            $lastAction = "";
            $registered_building = "";

            foreach($allActionsInTime as $action){
                $removeFirst = str_replace('Updated Property From ', '', $action->action);
                $removeSecond = str_replace('Updated Property from ', '', $removeFirst);
                $myProperty = explode(" to ", $removeSecond);

                foreach($allBuildings_data as $building){
                    if($building->building == $myProperty[0]){
                        if($myProperty[0] !== $myProperty[1] && $myProperty[0] != "" && $myProperty[1] != ""){
                            //add to moved from count
                            array_push($building->userUidOut, $moved->userUid);
                            $building->Out = $building->Out + 1;
                            //check if in moved in count & remove
                            if(in_array($moved->userUid, $building->userUidIn)){
                                if (($key = array_search($moved->userUid, $building->userUidIn)) !== false) {
                                    unset($building->userUidIn[$key]);
                                    $building->In = $building->In - 1;
                                }
                            }
                        }
                    }

                    if($building->building == $myProperty[1]){
                        if($myProperty[0] !== $myProperty[1] && $myProperty[0] != "" && $myProperty[1] != ""){
                            //add to moved to count
                            array_push($building->userUidIn, $moved->userUid);
                            $building->In = $building->In + 1;
                            //check if in moved out count & remove
                            if(in_array($moved->userUid, $building->userUidOut)){
                                if (($key = array_search($moved->userUid, $building->userUidOut)) !== false) {
                                    unset($building->userUidOut[$key]);
                                    $building->Out = $building->Out - 1;
                                }
                            }
                        }
                    }
                }
            }
        }

        return [$allBuildings, $registeredUsers, $cancelled_arr, $restored_arr, $allBuildings_data];
    }

    public function paymentDeatilsInternal($user_id){

        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        $userInfo = StudentsInfo::where('userUid', $user_id)->where('year', $this_year)->first();

        if($userInfo){

            $booking_new = Booking::where('id', $userInfo->bookingId)->where('year', $this_year)->first();
    
            $lookup = Lookups::where('group_id', '7')->first();
    
            $roomTypeData = RoomTypes::where('id', $booking_new->roomType)->first();
            $roomTypeId = $roomTypeData->room_map_slug;
    
            $res = Residence::where('id', $booking_new->property)->first();
    
            $room_rate = 0;
            $deposit = 0;
    
            $is_nsfas = false;
            if($roomTypeData->use_nsfas == 1){
                $is_nsfas = true;
            }
    
            if($lookup && $lookup->lookup_name == "1"){
                $room_rate = "0";
            }else{
                //Get residences from WP
                $jsonDataResidence = json_decode(file_get_contents('https://www.campusafrica.co.za/wp-json/wp/v2/residences'));
                $residenceId = "";
    
                foreach($jsonDataResidence as $residence){
                    if($residence->title->rendered == $res->residence){
                        //get room booking fee from residence (WP)
                        $room_rate = $residence->acf->booking_fee;
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
                            $roomType = $booking_new->roomType;
                            $roomName = $room->slug;
                            $deposit = $room->acf->price_from;
        
                            $appliesTo = $room->acf->booking_fee_applies_to;
    
                            if($room->acf->booking_fee_override !== ""){
                                $room_rate = $room->acf->booking_fee_override;
                            }
        
                            if($room->slug == $roomName && $roomType == $booking_new->roomType){
        
                                if($room->acf->booking_fee_override !== ""){
                                    $room_rate = $room->acf->booking_fee_override;
                                }
        
                                if($userInfo->studentType === "NSFAS"){
                                    if(!in_array("NSFAS Students", $appliesTo)){
                                        $room_rate = "0";
                                    }
                                }
        
                                if($userInfo->studentType === "Bursary"){
                                    if(!in_array("Bursary Students", $appliesTo)){
                                        $room_rate = "0";
                                    }
                                }
        
                                if($userInfo->studentType === "Cash / Direct"){
                                    if(!in_array("Cash Students", $appliesTo)){
                                        $room_rate = "0";
                                    }
                                }
                            } 
                        }
    
                    }
                }
            }
    
            if($roomTypeData && $roomTypeData->pull_price == null){
                $room_rate = $roomTypeData->booking_price;
                $deposit = $roomTypeData->rent_price;
    
                if($userInfo->studentType == "NSFAS"){
                    if($roomTypeData->apply_nsfas == null){
                        $room_rate = "0";
                    }
                }
    
                if($userInfo->studentType == "Bursary"){
                    if($roomTypeData->apply_bursary == null){
                        $room_rate = "0";
                    }
                }
    
                if($userInfo->studentType == "Cash / Direct"){
                    if($roomTypeData->apply_cash == null){
                        $room_rate = "0";
                    }
                }
            }
    
            $bookingPrice = $room_rate;
    
            //Get default lease data for the booking
            $lookup_lease_dates = Lookups::where('group_id', '4')->first();
    
            $booking_data['lease_from_date'] = $lookup_lease_dates['lease_from_date'];
            $booking_data['lease_from_time'] = $lookup_lease_dates['lease_from_time'];
            $booking_data['lease_to_date'] = $lookup_lease_dates['lease_to_date'];
            $booking_data['lease_to_time'] = $lookup_lease_dates['lease_to_time'];
    
    
            $diff = 0;
            for($i = new DateTime($lookup_lease_dates['lease_from_date']); $i <= new DateTime($lookup_lease_dates['lease_to_date']); $i->modify('+1 month')){
                $diff++;
            }
            $yearly_rent = floatval($deposit) * floatval($diff);
    
            $institution = Lookups::where('id', $userInfo->institutionStudy)->first();
            $funded = floatval($institution->nsfas_amount) * floatval($diff);
    
            $topup = floatval($yearly_rent) - floatval($funded);
            $monthlyFunded = $institution->nsfas_amount;
    
            $nsfas_rate = null;
    
    
            if(($userInfo->studentType == "Cash / Direct") || ($userInfo->studentType == "Bursary" && $userInfo->nsfas_bursar !== 1)){
                $admin_fee = '1000';
                $deposit = $monthly = $monthlyFunded = $deposit;
                $funded = $yearly_rent;
                $topup = '0';
            }else{
                $monthly = $deposit;
                $deposit = 0;
                $admin_fee = 0;
    
                if($is_nsfas === true){
                    $monthly = $monthlyFunded;
                    $deposit = 0;
                    $admin_fee = 0;
                    $yearly_rent = $funded;
                    $topup = 0;
                    $nsfas_rate = 1;
                }
            }
    
            BookingPayment::where('bookingId', $booking_new->id)->update([
                "bookingId" => $booking_new->id, 
                "booking_fee" => $bookingPrice, 
                "booking_fee_waived" => 0, 
                "rob_b_waived" => 0, 
                "loan_admin_fee" => $admin_fee, 
                "loan_admin_fee_waived" => 0, 
                "rob_loan_waived" => 0, 
                "deposit" => floatval($deposit), 
                "deposit_waived" => 0, 
                "rob_deposit_waived" => 0, 
                "monthly_rental" => $monthly, 
                "monthly_rental_total" => $yearly_rent, 
                "total_funded" => $funded, 
                "total_top_up" => $topup,
                "funded_monthly" => $monthlyFunded,
                "nsfas_rate" => $nsfas_rate
            ]);
        }
    }
    
    

    
}

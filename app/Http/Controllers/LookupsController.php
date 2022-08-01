<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lookups;
use App\Models\User;
use App\Models\UserFiles;
use App\Models\Booking;
use App\Models\Property;
use App\Models\ActionList;
use App\Models\StudentsInfo;
use Auth;
use App\Models\Residence;
use App\Models\RoomTypes;
use Illuminate\Support\Facades\DB;
use App\Models\Comments;

class LookupsController extends Controller {
    public function lookups(Request $request){

        if(Auth::user()->role_lookup == 1){
            $lookups = Lookups::with('users')->get();
    
            return view('admin.lookups', ["headerTitle" => "Lookups", "lookups" => $lookups, "title" => "Lookups"]);
        }else{
            return "404 Error";
        }

    }

    public function add_lookups(Request $request){

        if(Auth::user()->role_lookup == 1){

            return view('admin.lookups_new', ["headerTitle" => "Add Lookup", "title" => "Add Lookup"]);
        }else{
            return "404 Error";
        }
    }

    public function new_lookups(Request $request){

        if($request->group_id == "7"){
            $data['group_id'] = $request->group_id;
            $data['lookup_name'] = $request->booking_waiver;
            $data['waiver_date_from'] = $request->date_from;
            $data['waiver_date_to'] = $request->date_to;
            $data['actionUserUid'] = Auth::user()->id;
        }else if($request->group_id == "9"){
            $data = [
                "group_id" => $request->group_id,
                "lookup_name" => $request->onlineApp_value,
                "actionUserUid" => Auth::user()->id,
            ];
        }else{
            $data['group_id'] = $request->group_id;
            $data['lookup_name'] = $request->value;
            $data['actionUserUid'] = Auth::user()->id;
        }

        Lookups::create($data);

        return redirect()->route('lookups');
    }

    public function edit_lookups(Request $request, $lookupId){

        if(Auth::user()->role_lookup == 1){

            $lookup = Lookups::where('id', $lookupId)->first();

            $year_lookup = Lookups::where('group_id', '4')->first();

            return view('admin.lookups_edit', ["headerTitle" => "Edit Lookup", "lookup" => $lookup, "title" => "Edit Lookup", "year_lookup" => $year_lookup]);
        }else{
            return "404 Error";
        }
    }

    public function save_lookups(Request $request){

        if($request->group_id == "4"){

            $dateFrom = $request->value."-".$request->month_from."-".$request->day_from;
            $dateTo = $request->value."-".$request->month_to."-".$request->day_to;

            $data = [
                "group_id" => $request->group_id,
                "lookup_name" => $request->value,
                "lease_from_date" => date("Y-m-d", strtotime($dateFrom)),
                "day_from" => $request->day_from,
                "month_from" => $request->month_from,
                "lease_from_time" => $request->lease_from_time,
                "lease_to_date" => date("Y-m-d", strtotime($dateTo)),
                "day_to" => $request->day_to,
                "month_to" => $request->month_to,
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
        }else if($request->group_id == "9"){
            $data = [
                "group_id" => $request->group_id,
                "lookup_name" => $request->onlineApp_value,
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

        //Update bookings
        $this->admin_updates_update_properties();

        return redirect()->route('lookups');
    }

    public function admin_updates_landing() {
        return view('admin.lookups.admin_updates_landing', ['headerTitle' => 'Admin Updates', "title" => "Admin Updates"]);
    }

    public function admin_updates_fix_data(){
        $students = User::where('userRole', '0')->get();

        foreach($students as $student){
            $files = UserFiles::where('userUid', $student->id)->get();
            $allOtherFiles = UserFiles::where('userUid', $student->id)->whereNotIn('fileId', ['proof_of_payment', 'copyID', 'studentCard', 'proofRegistration', 'bursaryApproval', 'bankStatement', 'moveIn'])->get();

            $status_data = [
                'rob_10_fee' => 0,
                'id_data_approved' => 0,
                'rob_15_id' => 0,
                'student_card_approved' => 0,
                'rob_20_card' => 0,
                'registration_approved' => 0,
                'rob_25_reg' => 0,

                'bursary_approved' => 0,
                'bank_approved' => 0,
                'rob_30_funds' => 0,                
                
                'address_approved' => $student->address_approved,
                'kin_approved' => $student->kin_approved,
                'email_data_approved' => $student->email_data_approved,
                'rob_35_info' => 1,

                'rob_40_bed' => 1,
                'rob_45_other' => 3,
                'rob_50_approve' => $student->rob_50_approve,
                'rob_55_lease' => 3,
            ];

            

            if(count($allOtherFiles) == 0){
                $status_data['rob_45_other'] = '3';
            }else{
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

                $status_data['rob_45_other'] = $highest;
            }

            $leaseDocUploaded = 0;

            foreach($files as $f) {                
                if ($f->fileId == "proof_of_payment") {
                    if ($f->status == "Approved") {
                        $status_data['rob_10_fee'] = 2;
                    } elseif ($f->status == "Uploaded") {                                                
                        $status_data['rob_10_fee'] = 1;
                    }

                } elseif ($f->fileId == "copyID") {
                    if ($f->status == "Approved") {
                        $status_data['id_data_approved'] = 1;                        
                        $status_data['rob_15_id'] = 2;

                    } elseif ($f->status == "Uploaded") {                                                
                        $status_data['rob_15_id'] = 1;

                        if ($student->id_data_approved == 2) {
                            $status_data['id_data_approved'] = 1;
                        } else {
                            $status_data['id_data_approved'] = 0;
                        }
                    }

                } elseif ($f->fileId == "studentCard") {
                    if ($f->status == "Approved") {
                        $status_data['student_card_approved'] = 1;                        
                        $status_data['rob_20_card'] = 2;

                    } elseif ($f->status == "Uploaded") {                                                
                        $status_data['rob_20_card'] = 1;

                        if ($student->student_card_approved == 2) {
                            $status_data['student_card_approved'] = 1;
                        } else {
                            $status_data['student_card_approved'] = 0;
                        }
                    }

                } elseif ($f->fileId == "proofRegistration") {
                    if ($f->status == "Approved") {
                        $status_data['registration_approved'] = 1;                        
                        $status_data['rob_25_reg'] = 2;

                    } elseif ($f->status == "Uploaded") {                                                
                        $status_data['rob_25_reg'] = 1;

                        if ($student->registration_approved == 2) {
                            $status_data['registration_approved'] = 1;
                        } else {
                            $status_data['registration_approved'] = 0;
                        }
                    }

                } elseif ($f->fileId == "bankStatement" || $f->fileId == "bursaryApproval") {
                    if ($f->status == "Approved") {
                        if ($student->studentType == "Bursary" || $student->studentType == "NSFAS") {                            
                            $status_data['bursary_approved'] = 1;   
                        } else {
                            $status_data['bank_approved'] = 1;   
                        }
                        $status_data['rob_30_funds'] = 2;

                    } elseif ($f->status == "Uploaded") {                                                
                        $status_data['rob_30_funds'] = 1;

                        if ($student->studentType == "Bursary" || $student->studentType == "NSFAS") {                            
                            if ($student->bursary_approved == 2) {
                                $status_data['bursary_approved'] = 1;
                            } else {
                                $status_data['bursary_approved'] = 0;
                            } 
                        } else {
                            if ($student->bank_approved == 2) {
                                $status_data['bank_approved'] = 1;
                            } else {
                                $status_data['bank_approved'] = 0;
                            } 
                        }
                    }                

                } elseif ($f->fileId == "moveIn") {
                    $leaseDocUploaded = 1;
                    if ($f->status == "Approved") {
                        $status_data['rob_55_lease'] = 2;
                    } elseif ($f->status == "Uploaded") {                                                
                        $status_data['rob_55_lease'] = 1;
                    } elseif ($f->status == "Rejected") {                                                
                        $status_data['rob_55_lease'] = 0;
                    }
                    
                }
            }

            $booking = Booking::where('userId', $student->id)->first();
            if (!empty($booking->bed_code)) {
                $status_data['rob_40_bed'] = 2;
            }

            if ($student->address_approved == 2) {
                $status_data['address_approved'] = 1;
            }
            if ($student->kin_approved == 2) {
                $status_data['kin_approved'] = 1;
            }
            if ($student->email_data_approved == 2) {
                $status_data['email_data_approved'] = 1;
            }

            if ($status_data['address_approved'] == 1 && $status_data['kin_approved'] == 1 && $status_data['email_data_approved'] == 1) {
                $status_data['rob_35_info'] = 2;
            }

            if($leaseDocUploaded == 0 && $status_data['rob_50_approve'] == 2){
                $status_data['rob_55_lease'] = 0;
            }

            if($status_data['rob_10_fee'] == 2 && $status_data['rob_15_id'] == 2 && $status_data['rob_20_card'] == 2 && $status_data['rob_25_reg'] == 2 && $status_data['rob_30_funds'] == 2 && $status_data['rob_35_info'] == 2 && $status_data['rob_40_bed'] == 2 && ($status_data['rob_45_other'] == 3 || $status_data['rob_45_other'] == 2) && $status_data['rob_50_approve'] == 0){
                $status_data['rob_50_approve'] = 1;
            }

            $actions = ActionList::where('userUid', $student->id)->orderBy('id', 'DESC')->get();

            foreach($actions as $action){
                if ($action->action == 'Application Cancelled') {
                    $status_data['rob_60_cancelled'] = 1;
                }

            }
            foreach($actions as $action){

                if ($action->action == 'Restored Cancelled Application') {
                    $status_data['rob_60_cancelled'] = 0;
                }
            }

            if($status_data['rob_55_lease'] == 2){
                $status_data['rob_50_approve'] = 2;
            }

            if($status_data['rob_50_approve'] == 2){
                $status_data['rob_35_info'] = 2;
                $status_data['burser_approved'] = 1;
                $status_data['parent_approved'] = 1;
                $status_data['email_data_approved'] = 1;
            }

            //Update the students data
            User::where('id', $student->id)->update($status_data);
        }

        echo "done";
    }

    public function admin_updates_update_properties(){
        //Get current booking year
        $this_year = Lookups::where('group_id', '4')->first();

        //First make all rooms available
        Property::where('room_available', '0')->update(['room_available' => null]);
        Property::where('room_available', '1')->update(['room_available' => null]);

        //Set Inventory to Yes for all
        Property::where('inventory', '0')->update(['inventory' => '1']);
        Property::where('inventory', null)->update(['inventory' => '1']);

        //Now find building manager beds and mark inventory no
        Property::where('room_type', 'Building Manager')->update(['inventory' => '0']);
        
        //Then run through and mark all booked rooms
        $bed_used = Booking::where('year', $this_year->lookup_name)->get();        

        foreach($bed_used as $bed){
            $thisProperty = Property::where('residence', $bed->property)->where('room_type', $bed->roomType)->where('floor', $bed->floor)->where('unit', $bed->unit)->where('bed_code', $bed->bed_code)->get();
            $propertyData = "";

            //Find the correct bed
            foreach($thisProperty as $pi) {
                if ($pi->year <= $pi->year_end || $pi->year_end == 0) {
                    if (($this_year->lookup_name >= $pi->year && $this_year->lookup_name <= $pi->year_end) || ($this_year->lookup_name >= $pi->year && $pi->year_end == 0)) {
                        $propertyData = $pi;
                    }
                }
            }
            
            if (!empty($propertyData)) {
                //Update the bed availability
                Property::where('id', $propertyData->id)->update(['room_available' => '1']);

                //Update the booking with proper bed
                Booking::where('id', $bed->id)->update(['property_data_id' => $propertyData->id]);
            }
        }
        
    }

    public function admin_updates_update_property_id(request $request){
        $allProperties = Property::get();
        $allResidence = Residence::get();
        $allBookings = Booking::get();

        foreach($allProperties as $property){
            foreach($allResidence as $residence){
                if(strtoupper($property->residence) == strtoupper($residence->residence)){
                    $roomType = RoomTypes::where('property_id', $residence->id)->where('name', $property->room_type)->first();
                    Property::where('id', $property->id)->update(['residence' => $residence->id, 'room_type' => $roomType->id]);
                }
            }
        }

        foreach($allBookings as $booking){
            foreach($allResidence as $residence){
                if(strtoupper($booking->property) == strtoupper($residence->residence)){
                    $roomType = RoomTypes::where('property_id', $residence->id)->where('name', $booking->roomType)->first();
                    Booking::where('id', $booking->id)->update(['property' => $residence->id, 'roomType' => $roomType->id]);
                }
            }
        }

    }

    public function admin_updates_update_booking_code(Request $request){
        
        $allBookings = Booking::get();
        $residence = Residence::get();

        foreach($allBookings as $booking){
            
            $bookingYear = substr($booking->year, -2);
            $bookingPrefix = "";

            foreach($residence as $res){
                if($res->id == $booking->property){
                    $bookingPrefix = $res->building_prefix;
                }
            }

            $id_booking = $booking->id;
            $placeholder = "";

            for($i = 0; $i < 5 - strlen($id_booking); $i++){
                $placeholder .= "0";
            }

            $bookingID = $placeholder.$id_booking;

            Booking::where('id', $booking->id)->update(['booking_code' => $bookingYear.$bookingPrefix.$bookingID]);

        }
    }

    public function download_beds(Request $request){

        $property = $request->property;
        $year = $request->year;
        $this_year = Lookups::where('group_id', '4')->first();

        $query = "";

        if($property !== "All"){
            $query .= "where pd.residence = '".$property."'";
        }

        if($year !== "All"){

            if($year == "current"){
                $yearSearch = $this_year->lookup_name;
            }else{
                $yearSearch = floatval($this_year->lookup_name) + 1;
            }

            if($query !== ""){
                $query .= "and (pd.year_end = '0' or pd.year_end = '".$yearSearch."') ";
            }else{
                $query .= "where (pd.year_end = '0' or pd.year_end = '".$yearSearch."') ";
            }
        }

        $beds = [
			['<b>Residence</b>', '<b>Room Type</b>', '<b>Meta Info 1</b>', '<b>Meta Info 2</b>', '<b>Floor</b>', '<b>Unit</b>', '<b>Room</b>', '<b>Bed</b>', '<b>Bed Code</b>',  '<b>Inventory</b>', '<b>Year</b>', '<b>Year End</b>'],
		];

        $allBeds = DB::select('SELECT p.residence, r.name, pd.meta_info_1, pd.meta_info_2, pd.floor, pd.unit, pd.room, pd.bed, pd.bed_code, pd.inventory, pd.year, pd.year_end FROM property_data pd LEFT OUTER JOIN property p on pd.residence = p.id LEFT OUTER JOIN room_types r on pd.room_type = r.id '.$query.' ORDER BY pd.residence');

        foreach($allBeds as $bed){
            // $bedData = [$allBeds['residence']];
            array_push($beds, $bed);
        }

        $xlsx = new \Shuchkin\SimpleXLSXGen();
		$xlsx->addSheet( $beds, 'Beds');

        if($year !== "All"){
            //Bed_Codes_Export_YYYY
            if($year == "current"){
                $yearState = $this_year->lookup_name;
            }else{
                $yearState = floatval($this_year->lookup_name) + 1;
            }

            $downloadUrl = 'Bed_Codes_Export_'.$yearState.'.xlsx';

            $xlsx->saveAs('Bed_Codes_Export_'.$yearState.'.xlsx');
        }else{
            $downloadUrl = 'System_Bed_Codes_All_Years.xlsx';
            $xlsx->saveAs('System_Bed_Codes_All_Years.xlsx');
        }

		return $downloadUrl;
    }

    public function admin_updates_update_cancelled(Request $request){

        $allUsers = User::where('rob_60_cancelled', '1')->get();

        foreach($allUsers as $user){
            Booking::where('userId', $user->id)->update(['cancelled' => 1]);
        }
    }

    public function admin_updates_update_new_students(Request $request){

        $allUsers = User::where('userRole', '0')->get();

        foreach($allUsers as $user){

            StudentsInfo::create([
                'userUid' => $user->id,
                'bookingId' => $user->bookingRef,
                'userFirstName' => $user->userFirstName,
                'userLastname' => $user->userLastname,
                'userTitle' => $user->userTitle,
                'userDob' => $user->userDob,
                'userCellCode' => $user->userCellCode,
                'userCell' => $user->userCell,
                'userEmail' => $user->userEmail,
                'userId' => $user->userId,
                'userNationality' => $user->userNationality,
                'userPassport' => $user->userPassport,
                'institutionStudy' => $user->institutionStudy,
                'studentNo' => $user->studentNo,
                'institutionCampus' => $user->institutionCampus,
                'faculty' => $user->faculty,
                'year' => $user->year,
                'yearOfStudy' => $user->yearOfStudy,
                'studentType' => $user->studentType,
                'userAddress1' => $user->userAddress1,
                'userAddress2' => $user->userAddress2,
                'userAddressCity' => $user->userAddressCity,
                'userAddressPostal' => $user->userAddressPostal,
                'bookingRef' => $user->bookingRef,
                'bookingStatus' => $user->bookingStatus,
                'applicationStatus' => $user->applicationStatus,
                'sponFirstName' => $user->sponFirstName,
                'sponLastName' => $user->sponLastName,
                'sponCellCode' => $user->sponCellCode,
                'sponCell' => $user->sponCell,
                'sponEmail' => $user->sponEmail,
                'pNationality' => $user->pNationality,
                'pPassportNo' => $user->pPassportNo,
                'sponId' => $user->sponId,
                'pAddress1' => $user->pAddress1,
                'pAddress2' => $user->pAddress2,
                'pAddressCity' => $user->pAddressCity,
                'pAddressPostal' => $user->pAddressPostal,
                'c_credit_risk' => $user->c_credit_risk,
                'c_credit_check' => $user->c_credit_check,
                'burCompanyName' => $user->burCompanyName,
                'burRegNo' => $user->burRegNo,
                'burContactPerson' => $user->burContactPerson,
                'burContactCode' => $user->burContactCode,
                'burContactNo' => $user->burContactNo,
                'burEmail' => $user->burEmail,
                'b_meeting' => $user->b_meeting,
                'bAddress1' => $user->bAddress1,
                'bAddress2' => $user->bAddress2,
                'bAddressCity' => $user->bAddressCity,
                'bAddressPostal' => $user->bAddressPostal,
                'bPayment' => $user->bPayment,
                'bint' => $user->bint,
                'bIntName' => $user->bIntName,
                'kinFirstName' => $user->kinFirstName,
                'kinLastName' => $user->kinLastName,
                'kinTitle' => $user->kinTitle,
                'kinAddressStr' => $user->kinAddressStr,
                'kinAddressSuburb' => $user->kinAddressSuburb,
                'kinAddressCity' => $user->kinAddressCity,
                'kinAddressPost' => $user->kinAddressPost,
                'kinRelationship' => $user->kinRelationship,
                'kinTelCode' => $user->kinTelCode,
                'kinTelNo' => $user->kinTelNo,
                'kinCellCode' => $user->kinCellCode,
                'kinCellNo' => $user->kinCellNo,
                'kinWorkCode' => $user->kinWorkCode,
                'kinWorkNo' => $user->kinWorkNo,
                'kinEmail' => $user->kinEmail,
                'kinNationality' => $user->kinNationality,
                'kinPassportNo' => $user->kinPassportNo,
                'app_address' => $user->app_address,
                'app_address_p' => $user->app_address_p,
                'app_parent_kin' => $user->app_parent_kin,
                'id_data_approved' => $user->id_data_approved,
                'student_card_approved' => $user->student_card_approved,
                'registration_approved' => $user->registration_approved,
                'bursary_approved' => $user->bursary_approved,
                'bank_approved' => $user->bank_approved,
                'address_approved' => $user->address_approved,
                'kin_approved' => $user->kin_approved,
                'parent_approved' => $user->parent_approved,
                'burser_approved' => $user->burser_approved,
                'overall_status' => $user->overall_status,
                'email_info' => $user->email_info,
                'nsfas_bursar' => $user->nsfas_bursar,
                'rob_10_fee' => $user->rob_10_fee,
                'rob_15_id' => $user->rob_15_id,
                'rob_20_card' => $user->rob_20_card,
                'rob_25_reg' => $user->rob_25_reg,
                'rob_30_funds' => $user->rob_30_funds,
                'rob_35_info' => $user->rob_35_info,
                'rob_40_bed' => $user->rob_40_bed,
                'rob_45_other' => $user->rob_45_other,
                'rob_50_approve' => $user->rob_50_approve,
                'rob_55_lease' => $user->rob_55_lease,
                'email_data_approved' => $user->email_data_approved,
                'rob_60_cancelled' => $user->rob_60_cancelled,
                'onboard_complete' => $user->onboard_complete,
                'email_verified' => $user->email_verified,
                'fee_approved' => $user->fee_approved,
                'app_source' => $user->app_source,
            ]);

            if($user->rob_60_cancelled == 1){
                Booking::where('userId', $user->id)->update(['cancelled' => 1]);
            }

            $booking = Booking::where('userId', $user->id)->first();

            if(!empty($booking)){
                $allFiles = UserFiles::where('userUid', $user->id)->get();
                foreach($allFiles as $file){
                    UserFiles::where('id', $file->id)->update(['year'=> $booking->year]);
                }
    
                $allComments = Comments::where('user_uid', $user->id)->get();
                foreach($allComments as $comment){
                    Comments::where('id', $comment->id)->update(['bookingId'=> $booking->id]);
                }
    
                $allActionLists = ActionList::where('userUid', $user->id)->get();
                foreach($allActionLists as $list){
                    ActionList::where('id', $list->id)->update(['year'=> $booking->year]);
                }
            }

        }
    }
}

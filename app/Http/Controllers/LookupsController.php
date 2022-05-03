<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lookups;
use App\Models\User;
use App\Models\UserFiles;
use App\Models\Booking;
use App\Models\Property;
use App\Models\ActionList;
use Auth;

class LookupsController extends Controller {
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

            // $year = Lookups::where('group_id', '4')->first();

            // if(date($year->lookup_name) < date($request->value)){

            //     $bookingsList = Booking::whereNotNull('property_data_id')->where('year', $year->lookup_name)->get();
    
            //     foreach($bookingsList as $booking){
            //         Property::where('id', $booking->property_data_id)->update(['room_available' => null]);
            //     }
            // }else{
            //     $bookingsList = Booking::whereNotNull('property_data_id')->where('year', $request->value)->get();

            //     foreach($bookingsList as $booking){
            //         Property::where('id', $booking->property_data_id)->update(['room_available' => '1']);
            //     }
            // }


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
}

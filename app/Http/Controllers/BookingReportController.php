<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\Models\Property;
use App\Models\Residence;
use App\Models\RoomTypes;
use App\Models\Booking;
use App\Models\User;
use App\Models\Lookups;
use App\Models\PropertyPermission;


class BookingReportController extends Controller
{
    public function booking_summary(Request $request){

        $residences = [];
        $allProp = Residence::get();
        $permissionProp = PropertyPermission::where('userUid', Auth::user()->id)->get();

        foreach($allProp as $prop){
            foreach($permissionProp as $permission){
                if($permission->property == $prop->id){
                    array_push($residences, $prop);
                }
            }
        }


        // $residences = Residence::get();
        $lookupYear = Lookups::where('group_id', '4')->first();
        $propertyData = Property::where('inventory', '1')->get();
        $allBookings = Booking::where('year', $lookupYear->lookup_name)->get();
        $allRoomTypes = RoomTypes::where('name', '!=', 'Building Manager')->get();

        

        $summaryData = [];

        foreach($residences as $res){

            $propSummary['building_name'] = $res->residence;
            $propSummary['total_beds'] = 0;
            $propSummary['beds_allocated'] = 0;
            $propSummary['beds_waitlisted'] = 0;
            $propSummary['room_type_data'] = [];

            // foreach($propertyData as $prop){
            //     if($prop->residence == $res->id){

            //         if($prop->year == $lookupYear->lookup_name || $prop->year < $lookupYear->lookup_name){
            //             if($prop->year_end == '0' || $prop->year_end == $lookupYear->lookup_name){
            //                 $propSummary['total_beds']++;
            //             }
            //         }
            //     }
            // }

            foreach($allRoomTypes as $room){
                if($res->id == $room->property_id ){
                    $roomSummary['room_type'] = $room->name;
                    $roomSummary['room_online'] = $room->online;
                    $roomSummary['room_id'] = $room->id;

                    $roomSummary['room_beds'] = 0;
                    $roomSummary['room_allocated'] = 0;
                    $roomSummary['room_waitlisted'] = 0;

                    foreach($propertyData as $prop){
                        if($prop->residence == $res->id && $room->id == $prop->room_type){
                            if($prop->year == $lookupYear->lookup_name || $prop->year < $lookupYear->lookup_name){
                                if($prop->year_end == 0 || $prop->year_end == $lookupYear->lookup_name){
                                    $roomSummary['room_beds']++;
                                    $propSummary['total_beds']++;
                                }
                            }
                        }
                    }

                    foreach($allBookings as $booking){
                        if($booking->property == $res->id && $booking->roomType == $room->id){
                            $user = User::where('id', $booking->userId)->where('rob_60_cancelled', '0')->first();
        
                            if(!empty($user)){
                                if($user->rob_10_fee == '2' && $user->rob_40_bed == '2'){
                                    $roomSummary['room_allocated']++;
                                    $propSummary['beds_allocated']++;
                                }else if($user->rob_10_fee == '2' && $user->rob_40_bed == '1'){
                                    $roomSummary['room_waitlisted']++;
                                    $propSummary['beds_waitlisted']++;
                                }
                            }
                        }
                    }

                    $roomSummary['room_total_bookings'] = $roomSummary['room_allocated'] + $roomSummary['room_waitlisted'];
                    if($roomSummary['room_total_bookings'] == 0 || $roomSummary['room_beds'] == 0){
                        $roomSummary['room_booking_percent'] = 0;
                    }else{
                        $roomSummary['room_booking_percent'] = round($roomSummary['room_total_bookings'] / $roomSummary['room_beds'] * 100);
                    }

                    array_push($propSummary['room_type_data'], $roomSummary);
                } 
            }

            // foreach($allBookings as $booking){
            //     if($booking->property == $res->id){
            //         $user = User::where('id', $booking->userId)->where('rob_60_cancelled', '0')->first();

            //         if(!empty($user)){
            //             if($user->rob_10_fee == '2' && $user->rob_40_bed == '2'){
            //                 $propSummary['beds_allocated']++;
            //             }else if($user->rob_10_fee == '2' && $user->rob_40_bed == '1'){
            //                 $propSummary['beds_waitlisted']++;
            //             }
            //         }
            //     }
            // }

            $propSummary['total_bookings'] = $propSummary['beds_allocated'] + $propSummary['beds_waitlisted'];
            

            if($propSummary['total_bookings'] == 0 || $propSummary['total_beds'] == 0){
                $propSummary['booking_percent'] = 0;
            }else{
                $propSummary['booking_percent'] = round($propSummary['total_bookings'] / $propSummary['total_beds'] * 100);
            }

            array_push($summaryData, $propSummary);

        }

        $total['beds'] = 0;
        $total['bookings'] = 0;
        $total['allocated'] = 0;
        $total['waitlisted'] = 0;

        foreach($summaryData as $sum){
            $total['beds'] = $total['beds'] + $sum['total_beds'];
            $total['bookings'] = $total['bookings'] + $sum['total_bookings'];
            $total['allocated'] = $total['allocated'] + $sum['beds_allocated'];
            $total['waitlisted'] = $total['waitlisted'] + $sum['beds_waitlisted'];
        }

        $total['percent'] =  round($total['bookings'] / $total['beds'] * 100);

        return view('admin.report.booking_summary', ["headerTitle" => "Booking Summary", "title" => "Booking Summary", "summaryData" => $summaryData, "total" => $total]);
    }

    public function room_activation(Request $request){
        $roomAvailableText = $request->roomAvailableText;
        $roomID = $request->roomID;

        RoomTypes:: where('id', $roomID)->update(["online" => $roomAvailableText == "Yes" ? '0' : '1']);
    }
}

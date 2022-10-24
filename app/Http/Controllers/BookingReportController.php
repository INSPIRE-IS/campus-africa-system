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
use App\Models\StudentsInfo;

class BookingReportController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function booking_summary(Request $request){

        // $jsonDataRooms = json_decode(file_get_contents('https://www.campusafrica.co.za/wp-json/wp/v2/rooms?per_page=100'));

        // dd($jsonDataRooms);

        $residences = [];
        $allProp = Residence::whereNull('status')->get();
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
                    $roomSummary['room_map_id'] = $room->room_map_slug;
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
                        if($booking->cancelled != '1'){

                            if($booking->property == $res->id && $booking->roomType == $room->id){
                                // $user = User::where('id', $booking->userId)->where('rob_60_cancelled', '0')->first();
                                $user = StudentsInfo::where('userUid', $booking->userId)->where('year', $lookupYear->lookup_name)->first();
    
            
                                if(!empty($user)){
                                    if($user->rob_60_cancelled != 1){
    
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
        $roomMap = $request->roomMap;
        $setting = $request->roomAvailableText == "Yes" ? 1 : 0;

        RoomTypes:: where('id', $roomID)->update(["online" => $roomAvailableText == "Yes" ? '0' : '1']);

        $allRooms = RoomTypes::where('name', '!=', 'Building Manager')->get();

        $roomActive = 0;
        $roomCount = 0;
        foreach($allRooms as $room){
            if($room->room_map_slug == $roomMap){
                $roomCount++;
                if($setting == 1){
                    if($room->online == '1'){
                        $roomActive++;
                    }
                }else{
                    if($room->online == '0'){
                        $roomActive++;
                    }
                }
            }
        }

        if($setting === 0 && $roomCount > 1){
            $roomActive = 0;
        }

        $response = "";

        //cm9nZXI6eXdxcENoSVpXUmNUV0o4YVFmNXc2T0Z2
        //cm9nZXI6OHlRVVNRUTFHZVJmaHlsMXV1QWZNTHdM

        if($roomActive === 0){

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://campusafrica.whitehart.co.za/wp-json/wp/v2/rooms/'.$roomMap,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS =>'{
                    "acf": {
                        "offline": '.$setting.'
                    }
                }',
                CURLOPT_HTTPHEADER => array(
                    'Authorization: Basic cm9nZXI6OHlRVVNRUTFHZVJmaHlsMXV1QWZNTHdM',
                    'Content-Type: application/json',
                    'Cookie: wordpress_test_cookie=WP%20Cookie%20check'
                ),
            ));

            $response = curl_exec($curl);
            curl_close($curl);

        }

        return $response;
    }
}

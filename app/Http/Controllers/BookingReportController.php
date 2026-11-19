<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Property;
use App\Models\Residence;
use App\Models\RoomTypes;
use App\Models\Booking;
use App\Models\User;
use App\Models\Lookups;
use App\Models\PropertyPermission;
use App\Models\StudentsInfo;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BookingReportController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function booking_summary(Request $request)
    {

        // $jsonDataRooms = json_decode(file_get_contents('https://www.campusafrica.co.za/wp-json/wp/v2/rooms?per_page=100'));

        //dd($jsonDataRooms);
        // $residences = Residence::get();
        $year = Session::get('year');
        $yearLookups = Lookups::internalYearLookups();
        if ($year == null) {
            $year = $yearLookups->first()->lookup_name ?? (Lookups::activeYearValue() ?? (string) now()->year);
            session(['year' => $year]);
        }
        if (!Lookups::isInternalYear($year)) {
            $year = $yearLookups->first()->lookup_name ?? (Lookups::activeYearValue() ?? (string) now()->year);
            session(['year' => $year]);
        }
        $residences = [];
        $residenceIds = [3, 4, 7];
        $allProp = Residence::whereNull('status')
            ->when((int)$year >= 2026, function ($q) use ($residenceIds) {
                $q->whereNotIn('id', $residenceIds);
            })
            ->get();

        $permissionProp = PropertyPermission::where('userUid', Auth::user()->id)->get();
        $lookups = Lookups::get();

        foreach ($allProp as $prop) {
            foreach ($permissionProp as $permission) {
                if ($permission->property == $prop->id) {
                    array_push($residences, $prop);
                }
            }
        }

        $propertyData = Property::where('inventory', '1')->get();
        $allBookings = Booking::where('year', $year)->get();
        $allRoomTypes = RoomTypes::where('name', '!=', 'Building Manager')->get();



        $summaryData = [];

        foreach ($residences as $res) {

            $propSummary['building_name'] = $res->residence;
            $propSummary['total_beds'] = 0;
            $propSummary['beds_allocated'] = 0;
            $propSummary['beds_waitlisted'] = 0;
            $propSummary['room_type_data'] = [];

            // foreach($propertyData as $prop){
            //     if($prop->residence == $res->id){

            //         if($prop->year == $year || $prop->year < $year){
            //             if($prop->year_end == '0' || $prop->year_end == $year){
            //                 $propSummary['total_beds']++;
            //             }
            //         }
            //     }
            // }

            foreach ($allRoomTypes as $room) {
                if ($res->id == $room->property_id) {
                    $roomSummary['room_type'] = $room->name;
                    $roomSummary['room_online'] = $room->online;
                    $roomSummary['room_map_id'] = $room->room_map_slug;
                    $roomSummary['room_id'] = $room->id;

                    $roomSummary['room_beds'] = 0;
                    $roomSummary['room_allocated'] = 0;
                    $roomSummary['room_waitlisted'] = 0;

                    foreach ($propertyData as $prop) {
                        if ($prop->residence == $res->id && $room->id == $prop->room_type) {
                            if ($prop->year == $year || $prop->year < $year) {
                                if ($prop->year_end == 0 || $prop->year_end == $year) {
                                    $roomSummary['room_beds']++;
                                    $propSummary['total_beds']++;
                                }
                            }
                        }
                    }

                    foreach ($allBookings as $booking) {
                        if ($booking->cancelled != '1') {

                            if ($booking->property == $res->id && $booking->roomType == $room->id) {
                                // $user = User::where('id', $booking->userId)->where('rob_60_cancelled', '0')->first();
                                $user = StudentsInfo::where('userUid', $booking->userId)->where('year', $year)->first();


                                if (!empty($user)) {
                                    if ($user->rob_60_cancelled != 1) {

                                        if ($user->rob_10_fee == '2' && $user->rob_40_bed == '2') {
                                            $roomSummary['room_allocated']++;
                                            $propSummary['beds_allocated']++;
                                        } else if ($user->rob_10_fee == '2' && $user->rob_40_bed != '2') {
                                            $roomSummary['room_waitlisted']++;
                                            $propSummary['beds_waitlisted']++;
                                        }
                                    }
                                }
                            }
                        }
                    }

                    $roomSummary['room_total_bookings'] = $roomSummary['room_allocated'] + $roomSummary['room_waitlisted'];
                    if ($roomSummary['room_total_bookings'] == 0 || $roomSummary['room_beds'] == 0) {
                        $roomSummary['room_booking_percent'] = 0;
                    } else {
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


            if ($propSummary['total_bookings'] == 0 || $propSummary['total_beds'] == 0) {
                $propSummary['booking_percent'] = 0;
            } else {
                $propSummary['booking_percent'] = round($propSummary['total_bookings'] / $propSummary['total_beds'] * 100);
            }
            array_push($summaryData, $propSummary);
        }

        $total['beds'] = 0;
        $total['bookings'] = 0;
        $total['allocated'] = 0;
        $total['waitlisted'] = 0;

        foreach ($summaryData as $sum) {
            $total['beds'] = $total['beds'] + $sum['total_beds'];
            $total['bookings'] = $total['bookings'] + $sum['total_bookings'];
            $total['allocated'] = $total['allocated'] + $sum['beds_allocated'];
            $total['waitlisted'] = $total['waitlisted'] + $sum['beds_waitlisted'];
        }

        $total['percent'] =  round($total['bookings'] / $total['beds'] * 100);


        return view('admin.report.booking_summary', ["headerTitle" => "Booking Summary", "title" => "Booking Summary", "lookups" => $lookups, "yearLookups" => $yearLookups, "year" => $year, "summaryData" => $summaryData, "total" => $total]);
    }

    public function setYear(Request $request)
    {
        $request->validate([
            'year' => 'required|integer',
        ]);

        if (!Lookups::isInternalYear($request->year)) {
            return redirect()->back()->withErrors(['message' => 'Invalid year selected.']);
        }

        session(['year' => $request->year]);

        //return redirect()->back(); // redirect back to summary or index
        return redirect()->route('booking.summary');
    }

    public function room_activation(Request $request)
    {
        if (Auth::user()->role_toggle_online_booking != '1') {
            return response()->json([
                'message' => 'You do not have permission to change online booking availability.',
            ], 403);
        }

        if (!config('services.wordpress.writes_enabled')) {
            Log::warning('WordPress room activation blocked: API writes are disabled for this environment.');

            return response()->json([
                'message' => 'WordPress room activation is disabled for this environment.',
            ], 403);
        }

        $wordpressUrl = rtrim((string) config('services.wordpress.url'), '/');
        $wordpressUsername = config('services.wordpress.username');
        $wordpressPassword = config('services.wordpress.application_password');

        if (!$wordpressUrl || !$wordpressUsername || !$wordpressPassword) {
            Log::error('WordPress room activation failed: API credentials are not configured.');

            return response()->json([
                'message' => 'WordPress room activation is not configured.',
            ], 500);
        }

        $request->validate([
            'roomAvailableText' => 'required|in:Yes,No',
            'roomID' => 'required|integer|exists:room_types,id',
        ]);

        $selectedRoom = RoomTypes::findOrFail($request->roomID);
        $roomMap = (string) $selectedRoom->room_map_slug;
        $currentOnline = $selectedRoom->online == '1';
        $expectedCurrentText = $currentOnline ? 'Yes' : 'No';

        if ($request->roomAvailableText !== $expectedCurrentText) {
            return response()->json([
                'message' => 'Room availability changed after this page loaded. Refresh and try again.',
            ], 409);
        }

        if ($roomMap === '') {
            return response()->json([
                'message' => 'This room type does not have a WordPress room mapping.',
            ], 422);
        }

        $setting = $currentOnline ? 1 : 0;
        $newOnline = $currentOnline ? '0' : '1';

        $allRooms = RoomTypes::where('name', '!=', 'Building Manager')->get();

        $roomActive = 0;
        $roomCount = 0;
        foreach ($allRooms as $candidateRoom) {
            if ($candidateRoom->room_map_slug == $roomMap) {
                $roomCount++;
                $onlineAfterUpdate = $candidateRoom->id == $request->roomID ? $newOnline : $candidateRoom->online;
                if ($setting == 1) {
                    if ($onlineAfterUpdate == '1') {
                        $roomActive++;
                    }
                } else {
                    if ($onlineAfterUpdate == '0') {
                        $roomActive++;
                    }
                }
            }
        }

        if ($setting === 0 && $roomCount > 1) {
            $roomActive = 0;
        }

        $wordpressUpdated = false;

        if ($roomActive === 0) {
            try {
                $wordpressResponse = Http::withBasicAuth($wordpressUsername, $wordpressPassword)
                    ->acceptJson()
                    ->timeout(30)
                    ->post($wordpressUrl . '/rooms/' . $roomMap, [
                        'acf' => [
                            'offline' => $setting,
                        ],
                    ]);

                $responseData = $wordpressResponse->json();
                $returnedRoomId = data_get($responseData, 'id');
                $returnedOffline = data_get($responseData, 'acf.offline');
                $responseMatches = (string) $returnedRoomId === $roomMap
                    && (bool) $returnedOffline === (bool) $setting;

                Log::info('WordPress room activation response.', [
                    'local_room_type_id' => $selectedRoom->id,
                    'wordpress_room_id' => $roomMap,
                    'requested_offline' => (bool) $setting,
                    'http_status' => $wordpressResponse->status(),
                    'returned_room_id' => $returnedRoomId,
                    'returned_offline' => $returnedOffline,
                    'response_matches' => $responseMatches,
                    'actor_id' => Auth::id(),
                ]);

                if (!$wordpressResponse->successful() || !$responseMatches) {
                    Log::error('WordPress room activation was not applied.', [
                        'local_room_type_id' => $selectedRoom->id,
                        'wordpress_room_id' => $roomMap,
                        'http_status' => $wordpressResponse->status(),
                        'wordpress_error_code' => data_get($responseData, 'code'),
                        'wordpress_error_message' => data_get($responseData, 'message'),
                        'actor_id' => Auth::id(),
                    ]);

                    return response()->json([
                        'message' => 'WordPress did not confirm the room availability update. No local changes were made.',
                    ], 502);
                }

                $wordpressUpdated = true;
            } catch (\Throwable $exception) {
                Log::error('WordPress room activation request failed.', [
                    'local_room_type_id' => $selectedRoom->id,
                    'wordpress_room_id' => $roomMap,
                    'error' => $exception->getMessage(),
                    'actor_id' => Auth::id(),
                ]);

                return response()->json([
                    'message' => 'WordPress could not be reached. No local changes were made.',
                ], 502);
            }
        }

        $selectedRoom->online = $newOnline;
        $selectedRoom->save();

        Log::info('Local room availability updated.', [
            'local_room_type_id' => $selectedRoom->id,
            'wordpress_room_id' => $roomMap,
            'online' => (bool) $newOnline,
            'wordpress_updated' => $wordpressUpdated,
            'actor_id' => Auth::id(),
        ]);

        return response()->json([
            'message' => 'Room type updated.',
            'online' => (bool) $newOnline,
            'wordpress_updated' => $wordpressUpdated,
        ]);
    }
}

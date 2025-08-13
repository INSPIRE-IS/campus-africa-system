<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Lookups;
use App\Models\Residence;
use App\Models\StudentsInfo;
use App\Models\User;
use Illuminate\Http\Request;
use App\Services\FirebaseService;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class MaintenanceController extends Controller
{
    protected $firebase;

    public function __construct(FirebaseService $firebase)
    {
        $this->firebase = $firebase;
    }

    public function showStructure()
    {
        $structure = $this->firebase->getFirestoreStructure();
        return view('firebase.structure', compact('structure'));
    }


    public function create()
    {
        $userId = Auth::user()->id;
        //get current year
        $session_year = Session::get('year');
        $thisYear = (new \DateTime())->format("Y");
        if (!empty($session_year)) {
            $this_year = Lookups::where('group_id', '4')->where('lookup_name', '=', $session_year)->first();
            $this_year = $this_year->lookup_name;
        } else {
            $this_year = Lookups::where('group_id', '4')->first();
            $this_year = $this_year->lookup_name;
        }
        $studInfo = StudentsInfo::select('bookingId', 'userFirstName', 'userLastname', 'userEmail', 'userCellCode', 'userCell', 'year', 'id')
            ->where('userUid', $userId)
            ->where('year', $thisYear)->first();
        $booking = Booking::select('id', 'bed_code', 'property', 'booking_code')->find($studInfo->bookingId);
        $property = Residence::select('residence', 'id')->where('id', $booking->property ?? null)->first();
        //dd($studInfo->userEmail);

        try {
            $existing = $this->firebase->getUserByEmail($studInfo->userEmail);
            if (!empty($existing)) {
                $uid = $existing->uid;
            }
        } catch (Exception $e) {
            $uid = null;
        }

        $user = [
            "id" => $booking->booking_code,
            "bed_code" => $booking->bed_code,
            "email" => $studInfo->userEmail,
            "first_name" => $studInfo->userFirstName,
            "residence" =>  $property->residence ?? null,
            "last_name" => $studInfo->userLastname,
            "phone_number" => $studInfo->userCellCode . $studInfo->userCell,
            "role" => "Tenant",
            "uid" => $uid,
        ];
        Session::put('year', $thisYear);
        $headerTitle = "Maintenance App for ";
        $active = 1;

        return view('user.firebase.register', compact('user', 'headerTitle', 'active'));
    }

    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|min:6',
            'confirm_password' => 'required|same:password',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'phone_number' => 'required|string',
            'bed_code' => 'required|string',
            'residence' => 'required|string',
            'role' => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $userId = Auth::user()->id;
        $session_year = Session::get('year');
        if (!empty($session_year)) {
            $this_year = Lookups::where('group_id', '4')->where('lookup_name', '=', $session_year)->first();
            $this_year = $this_year->lookup_name;
        } else {
            $this_year = Lookups::where('group_id', '4')->first();
            $this_year = $this_year->lookup_name;
        }
        $studInfo = StudentsInfo::select('bookingId', 'userFirstName', 'userLastname', 'userEmail', 'userCellCode', 'userCell', 'year', 'id')->where('userUid', $userId)->where('year', $this_year)->first();
        $booking = Booking::select('id', 'bed_code', 'property', 'booking_code')->find($studInfo->bookingId);
        $property = Residence::select('residence', 'id')->where('id', $booking->property ?? null)->first();


        try {
            $existing = $this->firebase->getUserByEmail($studInfo->userEmail);
            if (!empty($existing)) {
                $uid = $existing->uid;
            }
        } catch (Exception $e) {
            $uid = null;
        }

        // $user = [
        //     "id" => $booking->booking_code,
        //     "bed_code" => $booking->bed_code,
        //     "email" => $studInfo->userEmail,
        //     "first_name" => $studInfo->userFirstName,
        //     "residence" =>  $property->residence ?? null,
        //     "last_name" => $studInfo->userLastname,
        //     "phone_number" => $studInfo->userCellCode . $studInfo->userCell,
        //     "role" => "Tenant",
        //     "uid" => $uid,
        // ];

        $details = [
            "id" => $booking->booking_code ?? null,
            "bed_code" => $booking->bed_code,
            "email" => $studInfo->userEmail,
            "first_name" => $studInfo->userFirstName,
            "groups" => [0 =>  $property->residence],
            "last_name" => $studInfo->userLastname,
            "phone_number" => $studInfo->userCellCode . $studInfo->userCell,
            "role" => "Tenant",
            "password" => $request->input('password'),
        ];
        // dd($details);
        try {
            $userId = Auth::user()->id;
            if (!$userId) {
                $this->error('User not found.');
                return;
            }
            Log::info("Updating local user password");
            //Log::info($user->uid);
            $localUser = User::find($userId);
            //User::where('id', $userId)->update(['password' => Hash::make($request->input('password'))]);
            Log::info('✅ Password. ' . $localUser->password);
            $localUser->password = Hash::make($request->input('password'));
            $localUser->save();
            Log::info('✅ Password updated. ' . $localUser->password);
            Log::info("Updating firebase user and password");
            $user = $this->firebase->registerOrUpdateUser($details);
            // Also update local Laravel user's password


            return back()->with('success', 'User registered: ' . $user->uid);
        } catch (\Throwable $e) {
            logger()->error('errrooorrr');
            Log::info($e->getMessage());
            return back()->with('error', $e->getMessage());
        }
    }

    public function showPasswordForm()
    {
        return view('firebase.password');
    }

    public function updatePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'uid' => 'required',
            'email' => 'required|email',
            'new_password' => 'required|min:6',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $this->firebase->updatePassword($request->uid, $request->new_password);
            return back()->with('success', 'Password updated.');
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function viewMaintenanceItems(Request $request)
    {
        $items = $this->firebase->getMaintenanceItems(20, $request->startAfter);
        return view('firebase.maintenance', compact('items'));
    }
}

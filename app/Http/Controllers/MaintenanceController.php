<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\FirebaseRegistrationLog;
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

        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $userId = Auth::user()->id;
        $thisYear = $this->currentMaintenanceYear();
        $eligibility = $this->firebaseRegistrationEligibility($userId, $thisYear);

        if (!$eligibility['eligible']) {
            $futureYearCheck = $this->openFutureApplicationCheck($userId, $thisYear);
            $this->writeFirebaseRegistrationLog('blocked', 'form_open', $eligibility['message'], [
                'user' => Auth::user(),
                'studentInfo' => $eligibility['studentInfo'] ?? null,
                'booking' => $eligibility['booking'] ?? null,
                'year' => $thisYear,
                'meta' => array_merge($eligibility['meta'] ?? [], [
                    'open_future_application_check' => $futureYearCheck,
                ]),
            ]);

            return redirect('/dashboard')->withErrors(['message' => $eligibility['message']]);
        }

        $studInfo = $eligibility['studentInfo'];
        $booking = $eligibility['booking'];
        $property = $eligibility['residence'];
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

        $this->writeFirebaseRegistrationLog('viewed', 'form_open', 'Firebase registration form opened.', [
            'user' => Auth::user(),
            'studentInfo' => $studInfo,
            'booking' => $booking,
            'year' => $thisYear,
            'firebase_uid' => $uid,
            'meta' => $eligibility['meta'] ?? [],
        ]);

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

        $userId = Auth::user()->id ?? null;
        $this_year = $this->currentMaintenanceYear();

        if ($validator->fails()) {
            $this->writeFirebaseRegistrationLog('failed', 'validation', 'Firebase registration validation failed.', [
                'user' => Auth::user(),
                'year' => $this_year,
                'email' => $request->input('email'),
                'meta' => ['errors' => $validator->errors()->toArray()],
            ]);

            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $eligibility = $this->firebaseRegistrationEligibility($userId, $this_year);
        if (!$eligibility['eligible']) {
            $futureYearCheck = $this->openFutureApplicationCheck($userId, $this_year);
            $this->writeFirebaseRegistrationLog('blocked', 'submit_eligibility', $eligibility['message'], [
                'user' => Auth::user(),
                'studentInfo' => $eligibility['studentInfo'] ?? null,
                'booking' => $eligibility['booking'] ?? null,
                'year' => $this_year,
                'email' => $request->input('email'),
                'meta' => array_merge($eligibility['meta'] ?? [], [
                    'open_future_application_check' => $futureYearCheck,
                ]),
            ]);

            return back()->with('error', $eligibility['message'])->withInput();
        }

        $studInfo = $eligibility['studentInfo'];
        $booking = $eligibility['booking'];
        $property = $eligibility['residence'];

        $this->writeFirebaseRegistrationLog('attempted', 'submit', 'Firebase registration submitted.', [
            'user' => Auth::user(),
            'studentInfo' => $studInfo,
            'booking' => $booking,
            'year' => $this_year,
            'email' => $studInfo->userEmail,
            'meta' => $eligibility['meta'] ?? [],
        ]);

        $uid = null;
        try {
            $existing = $this->firebase->getUserByEmail($studInfo->userEmail);
            if (!empty($existing)) {
                $uid = $existing->uid;
            }
        } catch (Exception $e) {
            Log::info("No Firebase user found for email will create one: " . $studInfo->userEmail);
            $uid = null;
        }

        $details = [
            "id" => $booking->booking_code ?? null,
            "bed_code" => $booking->bed_code,
            "email" => $studInfo->userEmail,
            "first_name" => $studInfo->userFirstName,
            "groups" => [0 =>  $property->residence],
            "last_name" => $studInfo->userLastname,
            "display_name" =>  $studInfo->userFirstName . " " . $studInfo->userLastname,
            "cell_code" =>  $studInfo->userCellCode,
            "phone_number" =>  $studInfo->userCellCode . $studInfo->userCell,
            "role" => "tenant",
            "password" => $request->input('password'),

            "building" => $property->residence,
            "building_id" => $booking->property,
            "floor" => $booking->floor,
            "room" => $booking->bed_code,
            "room_type" => $eligibility['roomType']->name,

            "is_active" => true,
            "needs_auth_setup" => false,
        ];
        $extra = [];
        // $extra = [
        //     'display_name' => $studInfo->userFirstName . ' ' . $studInfo->userLastname,
        //     'phone_number' => $studInfo->userCellCode . $studInfo->userCell,
        // ];


        //dd($details);
        try {
            $userId = Auth::user()->id;
            if (!$userId) {
                $this->writeFirebaseRegistrationLog('failed', 'local_user', 'User not found during Firebase registration.', [
                    'year' => $this_year,
                    'email' => $studInfo->userEmail ?? $request->input('email'),
                ]);

                return back()->with('error', 'User not found.');
            }
            Log::info("Updating local user password");
            //Log::info($user->uid);
            $localUser = User::find($userId);
            if (!$localUser) {
                $this->writeFirebaseRegistrationLog('failed', 'local_user', 'Local user row not found during Firebase registration.', [
                    'year' => $this_year,
                    'email' => $studInfo->userEmail ?? $request->input('email'),
                ]);

                return back()->with('error', 'User not found.');
            }
            //User::where('id', $userId)->update(['password' => Hash::make($request->input('password'))]);
            Log::info('✅ Password. ' . $localUser->password);
            $localUser->password = Hash::make($request->input('password'));
            $localUser->save();
            Log::info('✅ Password updated. ' . $localUser->password);
            Log::info('✅ existing firebase user id. ' . $uid);
            Log::info("Updating firebase user and password");
            $user = $this->firebase->registerOrUpdateUser($details, $extra);
            // Also update local Laravel user's password

            $this->writeFirebaseRegistrationLog('success', 'firebase_register_or_update', 'Firebase registration completed.', [
                'user' => $localUser,
                'studentInfo' => $studInfo,
                'booking' => $booking,
                'year' => $this_year,
                'email' => $studInfo->userEmail,
                'firebase_uid' => $user->uid ?? null,
                'meta' => $eligibility['meta'] ?? [],
            ]);

            return back()->with('success', 'User registered: ' . $user->uid);
        } catch (\Throwable $e) {
            logger()->error('errrooorrr');
            Log::info($e->getMessage());

            $this->writeFirebaseRegistrationLog('failed', 'firebase_register_or_update', $e->getMessage(), [
                'user' => Auth::user(),
                'studentInfo' => $studInfo,
                'booking' => $booking,
                'year' => $this_year,
                'email' => $studInfo->userEmail ?? $request->input('email'),
                'firebase_uid' => $uid,
                'meta' => array_merge($eligibility['meta'] ?? [], [
                    'exception' => get_class($e),
                ]),
            ]);

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

    private function currentMaintenanceYear(): string
    {
        return now()->format('Y');
    }

    private function firebaseRegistrationEligibility(?int $userId, string $year): array
    {
        $user = $userId ? User::find($userId) : null;
        $studentInfo = null;
        $booking = null;
        $residence = null;
        $roomType = null;

        if (!$user) {
            return $this->firebaseEligibilityResult(false, 'No logged-in user was found.', $studentInfo, $booking, $residence, $roomType);
        }

        if ((int) $user->userRole !== 0) {
            return $this->firebaseEligibilityResult(false, 'Only student accounts can register for the maintenance app.', $studentInfo, $booking, $residence, $roomType);
        }

        if (method_exists($user, 'isBanned') && $user->isBanned()) {
            return $this->firebaseEligibilityResult(false, 'Your application can not be processed currently. Please contact administrator with your ID number for assistance.', $studentInfo, $booking, $residence, $roomType);
        }

        $studentInfo = StudentsInfo::where('userUid', $user->id)
            ->where('year', $year)
            ->first();

        if (!$studentInfo) {
            return $this->firebaseEligibilityResult(false, "No {$year} student record was found for this account.", $studentInfo, $booking, $residence, $roomType);
        }

        if ((int) $studentInfo->rob_60_cancelled === 1) {
            return $this->firebaseEligibilityResult(false, "The {$year} application is cancelled and cannot register for the maintenance app.", $studentInfo, $booking, $residence, $roomType);
        }

        if ($studentInfo->bookingId) {
            $booking = Booking::with('residence:residence,id', 'roomType:name,id,property_id')
                ->where('id', $studentInfo->bookingId)
                ->where('userId', $user->id)
                ->where('year', $year)
                ->first();
        }

        if (!$booking) {
            $booking = Booking::with('residence:residence,id', 'roomType:name,id,property_id')
                ->where('userId', $user->id)
                ->where('year', $year)
                ->first();
        }

        if (!$booking) {
            return $this->firebaseEligibilityResult(false, "No active {$year} booking was found for this account.", $studentInfo, $booking, $residence, $roomType);
        }

        if ((int) $booking->cancelled === 1) {
            return $this->firebaseEligibilityResult(false, "The {$year} booking is cancelled and cannot register for the maintenance app.", $studentInfo, $booking, $residence, $roomType);
        }

        $residence = $booking->relationLoaded('residence') ? $booking->getRelation('residence') : null;
        $residence = $residence ?: Residence::select('residence', 'id')->where('id', $booking->property)->first();
        $roomType = $booking->relationLoaded('roomType') ? $booking->getRelation('roomType') : null;
        $roomType = $roomType ?: $booking->roomType()->first();

        if (empty($booking->bed_code)) {
            return $this->firebaseEligibilityResult(false, "The {$year} booking does not have a bed code allocated yet.", $studentInfo, $booking, $residence, $roomType);
        }

        if (!$residence) {
            return $this->firebaseEligibilityResult(false, "The {$year} booking does not have a valid residence.", $studentInfo, $booking, $residence, $roomType);
        }

        if (!$roomType) {
            return $this->firebaseEligibilityResult(false, "The {$year} booking does not have a valid room type.", $studentInfo, $booking, $residence, $roomType);
        }

        return $this->firebaseEligibilityResult(true, 'Student is eligible for Firebase maintenance app registration.', $studentInfo, $booking, $residence, $roomType);
    }

    private function firebaseEligibilityResult(bool $eligible, string $message, $studentInfo = null, $booking = null, $residence = null, $roomType = null): array
    {
        return [
            'eligible' => $eligible,
            'message' => $message,
            'studentInfo' => $studentInfo,
            'booking' => $booking,
            'residence' => $residence,
            'roomType' => $roomType,
            'meta' => [
                'student_info_found' => !empty($studentInfo),
                'booking_found' => !empty($booking),
                'residence_found' => !empty($residence),
                'room_type_found' => !empty($roomType),
                'student_cancelled' => $studentInfo ? $studentInfo->rob_60_cancelled : null,
                'booking_cancelled' => $booking ? $booking->cancelled : null,
                'booking_code' => $booking ? $booking->booking_code : null,
                'bed_code' => $booking ? $booking->bed_code : null,
                'rob_50_approve' => $studentInfo ? $studentInfo->rob_50_approve : null,
                'rob_55_lease' => $studentInfo ? $studentInfo->rob_55_lease : null,
            ],
        ];
    }

    private function openFutureApplicationCheck(?int $userId, string $currentYear): array
    {
        $futureYears = Lookups::uiYearLookups()
            ->pluck('lookup_name')
            ->map(function ($year) {
                return (string) (int) $year;
            })
            ->filter(function ($year) use ($currentYear) {
                return (int) $year > (int) $currentYear;
            })
            ->values();

        if ($futureYears->isEmpty()) {
            return [
                'open_future_year_found' => false,
                'message' => 'No future application year is currently open.',
            ];
        }

        $futureYear = (string) $futureYears->first();
        $futureEligibility = $this->firebaseRegistrationEligibility($userId, $futureYear);

        return [
            'open_future_year_found' => true,
            'future_year' => $futureYear,
            'future_student_info_id' => $futureEligibility['studentInfo']->id ?? null,
            'future_booking_id' => $futureEligibility['booking']->id ?? null,
            'future_application_would_be_ready' => $futureEligibility['eligible'],
            'message' => $futureEligibility['message'],
            'meta' => $futureEligibility['meta'] ?? [],
        ];
    }

    private function writeFirebaseRegistrationLog(string $status, string $stage, ?string $message, array $context = []): void
    {
        try {
            $user = $context['user'] ?? null;
            $studentInfo = $context['studentInfo'] ?? null;
            $booking = $context['booking'] ?? null;

            FirebaseRegistrationLog::create([
                'user_id' => $user->id ?? null,
                'students_info_id' => $studentInfo->id ?? null,
                'booking_id' => $booking->id ?? null,
                'year' => $context['year'] ?? null,
                'email' => $context['email'] ?? ($studentInfo->userEmail ?? ($user->email ?? null)),
                'firebase_uid' => $context['firebase_uid'] ?? null,
                'status' => $status,
                'stage' => $stage,
                'message' => $message,
                'meta' => $context['meta'] ?? null,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Firebase registration log write failed: ' . $e->getMessage());
        }
    }
}

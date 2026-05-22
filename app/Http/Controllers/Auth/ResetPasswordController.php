<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserResetToken;
use App\Services\FirebaseService;
use Illuminate\Support\Facades\Hash;

class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    /**
     * Where to redirect users after resetting their password.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;
    protected $firebase;

    public function __construct(FirebaseService $firebase)
    {
        $this->firebase = $firebase;
    }


    public function resetPasswordUser(Request $request, $userCustomId)
    {

        $userToken = UserResetToken::where('userToken', $userCustomId)->where('active', '1')->first();

        if ($userToken && !empty($userToken)) {
            $user = User::where('id', $userToken->userUid)->first();

            return view('outSession.updatePassword', ["userCustomId" => $userCustomId, "user" => $user]);
        } else {
            return "404 Error";
        }
    }

    public function updatePasswordUser(Request $request, $userCustomId)
    {

        $password = $request->password;
        $password_confirmation = $request->password_confirmation;

        if ($password === $password_confirmation) {
            $userToken = UserResetToken::where('userToken', $userCustomId)->where('active', '1')->first();

            if (!$userToken) {
                return redirect()->route('login')->with('error', 'Password reset link is invalid or has expired.');
            }

            User::where('id', $userToken->userUid)->update(['password' => Hash::make($password)]);
            UserResetToken::where('userToken', $userCustomId)->where('active', '1')->update(['active' => 0]);

            // Always load the user (system/admin users won't have studentInfo/booking).
            // Firebase password update only applies to tenant users with an active booking.
            $user = User::with(['studentInfo.booking'])->where("id", $userToken->userUid)->first();

            if (
                $user
                && $user->studentInfoForCurrentYear
                && $user->studentInfoForCurrentYear->booking
                && !empty($user->studentInfoForCurrentYear->booking->bed_code)
            ) {
                $bedCode = $user->studentInfoForCurrentYear->booking->bed_code;
                $bookingCode = $user->studentInfoForCurrentYear->booking->booking_code;
                try {
                    // If you don't store uid, fall back to getUserByEmail
                    $firebaseUser = $this->firebase->getUserByEmail($user->email);
                    $this->firebase->updatePassword($firebaseUser->uid, $password);
                } catch (\Throwable $e) {
                    logger()->error('Firebase password update failed: ' . $e->getMessage());
                }
                //return ["bed_code" => $bedCode,"booking_code" => $bookingCode];
            } else {
                if ($user) {
                    logger()->info("No booking found for user {$user->id}; skipping Firebase password update.");
                } else {
                    logger()->warning("Password updated for userUid {$userToken->userUid} but user record could not be loaded.");
                }
            }
            return redirect()->route('login');
        } else {
            return redirect()->back()->with('error', 'Passwords must match');
        }
    }
}

<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserResetToken;
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

    public function resetPasswordUser(Request $request, $userCustomId){

        $userToken = UserResetToken::where('userToken', $userCustomId)->where('active', '1')->first();

        if($userToken && !empty($userToken)){
            $user = User::where('id', $userToken->userUid)->first();

            return view('outSession.updatePassword', ["userCustomId" => $userCustomId, "user" => $user]);
        }else{
            return "404 Error";
        }
        
    }

    public function updatePasswordUser(Request $request, $userCustomId){

        $password = $request->password;
        $password_confirmation = $request->password_confirmation;

        if($password === $password_confirmation){
            $userToken = UserResetToken::where('userToken', $userCustomId)->where('active', '1')->first();

            User::where('id', $userToken->userUid)->update(['password' => Hash::make($password)]);
            UserResetToken::where('userToken', $userCustomId)->where('active', '1')->update(['active' => 0]);

            return redirect()->route('login');
        }else{
            return redirect()->back()->with('error', 'Passwords must match');   
        }
        
    }
}

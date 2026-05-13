<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use App\Models\User;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails {
        sendResetLinkEmail as protected traitSendResetLinkEmail;
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $email = strtolower(trim((string) $request->email));
        $user = User::withTrashed()
            ->where('email', $email)
            ->orWhere('userEmail', $email)
            ->first();

        if ($user && (int) ($user->userRole ?? 0) === 0 && !empty($user->banned_at)) {
            return redirect()->back()->withErrors([
                'email' => 'Your application cannot be processed currently. Please contact an administrator with your ID number for assistance.',
            ])->withInput($request->only('email'));
        }

        return $this->traitSendResetLinkEmail($request);
    }
}

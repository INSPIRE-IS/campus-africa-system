<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;

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

    use SendsPasswordResetEmails;

    public function sendResetLinkEmail(Request $request)
    {
        $request->merge([
            'email' => strtolower(trim((string) $request->email)),
        ]);

        $request->validate([
            'email' => [
                'required',
                'string',
                'max:255',
                'email:rfc',
                function ($attribute, $value, $fail) {
                    $domain = substr(strrchr($value, '@') ?: '', 1);
                    if ($domain === '' || strpos($domain, '.') === false) {
                        $fail('Please enter a complete email address.');
                    }
                },
            ],
        ]);

        $email = strtolower(trim((string) $request->email));
        $user = User::withTrashed()
            ->where('email', $email)
            ->first();

        if ($user && (int) ($user->userRole ?? 0) === 0 && !empty($user->banned_at)) {
            return redirect()->back()->withErrors([
                'email' => 'Your application cannot be processed currently. Please contact an administrator with your ID number for assistance.',
            ])->withInput($request->only('email'));
        }

        $genericStatus = 'If an account exists for this email address, you will receive a password reset email.';

        if (!$user || $user->trashed()) {
            return redirect()->back()->with('status', $genericStatus);
        }

        $storedEmailValidator = validator([
            'email' => $user->email,
        ], [
            'email' => [
                'required',
                'string',
                'max:255',
                'email:rfc',
                function ($attribute, $value, $fail) {
                    $domain = substr(strrchr($value, '@') ?: '', 1);
                    if ($domain === '' || strpos($domain, '.') === false) {
                        $fail('The stored email address is incomplete.');
                    }
                },
            ],
        ]);

        if ($storedEmailValidator->fails()) {
            $exception = new \UnexpectedValueException("Password reset blocked because users.email is invalid for user {$user->id}.");
            report($exception);

            return redirect()->back()->with('status', $genericStatus);
        }

        try {
            Password::sendResetLink(['email' => $user->email]);
        } catch (\Throwable $e) {
            report($e);
            Log::error('Password reset email failed', [
                'user_id' => $user->id,
                'exception' => get_class($e),
                'error' => $e->getMessage(),
            ]);
        }

        return redirect()->back()->with('status', $genericStatus);
    }
}

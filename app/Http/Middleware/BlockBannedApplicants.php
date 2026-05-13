<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class BlockBannedApplicants
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $u = Auth::user();
            if ((int) ($u->userRole ?? 0) === 0 && !empty($u->banned_at)) {
                Auth::logout();
                Session::flush();

                return redirect('/login')->withErrors([
                    'message' => 'Your application cannot be processed currently. Please contact an administrator with your ID number for assistance.',
                ]);
            }
        }

        return $next($request);
    }
}


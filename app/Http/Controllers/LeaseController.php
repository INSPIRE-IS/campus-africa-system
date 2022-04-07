<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserLeaseToken;
use App\Models\User;

class LeaseController extends Controller
{
    public function view_sign_lease(Request $request, $lease_link){
        
        $lease_link = UserLeaseToken::where('leaseToken', $lease_link)->where('active', '1')->first();

        if($lease_link && !empty($lease_link)){
            $user = User::where('id', $lease_link->userUid)->first();

            return view('outSession.signLease', ["userCustomId" => $lease_link, "user" => $user]);
        }else{
            return "404 Error";
        }

    }
}

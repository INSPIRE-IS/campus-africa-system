<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserLeaseToken;
use App\Models\User;

class LeaseController extends Controller
{
    public function view_sign_lease(Request $request, $lease_link_url){
        
        $lease_link = UserLeaseToken::where('leaseToken', $lease_link_url)->where('active', '1')->first();

        if($lease_link && !empty($lease_link)){
            $user = User::where('id', $lease_link->userUid)->first();

            return view('outSession.signLease', ["userCustomId" => $lease_link_url, "user" => $user]);
        }else{
            return "404 Error";
        }

    }

    public function save_signed_lease(Request $request, $lease_link){

        $lease_link = UserLeaseToken::where('leaseToken', $lease_link)->where('active', '1')->first();
        $user = User::where('id', $lease_link->userUid)->first();
        
        // get the base64 string
        $data = $request->signature_string;
        // clean any symbols to decode
        list($type, $data) = explode(';', $data);
        list(,$extension) = explode('/',$type);
        list(,$data)      = explode(',', $data);
        // decode the string to the correct format
        $data = base64_decode($data);

        // path variable for saving in the db
        // $path = 'students' . DIRECTORY_SEPARATOR;
        // 

        // generate a random name
        $new_name = $user->id . '_' .strtotime("now").'_'.substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyz', ceil(20/strlen($x)) )),1,20).'.'.$extension;

        // if (!is_dir(storage_path().'/app/public/files/'.$lease_link->userUid)) {
        //     dd("NO");
        // }else{
        //     //
        // }

        $success = file_put_contents(storage_path().'/app/public/files/'.$lease_link->userUid, $data);

        // $data->storeAs('public/files/'.$lease_link->userUid.'/', $new_name);
        // file_put_contents('/storage/app/public/files/'.$lease_link->userUid.'/', $data);

    }
}

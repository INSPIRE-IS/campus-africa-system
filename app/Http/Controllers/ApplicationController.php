<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lookups;
use App\Models\User;
use App\Models\ApplicationToken;

class ApplicationController extends Controller
{

    public function step_1(Request $request, $property, $roomType){

        $lookups = Lookups::get();

        return view('application.step_1', ["lookups" => $lookups, "property" => $property, "roomType" => $roomType]);
    }

    public function step_verify(Request $request, $applicationToken){
        $application = ApplicationToken::where('applicationToken', $applicationToken)->first();

        return view('application.verify_user', ["applicationToken" => $applicationToken]);
    }

    public function veryfiy_user(Request $request){
        $lookups = Lookups::get();

        $application = ApplicationToken::where('applicationToken', $request->applicationToken)->first();

        if($request->security_code == $application->security_token){
            ApplicationToken::where('applicationToken', $request->applicationToken)->update(['verified' => 1]);
            $user = User::where('id', $application->userUid)->first();
            
            return ["success", "/step-2/online-application/".$request->applicationToken];
        }else{
            return ["failed"];
        }
    }

    public function email_check_idNo(Request $request){
        $email = $request->email;
        $idNumber = $request->idNumber;

        $emailValid = true;
        $idNumberValid = true;

        if($email !== ""){
            $emailCheck = User::where('email', $email)->first();

            if(!empty($emailCheck)){
                $emailValid = false;
            }
        }

        if($idNumber !== ""){
            $idCheck = User::where('userId', $idNumber)->first();

            if(!empty($idCheck)){
                $idNumberValid = false;
            }
        }

        return ["done", $emailValid, $idNumberValid];
    }

    public function step_2(Request $request, $applicationToken){
        $lookups = Lookups::get();

        $application = ApplicationToken::where('applicationToken', $applicationToken)->first();
        $user = User::where('id', $application->userUid)->first();

        if($user->studentType == "Cash / Direct")
        {
            return view('application.step_2_spon', ["lookups" => $lookups, "user" => $user, "applicationToken" => $applicationToken]);
        }
        elseif($user->studentType == "Bursary")
        {
            return view('application.step_2_bur', ["lookups" => $lookups, "user" => $user, "applicationToken" => $applicationToken]);
        }
        else
        {
            return view('application.step_3', ["lookups" => $lookups, "user" => $user, "applicationToken" => $applicationToken]);
        } 
    }

    public function step_2_spon(Request $request, $applicationToken){
        
        $application = ApplicationToken::where('applicationToken', $applicationToken)->first();
        $user = User::where('id', $application->userUid)->first();

        if($request->app_address_p == 1){
            $request->pAddress1 = $user->userAddress1;
            $request->pAddress2 = $user->userAddress2;
            $request->pAddressCity = $user->userAddressCity;
            $request->pAddressPostal = $user->userAddressPostal;
        }

        User::where('id', $user->id)->update([
            'sponFirstName' => $request->pFirstName,
            'sponLastName' => $request->pLastName,
            'sponCell' => $request->pCell_no,
            'sponEmail' => $request->pEmail,
            'pNationality' => $request->pNationality,
            'sponId' => $request->pIdNumber,
            'pPassportNo' => $request->pPassportNo,
            'pAddress1' => $request->pAddress1,
            'pAddress2' => $request->pAddress2,
            'pAddressCity' => $request->pAddressCity,
            'pAddressPostal' => $request->pAddressPostal,
            'app_address_p' => $request->app_address_p,
        ]);

        return "/step-3/online-application/".$application->applicationToken;
    }

    public function step_3(Request $request, $applicationToken){
        $lookups = Lookups::get();

        $application = ApplicationToken::where('applicationToken', $applicationToken)->first();
        $user = User::where('id', $application->userUid)->first();

        return view('application.step_3', ["lookups" => $lookups, "user" => $user, "applicationToken" => $applicationToken]);
    }
}

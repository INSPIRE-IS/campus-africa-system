<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\Models\User;
use App\Models\ActionList;
use App\models\Lookups;

class ProfileController extends Controller
{
    public function index(Request $request){

        $user = User::where('id', Auth::user()->id)->first();
        $lookups = User::get();

        if(Auth::user()->userRole == "0"){
            return view('user.profile', ["headerTitle" => "My Profile", "user" => $user, "lookups" => $lookups]);
        }else{
            return view('admin.profile', ["headerTitle" => "My Profile", "user" => $user, "lookups" => $lookups]);
        }
    }

    public function update(Request $request){
        
        User::where('id', $request->userUid)->update([
            'userFirstName' => $request->firstName,
            'userLastname' => $request->lastName,
            'userTitle' => $request->title,
            'userDob' => $request->dob,
            'userCell' => $request->cell_no,
            'userEmail' => $request->email,
            'userId' => $request->idNumber,
            'userNationality' => $request->nationality,
            'userPassport' => $request->passportNo,
            'institutionStudy' => $request->institStudy,
            'studentNo' => $request->studNumber,
            // 'institutionCampus' => $request->institCampus,
            'faculty' => $request->faculty,
            'year' => $request->year,
            'sponFirstName' => $request->pFirstName,
            'sponLastName' => $request->pLastName,
            'sponCell' => $request->pCell_no,
            'sponEmail' => $request->pEmail,
            'sponId' => $request->pIdNumber,
            'burCompanyName' => $request->bCompanyName,
            'burRegNo' => $request->bRegistrationNo,
            'burContactPerson' => $request->bContactPerson,
            'burContactNo' => $request->bContactNumber,
            'burEmail' => $request->bEmail,
            'kinFirstName' => $request->kFirstName,
            'kinLastName' => $request->kLastName,
            'kinTitle' => $request->kTitle,
            'kinId' => $request->kIdNumber,
            'kinAddressStr' => $request->kAddress1,
            'kinAddressSuburb' => $request->kAddress2,
            'kinAddressCity' => $request->kAddressCity,
            'kinAddressPost' => $request->kAddressPostal,
            'kinRelationship' => $request->kRelationship,
            'kinTelNo' => $request->kTeleNo,
            'kinCellNo' => $request->kCellNo,
            'kinWorkNo' => $request->kWorkNo,
            'kinEmail' => $request->kEmail,
        ]);

        return redirect()->route('my-profile');
    }
}

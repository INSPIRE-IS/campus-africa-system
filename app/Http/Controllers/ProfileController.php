<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\ActionList;
use App\Models\Lookups;
use App\Models\QuestionOption;
use App\Models\StudentQuestionAnswer;
use Illuminate\Support\Facades\Session;

class ProfileController extends Controller
{
    public function index(Request $request)
    {

        $user = User::where('id', Auth::user()->id)->first();
        $lookups = User::get();

        if (Auth::user()->userRole == "0") {
            return view('user.profile', ["headerTitle" => "My Profile", "user" => $user, "lookups" => $lookups]);
        } else {
            return view('admin.profile', ["headerTitle" => "My Profile", "user" => $user, "lookups" => $lookups]);
        }
    }

    public function update(Request $request)
    {

        User::where('id', $request->userUid)->update([
            'userFirstName' => ucwords(strtolower($request->firstName)),
            'userLastname' => ucwords(strtolower($request->lastName)),
            'userTitle' => $request->title,
            'userDob' => $request->dob,
            'userCell' => $request->cell_no,
            'userEmail' => $request->email,
            'email' => $request->email,
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


    public function update_questionnaire(Request $request)
    {

        // User::where('id', $request->userUid)->update([
        //     'userFirstName' => ucwords(strtolower($request->firstName)),
        //     'userLastname' => ucwords(strtolower($request->lastName))]);
        $validated = $request->validate([
            'userUid' => 'required|integer',
            // 'year' => 'required|integer',
            'answers' => 'required|array',
        ]);

        if (!$validated) {
            return false;
        }
        // dd($request->answers);
        $this_year = Lookups::where('group_id', '4')->first();
        if (Session::has('year')) {
            $year = Session::get('year');
            $this_year = Lookups::where('group_id', '4')->where('lookup_name', '=', $year)->first();
        }
        $this_year = $this_year->lookup_name;

        $questionOptions = QuestionOption::all();
        $answers = $request->answers;

        foreach ($answers as $questionId => $optionId) {
            $option = $questionOptions->firstWhere('id', $optionId);
            StudentQuestionAnswer::updateOrCreate(
                [
                    'student_id' => $request->userUid,
                    'question_id' => $questionId,
                    'academic_year' => $this_year,
                ],
                [
                    'question_option_id' => $optionId,
                    'numeric_value' => $option ? $option->option_value : null, // optional if you want numeric comparison
                ]
            );
        }
        $info = User::where('id', $request->userUid)->first();

        ActionList::create([
            "userUid" => $request->userUid,
            "action" => "Questionnaire Updated",
            "actionUserUid" => $info->id,
            "actionUserDisplayName" => $info->name,
            "actionUserRole" => $info->userRole,
            "year" => $this_year,
        ]);

        return 'true';
    }
}

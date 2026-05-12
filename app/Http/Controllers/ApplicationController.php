<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lookups;
use App\Models\User;
use App\Models\ApplicationToken;
use App\Models\UserFiles;
use App\Models\ActionList;
use App\Models\Booking;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Jenssegers\Agent\Agent;
use Grosv\LaravelPasswordlessLogin\LoginUrl;
use App\Models\RoomTypes;
use App\Models\Residence;
use App\Models\MailerList;
use Illuminate\Support\Facades\Mail;
use App\Models\Property;
use App\Models\OnlinePayments;
use App\Models\StudentsInfo;
use App\Models\ImportNSFASStudentAuditLog;
use App\Models\ImportNSFASApproval;
use App\Models\BookingPayment;
use DateTime;
use App\Models\InstitutionProperties;
use App\Jobs\SendNewBookingMail;
use App\Jobs\SendStudentVerifyMailByAdminMail;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\StudentQuestionAnswer;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class ApplicationController extends Controller
{

    public function step_1(Request $request, $propertyID, $roomType)
    {

        $onlineAppOpen = Lookups::where('group_id', '9')->first();

        if ($onlineAppOpen->lookup_name == '1') {
            //
            $lookups = Lookups::get();
            $agent = new Agent();
            $residences = Residence::whereNull('status')->get();
            $this_year = Lookups::where('group_id', '4')->first();
            $this_year = $this_year->lookup_name;
            $questions = Question::with('options')->get();
            //dd($this_year);
            $fundingTypes = Lookups::FUNDING_TYPES;

            return view('application.step_1', ["lookups" => $lookups, "questions" => $questions, "propertyID" => $propertyID, "roomType" => $roomType, "agent" => $agent, "residences" => $residences, "application_year" => $this_year, "fundingTypes" => $fundingTypes]);
        } else {
            return view('errors.onlineAppOffline');
        }
    }

    public function get_room_maps(Request $request)
    {
        //Get residences from WP
        $jsonDataResidence = json_decode(file_get_contents('https://www.campusafrica.co.za/wp-json/wp/v2/residences'));
        $residenceNameWP = "";
        $roomID = "";

        foreach ($jsonDataResidence as $residence) {
            if ($residence->id == $request->residence) {
                //get residence Name from residence (WP)
                $residenceNameWP = $residence->title->rendered;
            }
        }

        $residence = Residence::where('residence', $residenceNameWP)->first();
        $roomMaps = RoomTypes::where('property_id', $residence->id)->get();

        foreach ($roomMaps as $room) {
            if ($room->room_map_slug == $request->roomType) {
                $roomID = $room->id;
            }
        }

        return [$residence->id, $roomID];
    }

    public function step_verify(Request $request, $applicationToken)
    {

        return view('application.verify_user', ["applicationToken" => $applicationToken]);
    }

    public function veryfiy_user(Request $request)
    {
        $lookups = Lookups::get();

        $application = ApplicationToken::where('applicationToken', $request->applicationToken)->first();

        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;



        if ($request->security_code == $application->security_token) {
            ApplicationToken::where('applicationToken', $request->applicationToken)->update(['verified' => 1]);
            // User::where('id', $application->userUid)->update(['email_verified' => 1]);
            StudentsInfo::where('userUid', $application->userUid)->where('year', $this_year)->update(['email_verified' => 1]);
            $user = User::where('id', $application->userUid)->first();

            $generator = new LoginUrl($user);
            $generator->setRedirectUrl('/verify-password');
            $url = $generator->generate();

            return ["success", $url];
        } else {
            return ["failed"];
        }
    }

    public function email_check_idNo(Request $request)
    {
        $email = $request->email;
        $idNumber = $request->idNumber;

        $emailValid = true;
        $idNumberValid = true;

        if ($email !== "") {
            $emailCheck = User::where('email', $email)->first();

            if (!empty($emailCheck)) {
                $emailValid = false;
            }
        }

        if ($idNumber !== "") {
            $idCheck = User::where('userId', $idNumber)->first();

            if (!empty($idCheck)) {
                $idNumberValid = false;
            }
        }

        return ["done", $emailValid, $idNumberValid];
    }

    public function passport_check_registration(Request $request)
    {
        $passportNo = $request->passportNo;
        $passportNoValid = true;
        $property = null;

        if ($passportNo !== "") {
            $user = $idCheck = StudentsInfo::where('userPassport', $passportNo)->orderby('year', 'desc')->first();

            if (!empty($idCheck)) {
                $passportNoValid = false;
                $booking = Booking::where('id', $idCheck->bookingId)->orderBy('year', 'desc')->first();
                $property = Residence::where('id', $booking->property)->pluck('residence')->first();
            }
        }

        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        $booking = (object)[];

        if (!empty($user)) {
            $booking = Booking::where('userId', $user->userUid)->orderBy('year', 'desc')->first();
        }

        return ["done", $passportNoValid, $user, $property, $booking, $this_year];
    }

    public function step_2(Request $request)
    {
        $lookups = Lookups::get();

        // $application = ApplicationToken::where('applicationToken', $applicationToken)->first();
        $user = User::where('id', Auth::user()->id)->first();

        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        $userInfo = StudentsInfo::where('userUid', Auth::user()->id)->where('year', $this_year)->first();


        // if(!empty($user)){
        //     ApplicationToken::where('applicationToken', $applicationToken)->update(['verified' => 1]);
        // }

        $agent = new Agent();

        if ($userInfo->studentType == "Cash / Direct") {
            return view('application.step_2_spon', ["lookups" => $lookups, "user" => $userInfo, "agent" => $agent, "new" => 0, "userNew" => $userInfo]);
        } elseif ($userInfo->studentType == "Bursary") {
            return view('application.step_2_bur', ["lookups" => $lookups, "user" => $userInfo, "agent" => $agent, "new" => 0, "userNew" => $userInfo]);
        } else {
            return view('application.step_3', ["lookups" => $lookups, "user" => $userInfo, "agent" => $agent, "new" => 0, "userNew" => $userInfo]);
        }
    }

    public function step_2_spon(Request $request)
    {

        // $application = ApplicationToken::where('applicationToken', $applicationToken)->first();
        $user = User::where('id', Auth::user()->id)->first();

        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        $userInfo = StudentsInfo::where('userUid', Auth::user()->id)->where('year', $this_year)->first();

        if ($request->app_address_p == 1) {
            $request->pAddress1 = $userInfo->userAddress1;
            $request->pAddress2 = $userInfo->userAddress2;
            $request->pAddressCity = $userInfo->userAddressCity;
            $request->pAddressPostal = $userInfo->userAddressPostal;
        }

        StudentsInfo::where('userUid', $user->id)->where('year', $this_year)->update([
            'sponFirstName' => $request->pFirstName,
            'sponLastName' => $request->pLastName,
            'sponCell' => $request->pCell_no,
            'sponCellCode' => $request->sponCellCode,
            'sponEmail' => $request->pEmail,
            'pNationality' => $request->pNationality,
            'sponId' => $request->pIdNumber,
            'pPassportNo' => $request->pPassportNo,
            'pAddress1' => $request->pAddress1,
            'pAddress2' => $request->pAddress2,
            'pAddressCity' => $request->pAddressCity,
            'pAddressPostal' => $request->pAddressPostal,
            'app_address_p' => $request->app_address_p,
            'c_credit_check' => $request->c_credit_check,
            'c_credit_risk' => $request->c_credit_risk,
        ]);

        return "/step-3/online-application";
    }

    public function step_2_bur(Request $request)
    {

        // $application = ApplicationToken::where('applicationToken', $applicationToken)->first();
        $user = User::where('id', Auth::user()->id)->first();

        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        StudentsInfo::where('userUid', $user->id)->where('year', $this_year)->update([
            'burCompanyName' => $request->bCompanyName,
            'burRegNo' => $request->bRegistrationNo,
            'burContactPerson' => $request->bContactPerson,
            'burContactNo' => $request->bContactNumber,
            'burContactCode' => $request->burContactCode,
            'burEmail' => $request->bEmail,
            'bAddress1' => $request->bAddress1,
            'bAddress2' => $request->bAddress2,
            'bAddressCity' => $request->bAddressCity,
            'bAddressPostal' => $request->bAddressPostal,
            'bPayment' => $request->bPayment,
            'bint' => $request->bint,
            'bIntName' => $request->bIntName,
        ]);

        return "/step-3/online-application";
    }

    public function step_3(Request $request)
    {
        $lookups = Lookups::get();

        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;
        /** @disregard P1009 */
        if (!Auth::check()) {
            return redirect()->route('login');
        }
        /** @disregard P1009 */
        $user = StudentsInfo::where('userUid', Auth::user()->id)->where('year', $this_year)->first();

        // $application = ApplicationToken::where('applicationToken', $applicationToken)->first();
        // $user = User::where('id', Auth::user()->id)->first();

        return view('application.step_3', ["lookups" => $lookups, "user" => $user, "userNew" => $user, "new" => 0]);
    }

    public function step_3_kin(Request $request)
    {
        // $application = ApplicationToken::where('applicationToken', $applicationToken)->first();
        $user = User::where('id', Auth::user()->id)->first();

        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        $userInfo = StudentsInfo::where('userUid', Auth::user()->id)->where('year', $this_year)->first();

        if ($request->app_parent_kin == 1) {
            $request->kFirstName = $userInfo->sponFirstName;
            $request->kLastName = $userInfo->sponLastName;
            $request->kCellNo = $userInfo->sponCell;
            $request->kinCellCode = $userInfo->sponCellCode;
            $request->kEmail = $userInfo->sponEmail;
            $request->kAddress1 = $userInfo->pAddress1;
            $request->kAddress2 = $userInfo->pAddress2;
            $request->kAddressCity = $userInfo->pAddressCity;
            $request->kAddressPostal = $userInfo->pAddressPostal;
        }

        if ($request->app_address == 1) {
            $request->kAddress1 = $userInfo->userAddress1;
            $request->kAddress2 = $userInfo->userAddress2;
            $request->kAddressCity = $userInfo->userAddressCity;
            $request->kAddressPostal = $userInfo->userAddressPostal;
        }

        StudentsInfo::where('userUid', $user->id)->where('year', $this_year)->update([
            'app_parent_kin' => $request->app_parent_kin,
            'kinFirstName' => $request->kFirstName,
            'kinLastName' => $request->kLastName,
            'kinTitle' => $request->kTitle,
            'kinRelationship' => $request->kRelationship,
            'kinCellNo' => $request->kCellNo,
            'kinCellCode' => $request->kinCellCode,
            'kinEmail' => $request->kEmail,
            'kinWorkNo' => $request->kWorkNo,
            'kinWorkCode' => $request->kinWorkCode,
            'kinTelNo' => $request->kTeleNo,
            'kinTelCode' => $request->kinTelCode,
            'app_address' => $request->app_address,
            'kinAddressStr' => $request->kAddress1,
            'kinAddressSuburb' => $request->kAddress2,
            'kinAddressCity' => $request->kAddressCity,
            'kinAddressPost' => $request->kAddressPostal,
            'rob_35_info' => 1,
        ]);

        return "/step-4/online-application";
    }

    public function step_4(Request $request)
    {
        $lookups = Lookups::get();

        // determine target year
        $year = Session::get('year') ?? Lookups::where('group_id', 4)->first()->lookup_name;
        // load current user

        /** @disregard P1009 */
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        /** @disregard P1009 */
        $userId = Auth::user()->id;

        // search booking for selected year
        $booking = Booking::where('userId', $userId)
            ->where('year', $year)
            ->first();

        // if not found, try previous year
        if (!$booking) {
            $year = $year - 1;

            $booking = Booking::where('userId', $userId)
                ->where('year', $year)
                ->first();

            // if found, switch session year and continue
            if ($booking) {
                Session::put('year', $year);
            }
        }

        // if still null → logout
        if (!$booking) {
            /** @disregard P1009 */
            Auth::logout();
            Session::flush();

            return redirect()->route('login')
                ->withErrors(['message' => 'No valid booking found.']);
        }


        // $application = ApplicationToken::where('applicationToken', $applicationToken)->first();
        $user = User::where('id', $userId)->first();
        $userInfo = StudentsInfo::where('userUid', $userId)->where('year', $year)->first();

        $IdCopy = UserFiles::where("userUid", $userId)->where('fileId', 'copyID')->where('year', $year)->first();
        $StudentCopy = UserFiles::where("userUid", $userId)->where('fileId', 'studentCard')->where('year', $year)->first();
        $BankCopy = UserFiles::where("userUid", $userId)->where('fileId', 'bankStatement')->where('year', $year)->first();
        // new logic for cash students require sponsorId and sponsorPayslip
        $SponsorId = UserFiles::where("userUid", $userId)->where('fileId', 'other_sponsor_id')->where('year', $year)->first();
        $SponsorPayslip = UserFiles::where("userUid", $userId)->where('fileId', 'other_sponsor_payslip')->where('year', $year)->first();
        if ($userInfo->studentType == "Cash / Direct" &&  $year >= '2026') {
            $existingSponsorId = UserFiles::withTrashed()
                ->where('userUid', $userInfo->userUid)
                ->where('fileId', 'other_sponsor_id')
                ->where('year', $year)
                ->first();
            if (!$existingSponsorId) {
                UserFiles::create([
                    'userUid' => $userInfo->userUid,
                    'fileId' => 'other_sponsor_id',
                    'year' => $year,
                ]);
            }

            $existingSponsorPayslip = UserFiles::withTrashed()
                ->where('userUid', $userInfo->userUid)
                ->where('fileId', 'other_sponsor_payslip')
                ->where('year', $year)
                ->first();
            if (!$existingSponsorPayslip) {
                UserFiles::create([
                    'userUid' => $userInfo->userUid,
                    'fileId' => 'other_sponsor_payslip',
                    'year' => $year,
                ]);
            }
        }
        $BursaryCopy = UserFiles::where("userUid", $userId)->where('fileId', 'bursaryApproval')->where('year', $year)->first();
        $RegistrationCopy = UserFiles::where("userUid", $userId)->where('fileId', 'proofRegistration')->where('year', $year)->first();

        return view('application.step_4', ["lookups" => $lookups, "user" => $userInfo, "IdCopy" => $IdCopy, "StudentCopy" => $StudentCopy, "BankCopy" => $BankCopy, "SponsorId" => $SponsorId, "SponsorPayslip" => $SponsorPayslip, "BursaryCopy" => $BursaryCopy, "RegistrationCopy" => $RegistrationCopy]);
    }

    function get_user_status($fileType, $user, $student)
    {

        if ($fileType == "proof_of_payment") {
            $typeName = "Booking Fee Proof of Payment";
            $field_data = "fee_approved";
            $field_doc = "rob_10_fee";
        } elseif ($fileType == "copyID") {
            $typeName = "Copy of ID Document";
            $field_data = "id_data_approved";
            $field_doc = "rob_15_id";
        } elseif ($fileType == "studentCard") {
            $typeName = "Copy of Student Card";
            $field_data = "student_card_approved";
            $field_doc = "rob_20_card";
        } elseif ($fileType == "proofRegistration") {
            $typeName = "Proof of Registration";
            $field_data = "registration_approved";
            $field_doc = "rob_25_reg";
        } elseif ($fileType == "bursaryApproval") {
            if ($student->studentType == "NSFAS") {
                $typeName = "NSFAS Approval";
            } else {
                $typeName = "Bursary Approval";
            }
            $field_data = "bursary_approved";
            $field_doc = "rob_30_funds";
        } elseif ($fileType == "bankStatement") {
            $typeName = "Bank Statements";
            $field_data = "bank_approved";
            $field_doc = "rob_30_funds";
        } elseif ($fileType == "moveIn") {
            $typeName = "Lease";
            $field_data = "";
            $field_doc = "rob_55_lease";
        } else {
            $typeName = strtoupper(str_replace("_", " ", str_replace("other", "other |", $fileType)));
            $field_data = $fileType;
            $field_doc = "rob_45_other";
        }

        $data_approved = 0;
        $doc_approved = 0;

        if (!empty($user)) {
            if (!empty($field_data) && isset($user->{$field_data})) {
                $data_approved = $user->{$field_data};
            }

            if (!empty($field_doc) && isset($user->{$field_doc})) {
                $doc_approved = $user->{$field_doc};
            }
        }

        return [
            'typeName' => $typeName,

            'field_data' => $field_data,
            'field_doc' => $field_doc,

            'data_approved' => $data_approved,
            'doc_approved' => $doc_approved
        ];
    }

    public function update_user_status($user_id)
    {

        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        $userInfo = StudentsInfo::where('userUid',  $user_id)->where('year', $this_year)->first();
        $userFiles = UserFiles::where('userUid',  $user_id)->where('year', $this_year)->get();

        $user = User::with('files')->where('id', $user_id)->first();

        if ($userInfo->overall_status !== "Cancelled" && $userInfo->overall_status !== "Rejected" && $userInfo->applicationStatus !== "Approved Confirmed") {

            $booking = "Rejected";
            $files_for_booking = [];

            //files for booking status === Proof of payment - ID Copy
            foreach ($userFiles as $file) {
                if ($file->fileId == "proof_of_payment" || $file->fileId == "copyID") {
                    array_push($files_for_booking, $file->status);
                }
            }

            //Default application files that all students have
            $file_types = ["studentCard", "proofRegistration"];

            //Add in additional files based on student type.
            if ($userInfo->studentType == "Cash / Direct") {
                array_push($file_types, "bankStatement");
            } elseif ($userInfo->studentType == "NSFAS" || $userInfo->studentType == "Bursary") {
                array_push($file_types, "bursaryApproval");
            }

            $application = "Rejected";
            $files_for_application = [];

            //files for application status
            foreach ($userInfo->files as $file) {
                if (in_array($file->fileId, $file_types)) {
                    array_push($files_for_application, $file->status);
                }
            }

            //lease
            $lease = "na";

            foreach ($userInfo->files as $file) {
                if ($file->fileId == "moveIn") {
                    if ($file->status == "Approved") {
                        $lease = "Approved";
                    } else if ($file->status == "Uploaded") {
                        $lease = "Action";
                    } else {
                        $lease = "Rejected";
                    }
                }
            }

            //Check all status in booking to get final booking status
            if (count($files_for_booking) == "2" && in_array("Approved", $files_for_booking)) {
                $booking = "Approved";
            }
            if (in_array("Rejected", $files_for_booking)) {
                $booking = "Rejected";
            }
            if (in_array("Uploaded", $files_for_booking)) {
                $booking = "Action";
            }
            if ($lease !== "na") {
                $booking = "Approved Confirmed";
            }

            //Check all status in application to get final booking status
            if (count($files_for_application) == "3" && in_array("Approved", $files_for_application)) {
                $application = "Approved";
            }
            if (in_array("Rejected", $files_for_application)) {
                $application = "Rejected";
            }
            if (in_array("Uploaded", $files_for_application)) {
                $application = "Action";
            }
            if ($lease !== "na") {
                $application = "Approved Confirmed";
            }

            //overall status
            $overall = "Incomplete";

            if ($booking == "Action" || $application == "Action" || $lease == "Action") {
                $overall = "Pending";
            }

            if ($booking == "Approved" && $application == "Approved") {
                $overall = "Pending";
            }

            if ($booking == "Approved Confirmed" && $application == "Approved Confirmed" && $lease == "Approved") {
                $overall = "Approved";
            }

            // User::where('id', $user_id)->update(['bookingStatus' => $booking, 'applicationStatus' => $application, 'overall_status' => $overall]);
            StudentsInfo::where('userUid',  $user_id)->where('year', $this_year)->update(['bookingStatus' => $booking, 'applicationStatus' => $application, 'overall_status' => $overall]);
        }

        if ($userInfo->applicationStatus == "Approved Confirmed") {
            //lease
            $lease = "na";

            foreach ($userFiles as $file) {
                if ($file->fileId == "moveIn") {
                    if ($file->status == "Approved") {
                        $lease = "Approved";
                    } else if ($file->status == "Uploaded") {
                        $lease = "Action";
                    } else {
                        $lease = "Rejected";
                    }
                }
            }

            if ($lease == "na" || $lease == "Rejected") {
                $overall = "Incomplete";
            }

            if ($lease == "Action") {
                $overall = "Pending";
            }

            if ($lease == "Approved") {
                $overall = "Approved";
            }

            // User::where('id', $user_id)->update(['overall_status' => $overall]);
            StudentsInfo::where('userUid',  $user_id)->where('year', $this_year)->update(['overall_status' => $overall]);
        }
    }

    public function file_upload(Request $request)
    {

        $userUid = $request->userUid;
        $currUser = User::where('id', $userUid)->first();
        $fileType = $request->fileType;

        $fileUrlBack = "";

        if ($request->hasFile('file')) {

            if ($request->file('file')->isValid()) {

                $this_year = Lookups::where('group_id', '4')->first();
                $this_year = $this_year->lookup_name;

                $userInfo = StudentsInfo::where('userUid', $userUid)->where('year', $this_year)->first();

                $filenameWithExt  = $request->file('file')->getClientOriginalName();
                $fileName = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                $extension = $request->file('file')->extension();
                $fileNameToStore = $fileName . '_' . time() . '.' . $extension;

                $request->file('file')->storeAs('public/files/' . $userUid . '/', $fileNameToStore);

                $fileUrl = 'files/' . $userUid . '/' . $fileNameToStore;
                $fileUrlBack = $fileUrl;

                $hasFile = UserFiles::where('userUid', $userUid)->where('fileId', $fileType)->where('year', $this_year)->first();
                //Log::info('hasFile ' . $hasFile);
                $user_status = $this->get_user_status($fileType, "", StudentsInfo::where('userUid', $userUid)->where('year', $this_year)->first());
                //Log::info('user_status ', $user_status);
                $field_data = $user_status['field_data'];
                $field_doc = $user_status['field_doc'];

                $docExists = UserFiles::where("userUid", $userUid)->where("fileId", $fileType)->where("year", $this_year)->first();

                if (!empty($docExists)) {
                    UserFiles::where("userUid", $userUid)->where("fileId", $fileType)->where("year", $this_year)->update([
                        'fileId' => $fileType,
                        'userUid' => $userUid,
                        'fileName' => $fileNameToStore,
                        'fileUrl' => $fileUrl,
                        'status' => "Uploaded",
                        'uploaded_by' => $userInfo->userFirstName,
                        'year' => $this_year,
                    ]);
                } else {
                    UserFiles::create([
                        'fileId' => $fileType,
                        'userUid' => $userUid,
                        'fileName' => $fileNameToStore,
                        'fileUrl' => $fileUrl,
                        'status' => "Uploaded",
                        'uploaded_by' => $userInfo->userFirstName,
                        'year' => $this_year,
                    ]);
                }



                $text = "Uploaded " . $user_status['typeName'] . " for review";

                // $cur_status = User::find($userUid);
                $cur_status = StudentsInfo::where('userUid', $userUid)->where('year', $this_year)->first();

                //Update data status back to pending
                if (!empty($field_data)) {
                    //If the data was already approved then unapprove
                    if ($cur_status->{$field_data} == 1) {
                        // User::where('id', $userUid)->update([$field_data => '0']);     
                        StudentsInfo::where('userUid', $userUid)->where('year', $this_year)->update([$field_data => '0']);
                    }
                }

                //Update Robot status back to pending
                if (!empty($field_doc)) {
                    // User::where('id', $userUid)->update([$field_doc => '1']);
                    StudentsInfo::where('userUid', $userUid)->where('year', $this_year)->update([$field_doc => '1']);
                }

                //Get the file
                $file = UserFiles::where('userUid', $userUid)->where('fileId', $fileType)->where('year', $this_year)->first();

                ActionList::create([
                    "userUid" => $userUid,
                    "action" => $text,
                    "actionUserUid" => $userUid,
                    "actionUserDisplayName" => $userInfo->userFirstName,
                    "actionUserRole" => 0,
                    "year" => $this_year,
                ]);

                Booking::where('userId', $userUid)->where('year', $this_year)->update(['payment_type' => 'Manual EFT']);

                $this->update_user_status($userUid);
                /** @disregard P1009 */
                return [$file->id, StudentsInfo::where('userUid', Auth::user()->id)->where('year', $this_year)->first(), $file];
            } else {
                return ["error", "File Not Valid"];
            }
        } else {
            return ["error", "No File Detected"];
        }
    }

    public function step_5(Request $request)
    {
        /** @disregard P1009 */
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $jsonDataResidence = json_decode(file_get_contents('https://www.campusafrica.co.za/wp-json/wp/v2/residences'));
        $jsonDataRooms = json_decode(file_get_contents('https://www.campusafrica.co.za/wp-json/wp/v2/rooms?per_page=100'));

        try {
            $lookups = Lookups::get();

            $this_year = Lookups::where('group_id', '4')->first();
            $this_year = $this_year->lookup_name;

            /** @disregard P1009 */
            $userId = Auth::user()->id;

            // $application = ApplicationToken::where('applicationToken', $applicationToken)->first();
            $user = User::where('id',  $userId)->first();
            $userInfo = StudentsInfo::where('userUid',  $userId)->where('year', $this_year)->first();
            $booking = null;

            if ($userInfo && $userInfo->bookingId) {
                $booking = Booking::where('id', $userInfo->bookingId)
                    ->where('userId', $userId)
                    ->where('year', $this_year)
                    ->first();
            }

            if (!$booking && $userId && $this_year) {
                $booking = Booking::where('userId', $userId)
                    ->where('year', $this_year)
                    ->first();
            }
            if (!$booking) {
                /** @disregard P1009 */
                Auth::logout();
                Session::flush();

                return redirect()->route('login')
                    ->withErrors(['message' => 'No valid booking found. Please log in again or contact support.']);
            }

            if ($booking->cancelled == 1) {
                /** @disregard P1009 */
                Auth::logout();
                Session::flush();

                return redirect()->route('login')
                    ->withErrors(['message' => 'Your booking is cancelled. Please contact support.']);
            }

            DB::beginTransaction();

            // $residences = Property::where('id', $booking->property)
            $proofPayment = UserFiles::where("userUid",  $userId)->where('fileId', 'proof_of_payment')->where('year', $this_year)->first();
            $agent = new Agent();


            $lookup = Lookups::where('group_id', '7')->first();
            $room_rate = 0;
            $actionFileData['action'] = "";

            $roomTypeInfo = RoomTypes::where('property_id', $booking->property)->where('id', $booking->roomType)->first();
            $propertyTypeInfo = Residence::where('id', $booking->property)->first();

            $allResidences = Residence::whereNull('status')->get();
            $allRoomTypes = RoomTypes::get();

            $property = Residence::where('id', $booking->property)->pluck('residence')->first();
            $roomTypeNew = RoomTypes::where('id', $booking->roomType)->pluck('name')->first();

            $roomTypeData = RoomTypes::where('id', $booking->roomType)->first();

            //TODO: temp set booking fee to 0 for sharing rooms
            $isSharingRoom = false;

            if ($roomTypeData && str_contains(strtolower($roomTypeData->name), 'sharing')) {
                $isSharingRoom = true;
            }
            $roomTypeId = $roomTypeData->room_map_slug;

            $bookingRoomtype = $roomTypeInfo->name;
            $booking->property = $propertyTypeInfo->residence;

            if ($lookup && $lookup->lookup_name == "1") {
                $room_rate = 0;
                $actionFileData['action'] = "Booking Fee Waived";
            }


            $residenceId = "";

            foreach ($jsonDataResidence as $residence) {
                if ($residence->title->rendered == $booking->property) {
                    //get room booking fee from residence (WP)
                    if ($actionFileData['action'] == "") {
                        $room_rate = $residence->acf->booking_fee;
                    }
                    //get residence ID (WP)
                    $residenceId = $residence->id;
                }
            }

            $roomType = "";
            $roomName = "";
            $priced_from = 0;
            $chair = "false"; //436
            $wifi = "false"; //405
            $bath = "false"; //468
            $kitchen = "false"; //413
            $beds = 0;

            foreach ($jsonDataRooms as $room) {
                if ($residenceId == $room->acf->residence) {


                    if ($room->id == $roomTypeId) {
                        $attributes = $room->acf->attributes;


                        foreach ($attributes as $attr) {
                            if ($attr == "436") {
                                $chair = "true";
                            }
                            if ($attr == "405") {
                                $wifi = "true";
                            }
                            if ($attr == "413") {
                                $kitchen = "true";
                            }
                            if ($attr == "468") {
                                $bath = "true";
                            }
                        }

                        $roomType = $roomTypeNew;
                        $roomName = $room->slug;
                        $priced_from = $room->acf->price_from;
                        $beds = $room->acf->beds;

                        $appliesTo = $room->acf->booking_fee_applies_to;

                        if ($room->acf->booking_fee_override !== "") {
                            $room_rate = $room->acf->booking_fee_override;
                        }

                        if ($userInfo->studentType == "NSFAS") {
                            if (!in_array("NSFAS Students", $appliesTo)) {
                                $room_rate = "0";
                                $actionFileData['action'] = "Booking Fee is R0";
                            }
                        }

                        if ($userInfo->studentType == "Bursary") {
                            if (!in_array("Bursary Students", $appliesTo)) {
                                $room_rate = "0";
                                $actionFileData['action'] = "Booking Fee is R0";
                            }
                        }

                        if ($userInfo->studentType == "Cash / Direct") {
                            if (!in_array("Cash Students", $appliesTo)) {
                                $room_rate = "0";
                                $actionFileData['action'] = "Booking Fee is R0";
                            }
                        }
                    }
                }
            }

            if ($roomTypeData->pull_price == null) {
                $priced_from = $roomTypeData->rent_price;
                $room_rate = $roomTypeData->booking_price;

                if ($userInfo->studentType == "NSFAS") {
                    if ($roomTypeData->apply_nsfas == null) {
                        $room_rate = "0";
                        $actionFileData['action'] = "Booking Fee is R0";
                    }
                }

                if ($userInfo->studentType == "Bursary") {
                    if ($roomTypeData->apply_bursary == null) {
                        $room_rate = "0";
                        $actionFileData['action'] = "Booking Fee is R0";
                    }
                }

                if ($userInfo->studentType == "Cash / Direct") {
                    if ($roomTypeData->apply_cash == null) {
                        $room_rate = "0";
                        $actionFileData['action'] = "Booking Fee is R0";
                    }
                }
            }
            ///////////////////////////////////////////////////////
            //TODO: temp set booking fee to 0 for sharing rooms
            if ($isSharingRoom === true) {
                $room_rate = "0";
                $actionFileData['action'] = "Booking Fee waived (Sharing Room)";
            }
            ///////////////////////////////////////////////////////
            // existing DB updates here
            // Booking::update(...)
            if ($room_rate == "0") {

                $uploaded = UserFiles::where('userUid',  $userId)->where('fileId', 'proof_of_payment')->where('year', $this_year)->first();

                if (!$uploaded) {
                    UserFiles::create([
                        'userUid' => $user->id,
                        'fileId' => 'proof_of_payment',
                        'status' => 'Approved',
                        'year' => $this_year,
                    ]);

                    // User::where('id', $user->id)->update([
                    //     'rob_10_fee' => 2
                    // ]);
                    // StudentsInfo::update
                    StudentsInfo::where('userUid',  $userId)->where('year', $this_year)->update([
                        'rob_10_fee' => 2,
                        'rob_40_bed' => 1
                    ]);

                    $actionFileData['userUid'] = $user->id;
                    $actionFileData['action'] = "Booking Fee is R0";
                    $actionFileData['actionUserUid'] =  $userId;
                    $actionFileData['actionUserDisplayName'] = $user->userFirstName;
                    $actionFileData['actionUserRole'] = $user->userRole;
                    $actionFileData['year'] = $this_year;
                    // ActionList::create
                    ActionList::create($actionFileData);
                } else {
                    UserFiles::where('userUid',  $userId)->where('fileId', 'proof_of_payment')->where('year', $this_year)->update([
                        'status' => 'Approved'
                    ]);

                    // User::where('id', $user->id)->update([
                    //     'rob_10_fee' => 2
                    // ]);
                    StudentsInfo::where('userUid',  $userId)->where('year', $this_year)->update([
                        'rob_10_fee' => 2,
                        'rob_40_bed' => 1,
                    ]);
                }

                Booking::where('userId', $user->id)->where('year', $this_year)->update(['room_rate' => $room_rate]);
            } else {
                Booking::where('userId', $user->id)->where('year', $this_year)->update(['room_rate' => $room_rate]);
            }

            $newBooking = Booking::where('userId', $user->id)->where('year', $this_year)->first();

            DB::commit();
            return view('application.step_5', ["lookups" => $lookups, "user" => $userInfo, "booking" => $newBooking, "proofPayment" => $proofPayment, "agent" => $agent, "priced_from" => $priced_from, "chair" => $chair, "wifi" => $wifi, "bath" => $bath, "kitchen" => $kitchen, "beds" => $beds, "room_rate" => $room_rate, "roomTypeInfo" => $roomTypeInfo, "propertyTypeInfo" => $propertyTypeInfo]);
        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Step 5 failed', [
                'user' => auth()->user(),
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            abort(500, 'Unable to complete booking step');
        }
    }

    public function complete_online_app(Request $request)
    {
        $lookups = Lookups::get();

        // determine target year
        $year = Session::get('year') ?? Lookups::where('group_id', 4)->first()->lookup_name;

        // load current user
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        // search booking for selected year
        $booking = Booking::where('userId', $user->id)
            ->where('year', $year)
            ->first();

        // if not found, try previous year
        if (!$booking) {
            $year = $year - 1;

            $booking = Booking::where('userId', $user->id)
                ->where('year', $year)
                ->first();

            // if found, switch session year and continue
            if ($booking) {
                Session::put('year', $year);
            }
        }

        // if still null → logout
        if (!$booking) {
            Auth::logout();
            Session::flush();

            return redirect()->route('login')
                ->withErrors(['message' => 'No valid booking found.']);
        }

        // User::where('id', Auth::user()->id)->update(['onboard_complete' => 1]);
        StudentsInfo::where('userUid', Auth::user()->id)->where('year', $year)->update(['onboard_complete' => 2]);
        $userInfo = StudentsInfo::where('userUid', Auth::user()->id)->where('year', $year)->first();
        //dd($booking);
        return view('application.complete', ["lookups" => $lookups, "user" => $userInfo, "booking" => $booking]);
    }

    public function verify_password(Request $request)
    {
        // $lookups = Lookups::get();
        // $user = User::where('id', Auth::user()->id)->first();

        // return view('application.password', ["lookups" => $lookups, "user" => $user]);

        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        // User::where('id', Auth::user()->id)->update(['email_verified' => 1]);
        StudentsInfo::where('userUid', Auth::user()->id)->where('year', $this_year)->update(['email_verified' => 1]);

        return redirect('/step-2/online-application');
    }

    public function confirm_password(Request $request)
    {
        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        User::where('id', Auth::user()->id)->update(['password' => Hash::make($request->confirm_password)]);
        StudentsInfo::where('userUid', Auth::user()->id)->where('year', $this_year)->update(['email_verified' => 1]);
    }

    public function change_student_type(Request $request)
    {
        $lookups = Lookups::get();

        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        // $user = User::where('id', Auth::user()->id)->first();
        $user = StudentsInfo::where('userUid', Auth::user()->id)->where('year', $this_year)->first();
        $agent = new Agent();

        return view('user.changeType', ["headerTitle" => "Change Student Type", "lookups" => $lookups, "user" => $user, "agent" => $agent, "active" => 1]);
    }

    public function update_student_details(Request $request)
    {

        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        $user = User::where('id', Auth::user()->id)->first();
        $userInfo = StudentsInfo::where('userUid', Auth::user()->id)->where('year', $this_year)->first();

        if ($request->studentType !== $userInfo->studentType) {
            if ($userInfo->studentType == "Cash / Direct") {
                $file = UserFiles::where('fileId', 'bankStatement')->where('userUid', $user->id)->where('year', $this_year)->first();
                if (!empty($file)) {
                    UserFiles::where('id', $file->id)->update(['status' => 'Rejected']);
                }
            } else {
                $file = UserFiles::where('fileId', 'bursaryApproval')->where('userUid', $user->id)->where('year', $this_year)->first();
                if (!empty($file)) {
                    UserFiles::where('id', $file->id)->update(['status' => 'Rejected']);
                }
            }

            ActionList::create([
                "userUid" => Auth::user()->id,
                "action" => "Updated Student Type From " . $userInfo->studentType . " to " . $request->studentType,
                "actionUserUid" => Auth::user()->id,
                "actionUserDisplayName" => $userInfo->userFirstName,
                "actionUserRole" => Auth::user()->userRole,
                "year" => $this_year,
            ]);
        }


        if ($request->app_address_p == 1) {
            $request->pAddress1 = $userInfo->userAddress1;
            $request->pAddress2 = $userInfo->userAddress2;
            $request->pAddressCity = $userInfo->userAddressCity;
            $request->pAddressPostal = $userInfo->userAddressPostal;
        }

        StudentsInfo::where('userUid', Auth::user()->id)->where('year', $this_year)->update([
            'studentType' => $request->studentType,
            'sponFirstName' => $request->pFirstName,
            'sponLastName' => $request->pLastName,
            'sponCell' => $request->pCell_no,
            'sponCellCode' => $request->sponCellCode,
            'sponEmail' => $request->pEmail,
            'pNationality' => $request->pNationality,
            'sponId' => $request->pIdNumber,
            'pPassportNo' => $request->pPassportNo,
            'pAddress1' => $request->pAddress1,
            'pAddress2' => $request->pAddress2,
            'pAddressCity' => $request->pAddressCity,
            'pAddressPostal' => $request->pAddressPostal,
            'app_address_p' => $request->app_address_p,
            'burCompanyName' => $request->bCompanyName,
            'burRegNo' => $request->bRegistrationNo,
            'burContactPerson' => $request->bContactPerson,
            'burContactNo' => $request->bContactNumber,
            'burContactCode' => $request->burContactCode,
            'burEmail' => $request->bEmail,
            'bAddress1' => $request->bAddress1,
            'bAddress2' => $request->bAddress2,
            'bAddressCity' => $request->bAddressCity,
            'bAddressPostal' => $request->bAddressPostal,
            'bPayment' => $request->bPayment,
            'bint' => $request->bint,
            'bIntName' => $request->bIntName,
            'b_meeting' => $request->b_meeting,
            'c_credit_risk' => $request->c_credit_risk,
            'c_credit_check' => $request->c_credit_check,
        ]);

        if (($request->studentType == 'Bursary' && $userInfo->nsfas_bursar == '1') || $request->studentType == 'NSFAS') {

            // $booking = Booking::where('id', $userInfo->bookingId)->first();
            // $diff = 0;
            // for($i = new DateTime($booking->lease_from_date); $i <= new DateTime($booking->lease_to_date); $i->modify('+1 month')){
            //     $diff++;
            // }

            // $institution = Lookups::where('id', $userInfo->institutionStudy)->first();
            // $funded = floatval($institution->nsfas_amount) * floatval($diff);
            // $thisBookingPayment = BookingPayment::where('bookingId', $userInfo->bookingId)->first();
            // BookingPayment::where('bookingId', $userInfo->bookingId)->update([
            //     'deposit' => '0',
            //     'loan_admin_fee' => '0',
            //     'total_funded' => $funded,
            //     'total_top_up' => floatval($thisBookingPayment->monthly_rental_total) - floatval($funded),
            //     'funded_monthly' => $institution->nsfas_amount,
            // ]);
            app('App\Http\Controllers\HomeController')->paymentDeatilsInternal(Auth::user()->id);
            $studentTypeChanged = 1;
        }

        if (($request->studentType == 'Bursary' && $userInfo->nsfas_bursar == '0') || $request->studentType == 'Cash / Direct') {

            // $booking = Booking::where('id', $userInfo->bookingId)->first();
            // $diff = 0;
            // for($i = new DateTime($booking->lease_from_date); $i <= new DateTime($booking->lease_to_date); $i->modify('+1 month')){
            //     $diff++;
            // }

            // $institution = Lookups::where('id', $userInfo->institutionStudy)->first();
            // $admin_fee = Lookups::where('group_id', '8')->first();
            // $funded = floatval($institution->nsfas_amount) * floatval($diff);
            // $thisBookingPayment = BookingPayment::where('bookingId', $userInfo->bookingId)->first();
            // BookingPayment::where('bookingId', $userInfo->bookingId)->update([
            //     'deposit' => $thisBookingPayment->monthly_rental,
            //     'loan_admin_fee' => $admin_fee->lookup_name,
            //     'total_funded' => $thisBookingPayment->monthly_rental_total,
            //     'total_top_up' => '0',
            //     'funded_monthly' => $thisBookingPayment->monthly_rental,
            // ]);
            app('App\Http\Controllers\HomeController')->paymentDeatilsInternal(Auth::user()->id);
            $studentTypeChanged = 1;
        }
    }

    public function verify_email_account(Request $request)
    {

        // User::where('id', Auth::user()->id)->update(['onboard_complete' => 2, 'email_verified' => 1]);
        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        StudentsInfo::where('userUid', Auth::user()->id)->where('year', $this_year)->update(['onboard_complete' => 2, 'email_verified' => 1]);

        $appOnline = Lookups::where('group_id', '9')->first();

        if ($appOnline->lookup_name == '1') {
            return redirect('/dashboard');
        } else {
            return redirect('/verified');
        }
    }

    public function verify_new_email_account(Request $request)
    {
        // User::where('id', Auth::user()->id)->update(['email_verified' => 1]);

        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        StudentsInfo::where('userUid', Auth::user()->id)->where('year', $this_year)->update(['email_verified' => 1]);

        $appOnline = Lookups::where('group_id', '9')->first();

        if ($appOnline->lookup_name == '1') {
            return redirect('/dashboard');
        } else {
            return redirect('/verified');
        }
    }

    public function verified()
    {
        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;
        /** @disregard P1009 */
        $userId = Auth::user()->id;
        $info = StudentsInfo::where('userUid', $userId)->where('year', $this_year)->first();
        $booking = Booking::where('id', $info->bookingId)->first();
        return view('outSession.verified', compact('info', 'booking'));
    }

    public function loginAsStudent($userid)
    {
        /** @disregard P1009 */
        Auth::loginUsingId($userid, true);
        return redirect('/dashboard');
    }

    public function get_room_ids(Request $request)
    {
        $property = $request->residence;

        $residence = Residence::where('id', $property)->first();

        $property = Property::where('residence', $residence->id)->get();
        $this_year = Lookups::where('group_id', '4')->first();
        $searchable_rooms = [];

        foreach ($property as $prop) {
            if ($prop->year_end == 0 || $prop->year_end >= $this_year) {
                if (!in_array($prop->room_type, $searchable_rooms)) {
                    array_push($searchable_rooms, $prop->room_type);
                }
            }
        }

        $rooms = RoomTypes::where('property_id', $residence->id)->where('name', '!=', 'Building Manager')->whereIn('id', $searchable_rooms)->get();

        return $rooms;
    }

    public function re_verify_email(Request $request)
    {

        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        // $userInfo = StudentsInfo::where('userUid', $request->userUid)->where('year', $this_year)->first();
        // $user = User::where('id', $request->userUid)->first();
        $booking = Booking::where('userId', $request->userUid)->where('year', $this_year)->first();

        $user = User::where('id', $request->userUid)->first();

        $generator = new LoginUrl($user);
        $generator->setRedirectUrl('/verify-new-email-account');
        $url = $generator->generate();

        $userUidData = User::where('id', $request->userUid)->first();
        $this->dispatch(new SendStudentVerifyMailByAdminMail($userUidData, $this_year, $booking->booking_code, $url));


        // $message_replace['bookingRef'] = $booking->booking_code;
        // $message_replace['name'] = $userInfo->userFirstName;
        // $message_replace['application_link'] = "<a href='".$url."' target='_blank'>";
        // $message_replace['close_application_link']="</a>";

        // $mailerForMessage = MailerList::where("mailer_name", "Password Validation From Admin")->first();
        // $mailerMessage = $mailerForMessage->message;

        // $newMessage = str_replace("[application_number]", "#".$message_replace['bookingRef'], $mailerMessage);
        // $newMessage = str_replace("[name]", $message_replace['name'], $newMessage);
        // $newMessage = str_replace("[application_link]", $message_replace['application_link'], $newMessage);
        // $newMessage = str_replace("[close_application_link]", $message_replace['close_application_link'], $newMessage);

        // $data['name'] = $userInfo->userFirstName;
        // $data['email'] = $userInfo->userEmail;
        // $data['messageForMail'] = $newMessage;
        // $data['subject'] = str_replace("[application_number]", $message_replace['bookingRef'], $mailerForMessage->subject);

        // $allowedMailsCC = Lookups::where('group_id', '11')->pluck('lookup_name')->first();
        // $data['cc'] = explode(";", $allowedMailsCC);

        // $allowedMails = Lookups::where('group_id', '10')->pluck('lookup_name')->first();

        // if(!empty($allowedMails)){

        //     $mailArr = explode(";", $allowedMails);

        //     if(in_array(explode("@", $data['email'])[1], $mailArr)){
        //         $data['email'] = $userInfo->userEmail;
        //     }else{
        //         $data['email'] = "studentsca00@gmail.com";
        //     }
        // }else{
        //     $data['email'] = $userInfo->userEmail;
        // }


        // Mail::send('email.yellow_back', $data, function($message) use ($data) {
        //     // $message->to($data['email'], $data['name'])->subject
        //     //     ('Campus Africa User Application');
        //     $message->to($data['email'])->subject($data['subject']);
        //     $message->bcc($data['cc']);
        //     $message->from('noreply@campusafrica.co.za','Campus Africa');
        // });
    }

    public function retry_payment(Request $request)
    {
        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;
        /** @disregard P1009 */
        $userId = Auth::user()->id;

        $lookups = Lookups::get();
        $user = User::where('id', $userId)->first();
        $userInfo = StudentsInfo::where('userUid', $userId)->where('year', $this_year)->first();
        $agent = new Agent();
        $transaction = OnlinePayments::where("userUid", $userId)->orderBy("created_at", "desc")->first();

        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        $booking = Booking::where('userId', $userId)->where('year', $this_year)->first();
        $propertyTypeInfo = Residence::where('id', $booking->property)->first();

        return view('application.retry', ["lookups" => $lookups, "user" => $userInfo, "agent" => $agent, "transaction" => $transaction, "booking" => $booking, "propertyTypeInfo" => $propertyTypeInfo]);
    }

    public function new_booking_step_1(Request $request)
    {
        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;
        /** @disregard P1009 */
        $userId = Auth::user()->id;

        $lookups = Lookups::get();
        $residences = Residence::whereNull('status')->get();
        $user = User::where('id', $userId)->first();
        $userInfo = StudentsInfo::where('userUid', $userId)->orderBy('year', 'desc')->first();

        $inSelectedCash = InstitutionProperties::where("institution_id", $userInfo->institutionStudy)->where('cash', '1')->pluck('property_id')->toArray();
        $inSelectedBursary = InstitutionProperties::where("institution_id", $userInfo->institutionStudy)->where('bursary', '1')->pluck('property_id')->toArray();
        $inSelectedNsfas = InstitutionProperties::where("institution_id", $userInfo->institutionStudy)->where('nsfas', '1')->pluck('property_id')->toArray();
        $questions = Question::with('options')->get();

        $cancelled = 0;

        if ($userInfo->rob_60_cancelled == 1 || $userInfo->year < $this_year - 1) {
            $cancelled = 1;
        }
        return view('user.new_booking.step_1', ["user" => $userInfo, "cancelled" => $cancelled, "lookups" => $lookups, "residences" => $residences, "inSelectedCash" => $inSelectedCash, "inSelectedBursary" => $inSelectedBursary, "inSelectedNsfas" => $inSelectedNsfas, "questions" => $questions]);
    }

    public function new_booking_step_1_save(Request $request)
    {
        // dd($request);
        /** @disregard P1009 */
        $userId = Auth::user()->id;

        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;
        //dd($this_year);
        $updateData = [
            'First Name|userFirstName' => ucwords(strtolower($request->firstName)),
            'Last Name|userLastname' => ucwords(strtolower($request->lastName)),
            'Title|userTitle' => $request->title,
            'Cellphone Number|userCell' => $request->cell_no,
            'Cellphone Country Code|userCellCode' => $request->userCellCode,
            'institution|institutionStudy' => $request->institStudy,
            'Student Number|studentNo' => $request->studNumber,
            'Faculty|faculty' => $request->faculty,
            'Year|year' => $request->year,
            'Street Address|userAddress1' => $request->userAddress1,
            'Suburb|userAddress2' => $request->userAddress2,
            'City|userAddressCity' => $request->userAddressCity,
            'Postal Code|userAddressPostal' => $request->userAddressPostal,
            'Student Type|studentType' => $request->studentType,
            'Year of Study|yearOfStudy' => $request->yearOfStudy,
            'Gender|gender' => $request->gender,
        ];

        // $userInfo = User::where('id', $userId)->first();
        $userInfo = StudentsInfo::where('userUid', $userId)->orderBy('year', 'desc')->first();

        foreach ($updateData as $key => $new) {
            $info = explode("|", $key);
            $key = $info[1];
            $changedField = $info[0];
            //dd($userInfo->$key);
            if ($changedField == "Gender") {

                if ($userInfo->$key == 1) {
                    $userInfo->$key = "Male";
                } elseif ($userInfo->$key == 2) {
                    $userInfo->$key = "Female";
                }

                if ($new == 1) {
                    $new = "Male";
                } elseif ($new == 2) {
                    $new = "Female";
                }
            }

            if ($userInfo->$key !== $new) {
                /** @disregard P1009 */
                ActionList::create([
                    "userUid" => $userId,
                    "action" => "Updated " . $changedField . " From " . $userInfo->$key . " to " . $new,
                    "actionUserUid" => $userId,
                    "actionUserDisplayName" => $request->firstName,
                    "actionUserRole" => Auth::user()->userRole,
                    "year" => $this_year,
                ]);
            }
        }

        $newStudentData = [
            'userUid' => $userId,
            'userDob' => $userInfo->userDob,
            'userEmail' => $userInfo->userEmail,
            'userId' => $userInfo->userId,
            'userNationality' => $userInfo->userNationality,
            'userPassport' => $userInfo->userPassport,
            'userFirstName' => ucwords(strtolower($request->firstName)),
            'userLastname' => ucwords(strtolower($request->lastName)),
            'userTitle' => $request->title,
            'userCell' => $request->cell_no,
            'userCellCode' => $request->userCellCode,
            'institutionStudy' => $request->institStudy,
            'studentNo' => $request->studNumber,
            'faculty' => $request->faculty,
            'year' => $request->year,
            'userAddress1' => $request->userAddress1,
            'userAddress2' => $request->userAddress2,
            'userAddressCity' => $request->userAddressCity,
            'userAddressPostal' => $request->userAddressPostal,
            'studentType' => $request->studentType,
            'yearOfStudy' => $request->yearOfStudy,
            'rob_10_fee' => 0,
            'rob_15_id' => 0,
            'rob_20_card' => 0,
            'rob_25_reg' => 0,
            'rob_30_funds' => 0,
            'rob_35_info' => 0,
            'rob_40_bed' => 0,
            'rob_50_approve' => 0,
            'rob_55_lease' => 3,
            'rob_45_other' => 3,
            'rob_60_cancelled' => 0,
            'email_data_approved' => 0,
            'onboard_complete' => 0,
            'email_verified' => 0,
            'fee_approved' => 0,
            'id_data_approved' => 0,
            'student_card_approved' => 0,
            'registration_approved' => 0,
            'bursary_approved' => 0,
            'bank_approved' => 0,
            'address_approved' => 0,
            'kin_approved' => 0,
            'parent_approved' => 0,
            'burser_approved' => 0,
            'email_info' => 0,
            'app_source' => 1,
            'gender' => $request->gender,
        ];

        if ($userInfo->year !== $request->year) {
            $user = StudentsInfo::create($newStudentData);
            /** @disregard P1009 */
            ActionList::create([
                "userUid" => $userId,
                "action" => "Created a new Booking ",
                "actionUserUid" => $userId,
                "actionUserDisplayName" => $request->firstName,
                "actionUserRole" => Auth::user()->userRole,
                "year" => $request->year,
            ]);
        } else {
            $user = StudentsInfo::where('userUid', $userId)->where('year', $request->year)->update($newStudentData);
        }

        User::where('id', $request->userUid)->update([
            'name' => $request->firstName,
            'email' => $request->userEmail,
            'userId' => $request->userId,
            'userNationality' => $request->userNationality,
            'userPassport' => $request->userPassport,
            'year' => $request->year,
        ]);


        //Booking data        
        $booking_data = array(
            "userId" => $userId,
            "property" => $request->property,
            "roomType" => $request->roomType,
            "year" => $request->year
        );

        $lookup = Lookups::where('group_id', '7')->first();

        $oldPropReq = $request->property;
        $roomId = $request->roomType;

        $request->property = Residence::where('id', $request->property)->pluck('residence')->first();
        $request->roomType = RoomTypes::where('id', $request->roomType)->pluck('name')->first();

        $roomTypeData = RoomTypes::where('id', $roomId)->first();
        $roomTypeId = $roomTypeData->room_map_slug;
        $is_nsfas = false;
        if ($roomTypeData->use_nsfas == 1) {
            $is_nsfas = true;
        }

        $allResidences = Residence::whereNull('status')->get();
        $allRoomTypes = RoomTypes::get();


        if ($lookup && $lookup->lookup_name == "1") {
            $booking_data['room_rate'] = "0";
            $actionFileData['action'] = "Booking Fee Waived";
        } else {
            //Get residences from WP
            $jsonDataResidence = json_decode(file_get_contents('https://www.campusafrica.co.za/wp-json/wp/v2/residences'));
            $residenceId = "";

            foreach ($jsonDataResidence as $residence) {
                if ($residence->title->rendered == $request->property) {
                    //get room booking fee from residence (WP)
                    $booking_data['room_rate'] = $residence->acf->booking_fee;
                    //get residence ID (WP)
                    $residenceId = $residence->id;
                }
            }

            $jsonDataRooms = json_decode(file_get_contents('https://www.campusafrica.co.za/wp-json/wp/v2/rooms?per_page=100'));

            $roomType = "";
            $roomName = "";

            foreach ($jsonDataRooms as $room) {
                if ($residenceId == $room->acf->residence) {

                    if ($room->id == $roomTypeId) {
                        $roomType = $request->roomType;
                        $roomName = $room->slug;
                        $deposit = $room->acf->price_from;

                        $appliesTo = $room->acf->booking_fee_applies_to;

                        if ($room->acf->booking_fee_override !== "") {
                            $booking_data['room_rate'] = $room->acf->booking_fee_override;
                        }

                        if ($room->slug == $roomName && $roomType == $request->roomType) {

                            if ($room->acf->booking_fee_override !== "") {
                                $booking_data['room_rate'] = $room->acf->booking_fee_override;
                            }
                            // Bursary
                            if ($request->studentType === "NSFAS" || $request->studentType === "NRF" || $request->studentType === "FUNZA" || $request->studentType === "GCRA" || $request->studentType === "MQA") {
                                if (!in_array("NSFAS Students", $appliesTo)) {
                                    $booking_data['room_rate'] = "0";
                                    $actionFileData['action'] = "Booking Fee is R0";
                                }
                            }

                            if ($request->studentType === "Bursary") {
                                if (!in_array("Bursary Students", $appliesTo)) {
                                    $booking_data['room_rate'] = "0";
                                    $actionFileData['action'] = "Booking Fee is R0";
                                }
                            }

                            if ($request->studentType === "Cash / Direct") {
                                if (!in_array("Cash Students", $appliesTo)) {
                                    $booking_data['room_rate'] = "0";
                                    $actionFileData['action'] = "Booking Fee is R0";
                                }
                            }
                        }
                    }
                }
            }
        }

        $user = StudentsInfo::where('userUid', $userId)->where('year', $this_year)->first();
        //dd($user);
        if ($roomTypeData && $roomTypeData->pull_price == null) {
            $booking_data['room_rate'] = $roomTypeData->booking_price;
            $deposit = $roomTypeData->rent_price;

            if ($user->studentType == "NSFAS" || $request->studentType === "FUNZA" || $user->studentType == "GCRA" || $user->studentType == "MQA") {
                if ($roomTypeData->apply_nsfas == null) {
                    $booking_data['room_rate'] = "0";
                    $actionFileData['action'] = "Booking Fee is R0";
                }
            }

            if ($user->studentType == "Bursary") {
                if ($roomTypeData->apply_bursary == null) {
                    $booking_data['room_rate'] = "0";
                    $actionFileData['action'] = "Booking Fee is R0";
                }
            }

            if ($user->studentType == "Cash / Direct") {
                if ($roomTypeData->apply_cash == null) {
                    $booking_data['room_rate'] = "0";
                    $actionFileData['action'] = "Booking Fee is R0";
                }
            }
        }



        //Get default lease data for the booking
        $lookup_lease_dates = Lookups::where(['group_id' => '4', 'lookup_name' => $request->year])->first();

        $booking_data['lease_from_date'] = $lookup_lease_dates['lease_from_date'];
        $booking_data['lease_from_time'] = $lookup_lease_dates['lease_from_time'];
        $booking_data['lease_to_date'] = $lookup_lease_dates['lease_to_date'];
        $booking_data['lease_to_time'] = $lookup_lease_dates['lease_to_time'];

        // $booking = Booking::create($booking_data);
        /** @disregard P1009 */
        $userId = Auth::user()->id;
        $booking_search = Booking::where('userId', $userId)->where('year', $request->year)->first();

        if (empty($booking_search)) {
            $booking = Booking::create($booking_data);
        } else {
            Booking::where('userId', $userId)->where('year', $request->year)->update($booking_data);
            $booking = $booking_search;
        }

        $bookingYear = substr($request->year, -2);
        $bookingPrefix = Residence::where('id', $oldPropReq)->pluck('building_prefix')->first();
        $id_booking = $booking->id;
        $placeholder = "";
        for ($i = 0; $i < 5 - strlen($id_booking); $i++) {
            $placeholder .= "0";
        }
        $bookingID = $placeholder . $id_booking;
        Booking::where('id', $booking->id)->update(['booking_code' => $bookingYear . $bookingPrefix . $bookingID]);

        //Link booking to User
        // User::where('id', $userId)->update(["bookingRef" => $booking->id]);
        StudentsInfo::where('userUid', $userId)->where('year', $this_year)->update(["bookingRef" => $booking->id, "bookingId" => $booking->id]);

        $residence = Residence::where('residence', $request->property)->first();

        if ($booking_data['room_rate'] == "0") {

            // User::where('id', $userId)->update(["rob_10_fee" => 2]);
            StudentsInfo::where('userUid', $userId)->where('year', $this_year)->update(["rob_10_fee" => 2]);

            $this_year = Lookups::where('group_id', '4')->first();
            $this_year = $this_year->lookup_name;

            $findPop = UserFiles::where('userUid', $userId)->where('fileId', 'proof_of_payment')->where('year', $this_year)->first();

            if (empty($findPop)) {
                UserFiles::create([
                    'userUid' => $userId,
                    'fileId' => 'proof_of_payment',
                    'status' => 'Approved',
                    'year' => $this_year
                ]);
                StudentsInfo::where('userUid', $userId)->where("year", $this_year)->update(["rob_40_bed" => 1]);
            } else {
                UserFiles::where('userUid', $userId)->where('fileId', 'proof_of_payment')->where('year', $this_year)->update([
                    'status' => 'Approved',
                ]);
                StudentsInfo::where('userUid', $userId)->where("year", $this_year)->update(["rob_40_bed" => 1]);
            }

            $actionFileData['userUid'] = $userId;
            $actionFileData['action'] = "Booking Fee is R 0";
            $actionFileData['actionUserUid'] = $userId;
            $actionFileData['actionUserDisplayName'] = $user->userFirstName;
            /** @disregard P1009 */
            $actionFileData['actionUserRole'] = Auth::user()->userRole;
            $actionFileData['year'] = $this_year;

            ActionList::create($actionFileData);
        }
        // Bursary
        if ($request->studentType == "NSFAS" || $request->studentType === "FUNZA" || $request->studentType == "GCRA" || $request->studentType == "MQA") {

            $nsfasStudentsApproval = ImportNSFASStudentAuditLog::where('student_number', $request->studNumber)->orderBy('created_at', 'desc')->first();

            if (!empty($nsfasStudentsApproval)) {

                $yearOfImport = ImportNSFASApproval::where('id', $nsfasStudentsApproval->importId)->first();
                $this_year = Lookups::where('group_id', '4')->first();

                if (!empty($nsfasStudentsApproval) && $yearOfImport->year == $this_year->lookup_name) {

                    $findBa = UserFiles::where('userUid', $userId)->where('fileId', 'bursaryApproval')->where('year', $this_year->lookup_name)->first();

                    if (empty($findBa)) {
                        UserFiles::create([
                            'userUid' => $userId,
                            'fileId' => 'bursaryApproval',
                            'status' => 'Approved',
                            'year' => $this_year->lookup_name
                        ]);
                    } else {
                        UserFiles::where('userUid', $userId)->where('fileId', 'bursaryApproval')->where('year', $this_year->lookup_name)->update([
                            'status' => 'Approved',
                        ]);
                    }

                    // User::where('id', $userId)->update(["rob_30_funds" => 2]);
                    StudentsInfo::where('userUid', $userId)->where('year', $this_year->lookup_name)->update(["rob_30_funds" => 2]);
                    /** @disregard P1009 */
                    ActionList::create([
                        "userUid" => $userId,
                        // "action" => "NSFAS / Bursary has been Approved via import",
                        "action" => "NSFAS Approval has been Approved via import",
                        "actionUserUid" => $userId,
                        "actionUserDisplayName" => $user->userFirstName,
                        "actionUserRole" => Auth::user()->userRole,
                        "year" => $this_year->lookup_name,
                    ]);
                }
            }
        }

        $user = User::where('id', $userId)->first();
        $userInfo = StudentsInfo::where('userUid', $userId)->where('year', $this_year)->first();

        $user->login_route_expires_in = 60 * 24 * 2; // 2 days   
        $generator = new LoginUrl($user);

        $generator->setRedirectUrl('/verify-email-account');
        $url = $generator->generate();

        $userUidData = User::where('id', $userId)->first();
        $this->dispatch(new SendNewBookingMail($userUidData, $url));


        // Log details for debugging
        $expiresAt = now()->addMinutes($user->login_route_expires_in);

        Log::info('Magic Login URL Generated', [
            'user_id' => $user->id,
            'email' => $user->email,
            'magic_login_url' => $url,
            'expires_at' => $expiresAt->toDateTimeString(),
            'expires_timestamp' => $expiresAt->timestamp,
            'current_time' => now()->toDateTimeString(),
            'expires_in_human' => $expiresAt->diffForHumans(),
        ]);
        $lookup_lease_dates = Lookups::where('group_id', '4')->first();
        $booking = Booking::where('id', $booking->id)->first();

        $alreadyPayment = BookingPayment::where("bookingId", $booking->id)->first();

        if (empty($alreadyPayment)) {


            $bookingPrice = $booking_data['room_rate'];
            $residenceId = 0;
            $roomTypeData = RoomTypes::where('id', $booking->roomType)->first();
            $booking->property = Residence::where('id', $booking->property)->pluck('residence')->first();

            $userInfo = StudentsInfo::where('bookingId', $booking->id)->orderBy('year', 'desc')->first();

            if (!empty($userInfo)) {

                $diff = 0;
                for ($i = new DateTime($lookup_lease_dates['lease_from_date']); $i <= new DateTime($lookup_lease_dates['lease_to_date']); $i->modify('+1 month')) {
                    $diff++;
                }
                $monthlyRent = floatval($deposit);
                $yearly_rent = $monthlyRent * floatval($diff);

                $institution = Lookups::where('id', $userInfo->institutionStudy)->first();
                $funded = floatval($institution->nsfas_amount) * floatval($diff);

                $topup = floatval($yearly_rent) - floatval($funded);
                $monthlyFunded = $institution->nsfas_amount;

                $nsfas_rate = null;

                $monthly = $monthlyRent;

                if (($userInfo->studentType == "Cash / Direct") || ($userInfo->studentType == "Bursary" && $userInfo->nsfas_bursar !== 1)) {
                    $monthlyFunded = $monthlyRent;
                    $funded = $yearly_rent;
                    $topup = '0';
                } else {
                    if ($is_nsfas === true) {
                        $monthly = $monthlyFunded;
                        $yearly_rent = $funded;
                        $topup = 0;
                        $nsfas_rate = 1;
                    }
                }

                $admin_fee = 0;
                $deposit = 0;
                $fundingId = Lookups::fundingTypeIdForLabel($userInfo->studentType);
                $selectedYear = $booking->year ?? null;

                if ($fundingId && $selectedYear) {
                    $yearLookup = Lookups::where('group_id', '4')->where('lookup_name', $selectedYear)->first();
                    $yearData = [];
                    if ($yearLookup) {
                        if (is_array($yearLookup->data)) {
                            $yearData = $yearLookup->data;
                        } elseif (!empty($yearLookup->data)) {
                            $decoded = json_decode($yearLookup->data, true);
                            if (is_array($decoded)) {
                                $yearData = $decoded;
                            }
                        }
                    }

                    $leaseFees = $yearData['lease_fees'] ?? [];
                    $depositApplies = $yearData['deposit_applies'] ?? [];

                    if (array_key_exists($fundingId, $leaseFees)) {
                        $admin_fee = floatval($leaseFees[$fundingId]);
                    }

                    if (array_key_exists($fundingId, $depositApplies) && $depositApplies[$fundingId]) {
                        $deposit = $monthlyRent;
                    }
                }

                BookingPayment::create([
                    "bookingId" => $booking->id,
                    "booking_fee" => $bookingPrice,
                    "booking_fee_waived" => 0,
                    "rob_b_waived" => 0,
                    "loan_admin_fee" => $admin_fee,
                    "loan_admin_fee_waived" => 0,
                    "rob_loan_waived" => 0,
                    "deposit" => floatval($deposit),
                    "deposit_waived" => 0,
                    "rob_deposit_waived" => 0,
                    "monthly_rental" => $monthly,
                    "monthly_rental_total" => $yearly_rent,
                    "total_funded" => $funded,
                    "total_top_up" => $topup,
                    "funded_monthly" => $monthlyFunded,
                    "nsfas_rate" => $nsfas_rate,
                ]);
            }
        }
        if (!empty($userInfo)) {
            $questionOptions = QuestionOption::all();
            $studentId = $userInfo->id;
            $answers = $request->answers;
            $year = $request->year;
            foreach ($answers as $questionId => $optionId) {
                $option = $questionOptions->firstWhere('id', $optionId);
                StudentQuestionAnswer::updateOrCreate(
                    [
                        'student_id' => $studentId,
                        'question_id' => $questionId,
                        'academic_year' => $year,
                    ],
                    [
                        'question_option_id' => $optionId,
                        'numeric_value' => $option ? $option->option_value : null, // optional if you want numeric comparison
                    ]
                );
            }
        }

        return redirect('/new-booking-step-2');
    }

    public function new_booking_step_2(Request $request)
    {
        $lookups = Lookups::get();

        // $this_year = Lookups::where('group_id', '4')->first();
        // $this_year = $this_year->lookup_name;
        $year = Session::get('year') ?? Lookups::where('group_id', 4)->first()->lookup_name;
        // $user = User::where('id', Auth::user()->id)->first();
        //dd($year);
        /** @disregard P1009 */
        $userId = Auth::user() ? Auth::user()->id : null;

        if (!$userId) {
            return redirect()->route('login');
        }
        // search booking for selected year
        $booking = Booking::where('userId', $userId)
            ->where('year', $year)
            ->first();

        // if not found, try previous year
        if (!$booking) {
            $year = $year - 1;

            $booking = Booking::where('userId', $userId)
                ->where('year', $year)
                ->first();

            // if found, switch session year and continue
            if ($booking) {
                Session::put('year', $year);
            }
        }

        // if still null → logout
        if (!$booking) {
            Auth::logout();
            Session::flush();
            return redirect()->route('login')
                ->withErrors(['message' => 'No valid booking found.']);
        }

        $user = StudentsInfo::where('userUid', $userId)->orderBy('year', 'desc')->where('year', '!=', $year)->first();
        $userNew = StudentsInfo::where('userUid', $userId)->where('year', $year)->first();

        $agent = new Agent();

        if ($userNew->studentType == "Cash / Direct") {
            return view('application.step_2_spon', ["lookups" => $lookups, "user" => $user, "agent" => $agent, "new" => 1, "userNew" => $userNew]);
        } elseif ($userNew->studentType == "Bursary") {
            return view('application.step_2_bur', ["lookups" => $lookups, "user" => $user, "agent" => $agent, "new" => 1, "userNew" => $userNew]);
        } else {
            return view('application.step_3', ["lookups" => $lookups, "user" => $user, "agent" => $agent, "new" => 1, "userNew" => $userNew]);
        }
    }

    public function new_booking_step_2_spon_save(Request $request)
    {
        /** @disregard P1009 */
        $userId = Auth::user()->id;

        $user = User::where('id', $userId)->first();

        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        $userInfo = StudentsInfo::where('userUid', $userId)->where('year', $this_year)->first();

        if ($request->app_address_p == 1) {
            $request->pAddress1 = $userInfo->userAddress1;
            $request->pAddress2 = $userInfo->userAddress2;
            $request->pAddressCity = $userInfo->userAddressCity;
            $request->pAddressPostal = $userInfo->userAddressPostal;
        }

        StudentsInfo::where('userUid', $user->id)->where('year', $this_year)->update([
            'sponFirstName' => $request->pFirstName,
            'sponLastName' => $request->pLastName,
            'sponCell' => $request->pCell_no,
            'sponCellCode' => $request->sponCellCode,
            'sponEmail' => $request->pEmail,
            'pNationality' => $request->pNationality,
            'sponId' => $request->pIdNumber,
            'pPassportNo' => $request->pPassportNo,
            'pAddress1' => $request->pAddress1,
            'pAddress2' => $request->pAddress2,
            'pAddressCity' => $request->pAddressCity,
            'pAddressPostal' => $request->pAddressPostal,
            'app_address_p' => $request->app_address_p,
            'c_credit_check' => $request->c_credit_check,
            'c_credit_risk' => $request->c_credit_risk,
        ]);

        return "/new-booking-step-3";
    }

    public function new_booking_step_2_bur_save(Request $request)
    {
        /** @disregard P1009 */
        $userId = Auth::user()->id;

        $user = User::where('id', $userId)->first();

        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        StudentsInfo::where('userUid', $user->id)->where('year', $this_year)->update([
            'burCompanyName' => $request->bCompanyName,
            'burRegNo' => $request->bRegistrationNo,
            'burContactPerson' => $request->bContactPerson,
            'burContactNo' => $request->bContactNumber,
            'burContactCode' => $request->burContactCode,
            'burEmail' => $request->bEmail,
            'bAddress1' => $request->bAddress1,
            'bAddress2' => $request->bAddress2,
            'bAddressCity' => $request->bAddressCity,
            'bAddressPostal' => $request->bAddressPostal,
            'bPayment' => $request->bPayment,
            'bint' => $request->bint,
            'bIntName' => $request->bIntName,
        ]);

        return "/new-booking-step-3";
    }

    public function new_booking_step_3(Request $request)
    {
        /** @disregard P1009 */
        $userId = Auth::user()->id;

        $lookups = Lookups::get();
        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;
        $user = StudentsInfo::where('userUid', $userId)->orderBy('year', 'desc')->where('year', '!=', $this_year)->first();
        $userNew = StudentsInfo::where('userUid', $userId)->where('year', $this_year)->first();
        $agent = new Agent();

        return view('application.step_3', ["lookups" => $lookups, "user" => $user, "agent" => $agent, "new" => 1, "userNew" => $userNew]);
    }

    public function new_booking_step_3_save(Request $request)
    {
        /** @disregard P1009 */
        $userId = Auth::user()->id;

        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        $userInfo = StudentsInfo::where('userUid', $userId)->where('year', $this_year)->first();

        if ($request->app_parent_kin == 1) {
            $request->kFirstName = $userInfo->sponFirstName;
            $request->kLastName = $userInfo->sponLastName;
            $request->kCellNo = $userInfo->sponCell;
            $request->kinCellCode = $userInfo->sponCellCode;
            $request->kEmail = $userInfo->sponEmail;
            $request->kAddress1 = $userInfo->pAddress1;
            $request->kAddress2 = $userInfo->pAddress2;
            $request->kAddressCity = $userInfo->pAddressCity;
            $request->kAddressPostal = $userInfo->pAddressPostal;
        }

        if ($request->app_address == 1) {
            $request->kAddress1 = $userInfo->userAddress1;
            $request->kAddress2 = $userInfo->userAddress2;
            $request->kAddressCity = $userInfo->userAddressCity;
            $request->kAddressPostal = $userInfo->userAddressPostal;
        }

        StudentsInfo::where('userUid', $userId)->where('year', $this_year)->update([
            'app_parent_kin' => $request->app_parent_kin,
            'kinFirstName' => $request->kFirstName,
            'kinLastName' => $request->kLastName,
            'kinTitle' => $request->kTitle,
            'kinRelationship' => $request->kRelationship,
            'kinCellNo' => $request->kCellNo,
            'kinCellCode' => $request->kinCellCode,
            'kinEmail' => $request->kEmail,
            'kinWorkNo' => $request->kWorkNo,
            'kinWorkCode' => $request->kinWorkCode,
            'kinTelNo' => $request->kTeleNo,
            'kinTelCode' => $request->kinTelCode,
            'app_address' => $request->app_address,
            'kinAddressStr' => $request->kAddress1,
            'kinAddressSuburb' => $request->kAddress2,
            'kinAddressCity' => $request->kAddressCity,
            'kinAddressPost' => $request->kAddressPostal,
        ]);

        return "/new-booking-step-4";
    }

    private function duplicatePreviousYearFile($userId, $fileId, $currentYear)
    {
        $previousYear = $currentYear - 1;

        // Check if file exists for current year already
        $current = UserFiles::where('userUid', $userId)
            ->where('fileId', $fileId)
            ->where('year', $currentYear)
            ->first();

        if ($current) {
            return $current; // nothing to do
        }

        // Look for previous year file
        $previous = UserFiles::where('userUid', $userId)
            ->where('fileId', $fileId)
            ->where('year', $previousYear)
            ->where('status', "Approved")
            ->first();

        if (!$previous) {
            return null; // no prior file to duplicate
        }

        // Duplicate record for the new year
        $new = $previous->replicate(); // copies fillable fields
        $new->year = $currentYear;
        $new->status = "Uploaded";     // reset status so it's not auto-approved
        $new->approved_by = null;      // remove approval
        $new->rejectReason = null;     // optional: clear previous rejection
        $new->created_at = now();
        $new->updated_at = now();

        $new->save();

        return $new;
    }


    public function new_booking_step_4(Request $request)
    {
        /** @disregard P1009 */
        $userId = Auth::user()->id;
        if (!$userId) {
            return redirect()->route('login');
        }

        $lookups = Lookups::get();
        // determine target year
        $year = Session::get('year') ?? Lookups::where('group_id', 4)->first()->lookup_name;
        // search booking for selected year
        $booking = Booking::where('userId', $userId)
            ->where('year', $year)
            ->first();

        // if not found, try previous year
        if (!$booking) {
            $year = $year - 1;

            $booking = Booking::where('userId', $userId)
                ->where('year', $year)
                ->first();

            // if found, switch session year and continue
            if ($booking) {
                Session::put('year', $year);
            }
        }

        // if still null → logout
        if (!$booking) {
            /** @disregard P1009 */
            Auth::logout();
            Session::flush();

            return redirect()->route('login')
                ->withErrors(['message' => 'No valid booking found.']);
        }

        $userInfo = StudentsInfo::where('userUid', $userId)->orderBy('year', 'desc')->first();

        // $IdCopy = UserFiles::where("userUid", $userId)->where('fileId', 'copyID')->where('year', $year)->first();
        // $StudentCopy = UserFiles::where("userUid", $userId)->where('fileId', 'studentCard')->where('year', $year)->first();

        // new logic for repeat booking duplicate previous year files
        $IdCopy           = $this->duplicatePreviousYearFile($userId, 'copyID', $year);
        $StudentCopy      = $this->duplicatePreviousYearFile($userId, 'studentCard', $year);

        $BankCopy = UserFiles::where("userUid", $userId)->where('fileId', 'bankStatement')->where('year', $year)->first();
        // new logic for cash students require sponsorId and sponsorPayslip
        if ($userInfo->studentType == "Cash / Direct" &&  $year >= '2026') {
            $existingSponsorId = UserFiles::withTrashed()
                ->where('userUid', $userInfo->userUid)
                ->where('fileId', 'other_sponsor_id')
                ->where('year', $year)
                ->first();
            if (!$existingSponsorId) {
                UserFiles::create([
                    'userUid' => $userInfo->userUid,
                    'fileId' => 'other_sponsor_id',
                    'year' => $year,
                ]);
            }

            $existingSponsorPayslip = UserFiles::withTrashed()
                ->where('userUid', $userInfo->userUid)
                ->where('fileId', 'other_sponsor_payslip')
                ->where('year', $year)
                ->first();
            if (!$existingSponsorPayslip) {
                UserFiles::create([
                    'userUid' => $userInfo->userUid,
                    'fileId' => 'other_sponsor_payslip',
                    'year' => $year,
                ]);
            }
        }

        $BursaryCopy = UserFiles::where("userUid", $userId)->where('fileId', 'bursaryApproval')->where('year', $year)->first();
        $RegistrationCopy = UserFiles::where("userUid", $userId)->where('fileId', 'proofRegistration')->where('year', $year)->first();
        // new logic for cash students require sponsorId and sponsorPayslip
        $SponsorId = UserFiles::where("userUid", $userId)->where('fileId', 'other_sponsor_id')->where('year', $year)->first();
        $SponsorPayslip = UserFiles::where("userUid", $userId)->where('fileId', 'other_sponsor_payslip')->where('year', $year)->first();

        return view('application.step_4', ["lookups" => $lookups, "user" => $userInfo, "IdCopy" => $IdCopy, "StudentCopy" => $StudentCopy, "BankCopy" => $BankCopy, "SponsorId" => $SponsorId, "SponsorPayslip" => $SponsorPayslip, "BursaryCopy" => $BursaryCopy, "RegistrationCopy" => $RegistrationCopy, "new" => 1]);
    }

    public function check_uploaded_docs(Request $request)
    {
        // load current user
        /** @disregard P1009 */
        $userId = Auth::user()->id;
        //dd(Auth::user()->id);
        $year = Session::get('year') ?? Lookups::where('group_id', 4)->first()->lookup_name;

        $IdCopy = UserFiles::where("userUid", $userId)->where('fileId', 'copyID')->where('year', $year)->first();
        $StudentCopy = UserFiles::where("userUid", $userId)->where('fileId', 'studentCard')->where('year', $year)->first();
        $BankCopy = UserFiles::where("userUid", $userId)->where('fileId', 'bankStatement')->where('year', $year)->first();
        // new logic for cash students require sponsorId and sponsorPayslip
        // other_sponsor_id 
        // other_sponsor_payslip
        $SponsorId = UserFiles::where("userUid", $userId)->where('fileId', 'other_sponsor_id')->where('year', $year)->first();
        $SponsorPayslip = UserFiles::where("userUid", $userId)->where('fileId', 'other_sponsor_payslip')->where('year', $year)->first();

        $BursaryCopy = UserFiles::where("userUid", $userId)->where('fileId', 'bursaryApproval')->where('year', $year)->first();
        $RegistrationCopy = UserFiles::where("userUid", $userId)->where('fileId', 'proofRegistration')->where('year', $year)->first();

        return [$IdCopy, $StudentCopy, $BankCopy, $SponsorId, $SponsorPayslip, $BursaryCopy, $RegistrationCopy];
    }

    public function all_properties_online(Request $request)
    {
        $allRes = Residence::whereNull('status')->get();

        return $allRes;
    }

    public function nsfas_properties_online(Request $request)
    {
        $allRes = Residence::whereNull('status')->get();

        $inSelectedCash = InstitutionProperties::where("institution_id", $request->institStudy)->where('cash', '1')->pluck('property_id')->toArray();
        $inSelectedBursary = InstitutionProperties::where("institution_id", $request->institStudy)->where('bursary', '1')->pluck('property_id')->toArray();
        $inSelectedNsfas = InstitutionProperties::where("institution_id", $request->institStudy)->where('nsfas', '1')->pluck('property_id')->toArray();


        return [$allRes, $inSelectedCash, $inSelectedBursary, $inSelectedNsfas];
    }
}

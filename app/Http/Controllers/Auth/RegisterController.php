<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\UserFiles;
use Illuminate\Support\Facades\Storage;
use App\Models\ActionList;
use App\Models\Booking;
use App\Models\Lookups;
use App\Models\MailerList;
use Mail;
use App\Models\ApplicationToken;
use App\Models\ImportNSFASApproval;
use App\Models\ImportNSFASStudentAuditLog;
use Grosv\LaravelPasswordlessLogin\LoginUrl;
use App\Models\Residence;
use Auth;
use App\Models\StudentsInfo;
use App\Models\RoomTypes;
use DateTime;
use App\Models\BookingPayment;
use App\Jobs\SendApplicationThankYouMail;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'firstName' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        return User::create([
            'name' => $data['firstName'],
            'userFirstName' => ucwords(strtolower($data['firstName'])),
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'userLastname' => ucwords(strtolower($data['lastName'])),
            'userTitle' => $data['title'],
            'userDob' => $data['dob'],
            'userCell' => $data['cell_no'],
            'userEmail' => $data['email'],
            'userId' => $data['idNumber'],
            'userNationality' => $data['nationality'],
            'userPassport' => $data['passportNo'],
            'institutionStudy' => $data['institStudy'],
            'studentNo' => $data['studNumber'],
            // 'institutionCampus' => $data['institCampus'],
            'faculty' => $data['faculty'],
            'year' => $data['year'],
        ]);
    }

    public function generateRandomLongString($length = 24)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function generateRandomShortString($length = 5)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    protected function registration_step_one(Request $request)
    {

        $validated = $request->validate([
            'email' => 'email|required|unique:users',
        ]);

        $user = User::create([
            'name' => $request->firstName,
            'userDob' => $request->dob,
            'userEmail' => $request->email,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'userId' => $request->idNumber,
            'userNationality' => $request->nationality,
            'userPassport' => $request->passportNo,
        ]);

        $userInfo = StudentsInfo::create([
            'userUid' => $user->id,
            'userFirstName' => ucwords(strtolower($request->firstName)),
            'userLastname' => ucwords(strtolower($request->lastName)),
            'userTitle' => $request->title,
            'userDob' => $request->dob,
            'userCell' => $request->cell_no,
            'userCellCode' => $request->userCellCode,
            'userEmail' => $request->email,
            'userId' => $request->idNumber,
            'userNationality' => $request->nationality,
            'userPassport' => $request->passportNo,
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
            'rob_60_cancelled' => 0,
            'rob_55_lease' => 3,
            'rob_45_other' => 3,
            'app_source' => 1,
            'gender' => $request->gender,
            'userEmailAlt' => $request->email_alt,
        ]);

        ActionList::create([
            "userUid" => $user->id,
            "action" => "Registered " . $request->firstName . " as a new user",
            "actionUserUid" => $user->id,
            "actionUserDisplayName" => $request->firstName,
            "actionUserRole" => '0',
            "year" => $request->year,
        ]);

        $lookup_lease_dates = Lookups::where(['group_id' => '4', 'lookup_name' => $request->year])->first();

        //Booking data        
        $booking = Booking::create(["userId" => $user->id, "property" => $request->property, "roomType" => $request->roomType, "year" => $request->year, "room_rate" => "0", "lease_from_date" => $lookup_lease_dates->lease_from_date, "lease_from_time" => $lookup_lease_dates->lease_from_time, "lease_to_date" => $lookup_lease_dates->lease_to_date, "lease_to_time" => $lookup_lease_dates->lease_to_time]);


        $bookingYear = substr($booking->year, -2);
        $bookingPrefix = Residence::where('id',  $request->property)->pluck('building_prefix')->first();
        $id_booking = $booking->id;
        $placeholder = "";

        for ($i = 0; $i < 5 - strlen($id_booking); $i++) {
            $placeholder .= "0";
        }

        $bookingID = $placeholder . $id_booking;

        Booking::where('id', $booking->id)->update(['booking_code' => $bookingYear . $bookingPrefix . $bookingID]);



        //Link booking to User
        // User::where('id', $user->id)->update(["bookingRef" => $booking->id]);
        StudentsInfo::where('id', $userInfo->id)->update(["bookingRef" => $booking->id, "bookingId" => $booking->id]);

        if ($request->studentType == "NSFAS" || $request->studentType === "FUNZA" || $request->studentType == "GCRA" || $request->studentType == "MQA") {

            $nsfasStudentsApproval = ImportNSFASStudentAuditLog::where('student_number', $request->studNumber)->orderBy('created_at', 'desc')->first();

            if (!empty($nsfasStudentsApproval)) {

                $yearOfImport = ImportNSFASApproval::where('id', $nsfasStudentsApproval->importId)->first();
                $this_year = Lookups::where('group_id', '4')->first();

                if (!empty($nsfasStudentsApproval) && $yearOfImport->year == $this_year->lookup_name) {

                    // User::where('id', $user->id)->update(["rob_30_funds" => 2]);
                    StudentsInfo::where('id', $userInfo->id)->update(["rob_30_funds" => 2]);

                    UserFiles::create([
                        'userUid' => $user->id,
                        'fileId' => 'bursaryApproval',
                        'status' => 'Approved',
                        'year' => $this_year->lookup_name,
                    ]);

                    ActionList::create([
                        "userUid" => $user->id,
                        "action" => "NSFAS Approval has been Approved via import",
                        "actionUserUid" => $user->id,
                        "actionUserDisplayName" => $userInfo->userFirstName,
                        "actionUserRole" => $userInfo->userRole,
                        "year" => $this_year->lookup_name,
                    ]);
                }
            }
        }


        $userToken = ApplicationToken::create(["userUid" => $user->id, "applicationToken" => $this->generateRandomLongString(), 'verified' => 0, 'security_token' => $this->generateRandomShortString()]);

        $generator = new LoginUrl($user);
        $generator->setRedirectUrl('/verify-password');
        $url = $generator->generate();

        $userUidData = User::where('id', $user->id)->first();
        $this->dispatch(new SendApplicationThankYouMail($userUidData, $userToken->security_token, $url));

        $bookinfRefNo = Booking::where('id', $booking->id)->first();

        // $message_replace['bookingRef'] = $bookinfRefNo->booking_code;
        // $message_replace['name'] = $request->firstName;
        // $message_replace['security_token'] = $userToken->security_token;
        // $message_replace['application_link'] = "<a href='".$url."' target='_blank'>";
        // $message_replace['close_application_link']="</a>";

        // $mailerForMessage = MailerList::where("mailer_name", "Application Thank You")->first();

        // if($mailerForMessage->status == '1'){

        //     $mailerMessage = $mailerForMessage->message;

        //     $newMessage = str_replace("[application_number]", "#".$message_replace['bookingRef'], $mailerMessage);
        //     $newMessage = str_replace("[name]", $message_replace['name'], $newMessage);
        //     $newMessage = str_replace("[security_code]", $message_replace['security_token'], $newMessage);
        //     $newMessage = str_replace("[application_link]", $message_replace['application_link'], $newMessage);
        //     $newMessage = str_replace("[close_application_link]", $message_replace['close_application_link'], $newMessage);

        //     $data['name'] = $request->firstName;
        //     $data['email'] = $request->email;
        //     $data['messageForMail'] = $newMessage;
        //     $data['subject'] = str_replace("[application_number]", $message_replace['bookingRef'], $mailerForMessage->subject);



        //     $allowedMailsCC = Lookups::where('group_id', '11')->pluck('lookup_name')->first();
        //     $data['cc'] = explode(";", $allowedMailsCC);

        //     $allowedMails = Lookups::where('group_id', '10')->pluck('lookup_name')->first();

        //     if(!empty($allowedMails)){

        //         $mailArr = explode(";", $allowedMails);

        //         if(in_array(explode("@", $data['email'])[1], $mailArr)){
        //             $data['email'] = $user->email;
        //         }else{
        //             $data['email'] = "conurbandigital@gmail.com";
        //         }
        //     }else{
        //         $data['email'] = $user->email;
        //     }

        //     // if(explode("@", $data['email'])[1] == "conurban.co.za"){
        //     //     $data['email'] = $user->email;
        //     // }else{
        //     //     $data['email'] = "conurbandigital@gmail.com";
        //     // }

        //     Mail::send('email.yellow_back', $data, function($message) use ($data) {
        //         // $message->to($data['email'], $data['name'])->subject
        //         //     ('Campus Africa User Application');
        //         $message->to($data['email'])->subject($data['subject']);
        //         $message->bcc($data['cc']);
        //         // $message->cc(['conurbandigital@gmail.com', 'roger@whitehart.co.za']);
        //         $message->from('noreply@campusafrica.co.za','Campus Africa');
        //     });
        // }



        $admin_fee = Lookups::where('group_id', '8')->first();
        $lookup_lease_dates = Lookups::where('group_id', '4')->first();

        $jsonDataResidence = json_decode(file_get_contents('https://www.campusafrica.co.za/wp-json/wp/v2/residences'));
        $jsonDataRooms = json_decode(file_get_contents('https://www.campusafrica.co.za/wp-json/wp/v2/rooms?per_page=100'));

        $bookingPrice = 0;
        $residenceId = 0;
        $roomTypeData = RoomTypes::where('id', $bookinfRefNo->roomType)->first();
        $bookinfRefNo->property = Residence::where('id', $bookinfRefNo->property)->pluck('residence')->first();
        $roomTypeId = "";
        $is_nsfas = false;

        if ($roomTypeData && $roomTypeData->room_map_slug) {
            //
            $roomTypeId = $roomTypeData->room_map_slug;
        }
        if ($roomTypeData->use_nsfas == 1) {
            $is_nsfas = true;
        }
        $deposit = 0;
        $userInfo = StudentsInfo::where('userUid', $bookinfRefNo->userId)->orderBy('year', 'desc')->first();

        if (!empty($userInfo)) {

            foreach ($jsonDataResidence as $residence) {
                if ($residence->title->rendered == $bookinfRefNo->property) {
                    $bookingPrice = $residence->acf->booking_fee;
                    $residenceId = $residence->id;
                }

                foreach ($jsonDataRooms as $room) {
                    if ($residenceId == $room->acf->residence) {
                        if ($room->id == $roomTypeId) {
                            $deposit = $room->acf->price_from;
                            $appliesTo = $room->acf->booking_fee_applies_to;

                            if ($room->acf->booking_fee_override !== "") {
                                $bookingPrice = $room->acf->booking_fee_override;
                            }

                            if ($userInfo->studentType === "NSFAS" || $userInfo->studentType === "NRF" || $userInfo->studentType === "FUNZA" || $userInfo->studentType === "GCRA" || $userInfo->studentType === "MQA") {
                                if (!in_array("NSFAS Students", $appliesTo)) {
                                    $bookingPrice = 0;
                                }
                            }

                            if ($userInfo->studentType === "Bursary") {
                                if (!in_array("Bursary Students", $appliesTo)) {
                                    $bookingPrice = 0;
                                }
                            }

                            if ($userInfo->studentType === "Cash / Direct") {
                                if (!in_array("Cash Students", $appliesTo)) {
                                    $bookingPrice = 0;
                                }
                            }
                        }
                    }
                }
            }

            if ($roomTypeData && $roomTypeData->pull_price == null) {
                $bookingPrice = $roomTypeData->booking_price;
                $deposit = $roomTypeData->rent_price;

                if ($userInfo->studentType == "NSFAS" || $request->studentType === "FUNZA" || $userInfo->studentType == "GCRA" || $userInfo->studentType == "MQA") {
                    if ($roomTypeData->apply_nsfas == null) {
                        $bookingPrice = 0;
                    }
                }

                if ($userInfo->studentType == "Bursary") {
                    if ($roomTypeData->apply_bursary == null) {
                        $bookingPrice = 0;
                    }
                }

                if ($userInfo->studentType == "Cash / Direct") {
                    if ($roomTypeData->apply_cash == null) {
                        $bookingPrice = 0;
                    }
                }
            }

            if ($bookingPrice == "0") {

                $this_year = Lookups::where('group_id', '4')->first();
                $this_year = $this_year->lookup_name;

                StudentsInfo::where('userUid', $user->id)->where('year', $this_year)->update(["rob_10_fee" => 2]);

                UserFiles::create([
                    'userUid' => $user->id,
                    'fileId' => 'proof_of_payment',
                    'status' => 'Approved',
                    'year' => $this_year,
                ]);

                $actionFileData['userUid'] = $user->id;
                $actionFileData['actionUserUid'] = $user->id;
                $actionFileData['actionUserDisplayName'] = $userInfo->userFirstName;
                $actionFileData['actionUserRole'] = 0;
                $actionFileData['year'] = $this_year;
                $actionFileData['action'] = "Booking Fee is R0";

                ActionList::create($actionFileData);
            }

            $diff = 0;
            for ($i = new DateTime($lookup_lease_dates['lease_from_date']); $i <= new DateTime($lookup_lease_dates['lease_to_date']); $i->modify('+1 month')) {
                $diff++;
            }
            $yearly_rent = floatval($deposit) * floatval($diff);

            $institution = Lookups::where('id', $userInfo->institutionStudy)->first();
            $funded = floatval($institution->nsfas_amount) * floatval($diff);

            $topup = floatval($yearly_rent) - floatval($funded);
            $monthlyFunded = $institution->nsfas_amount;
            $nsfas_rate = null;


            if (($userInfo->studentType == "Cash / Direct") || ($userInfo->studentType == "Bursary" && $userInfo->nsfas_bursar !== 1)) {
                $admin_fee = '1000';
                $deposit = $monthly = $monthlyFunded = $deposit;
                $funded = $yearly_rent;
                $topup = '0';
            } else {
                $monthly = $deposit;
                $deposit = 0;
                $admin_fee = 0;

                if ($is_nsfas === true) {
                    $monthly = $monthlyFunded;
                    $deposit = 0;
                    $admin_fee = 0;
                    $nsfas_rate = 1;
                    $yearly_rent = $funded;
                    $topup = 0;
                    $nsfas_rate = 1;
                }
            }

            BookingPayment::create([
                "bookingId" => $bookinfRefNo->id,
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

        return $userToken->applicationToken;
    }

    protected function registration_step_one_update(Request $request)
    {
        return User::where('id', $request->userUid)->update([
            'name' => $request->firstName,
            'userFirstName' => ucwords(strtolower($request->firstName)),
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'userLastname' => ucwords(strtolower($request->lastName)),
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
            'gender' => $request->gender,
        ]);
    }

    protected function registration_step_two(Request $request)
    {
        return User::where('id', $request->userUid)->update([
            'sponFirstName' => $request->pFirstName,
            'sponLastName' => $request->lastName,
            'sponCell' => $request->cell,
            'sponEmail' => $request->email,
            'sponId' => $request->idNo,
            'burCompanyName' => $request->companyName,
            'burRegNo' => $request->regNo,
            'burContactPerson' => $request->contactPerson,
            'burContactNo' => $request->contactNo,
            'burEmail' => $request->emailAddress,
        ]);
    }

    protected function registration_step_three(Request $request)
    {
        return User::where('id', $request->userUid)->update([
            'kinFirstName' => $request->firstName,
            'kinLastName' => $request->lastName,
            'kinTitle' => $request->title,
            'kinId' => $request->idNo,
            'kinAddressStr' => $request->address1,
            'kinAddressSuburb' => $request->address2,
            'kinAddressCity' => $request->addressCity,
            'kinAddressPost' => $request->addressPostal,
            'kinRelationship' => $request->relationship,
            'kinTelNo' => $request->telNo,
            'kinCellNo' => $request->celNo,
            'kinWorkNo' => $request->workNo,
            'kinEmail' => $request->email,
        ]);
    }

    protected function fileUpload(Request $request)
    {
        $userUid = $request->userUid;
        $fileType = $request->fileType;

        if ($request->hasFile('file')) {

            if ($request->file('file')->isValid()) {

                $filenameWithExt  = $request->file('file')->getClientOriginalName();
                $fileName = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                $extension = $request->file('file')->extension();
                $fileNameToStore = $fileName . '_' . time() . '.' . $extension;

                $request->file('file')->storeAs('public/files/' . $userUid . '/', $fileNameToStore);

                $fileUrl = 'files/' . $userUid . '/' . $fileNameToStore;

                $this_year = Lookups::where('group_id', '4')->first();
                $this_year = $this_year->lookup_name;

                UserFiles::create([
                    'fileId' => $fileType,
                    'userUid' => $userUid,
                    'fileName' => $fileNameToStore,
                    'fileUrl' => $fileUrl,
                    'status' => 'Uploaded',
                    'year' => $this_year,
                ]);

                if ($fileType == "copyID") {
                    $typeName = "Copy of ID Document";
                } elseif ($fileType == "studentCard") {
                    $typeName = "Copy of Student Card";
                } elseif ($fileType == "bankStatement") {
                    $typeName = "Bank Statements";
                } elseif ($fileType == "proofRegistration") {
                    $typeName = "Proof of Registration";
                } elseif ($fileType == "proof_of_payment") {
                    $typeName = "Booking Fee Proof of Payment";
                }

                $user = User::where('id', $userUid)->first();

                ActionList::create([
                    "userUid" => $userUid,
                    "action" => $typeName . " Uploaded for review",
                    "actionUserUid" => $userUid,
                    "actionUserDisplayName" => $user->userFirstName,
                    "actionUserRole" => "0",
                    "year" => $this_year,
                ]);
            }
        }


        return "done";
    }
}

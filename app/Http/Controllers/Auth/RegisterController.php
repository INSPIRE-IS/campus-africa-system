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
            'userFirstName' => $data['firstName'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'userLastname' => $data['lastName'],
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

    public function generateRandomLongString($length = 24) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function generateRandomShortString($length = 5) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    protected function registration_step_one(Request $request){

        $user = User::create([
            'name' => $request->firstName,
            'userFirstName' => $request->firstName,
            'userLastname' => $request->lastName,
            'userTitle' => $request->title,
            'userDob' => $request->dob,
            'userCell' => $request->cell_no,
            'userCellCode' => $request->userCellCode,
            'userEmail' => $request->email,
            'email' => $request->email,
            'password' => Hash::make($request->password),
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
            'rob_55_lease' => 3,
            'rob_45_other' => 3,
            'app_source' => 1,
        ]);

        ActionList::create([
            "userUid" => $user->id,
            "action" => "Registered ".$request->firstName." as a new user",
            "actionUserUid" => $user->id,
            "actionUserDisplayName" => $request->firstName,
            "actionUserRole" => '0',
        ]);

        $lookup_lease_dates = Lookups::where(['group_id' => '4', 'lookup_name' => $request->year])->first();

        //Booking data        
        $booking = Booking::create(["userId" => $user->id, "property" => $request->property, "roomType" => $request->roomType, "year" => $request->year, "room_rate" => "0", "lease_from_date" => $lookup_lease_dates->lease_from_date, "lease_from_time" => $lookup_lease_dates->lease_from_time, "lease_to_date" => $lookup_lease_dates->lease_to_date, "lease_to_time" => $lookup_lease_dates->lease_to_time]);
        

        $bookingYear = substr($booking->year, -2);
        $bookingPrefix = Residence::where('id',  $request->property)->pluck('building_prefix')->first();
        $id_booking = $booking->id;
        $placeholder = "";

        for($i = 0; $i < 5 - strlen($id_booking); $i++){
            $placeholder .= "0";
        }

        $bookingID = $placeholder.$id_booking;

        Booking::where('id', $booking->id)->update(['booking_code' => $bookingYear.$bookingPrefix.$bookingID]);

        
    
        //Link booking to User
        User::where('id', $user->id)->update(["bookingRef" => $booking->id]);

        if($request->studentType == "NSFAS"){

            $nsfasStudentsApproval = ImportNSFASStudentAuditLog::where('student_number', $request->studNumber)->orderBy('created_at', 'desc')->first();

            if(!empty($nsfasStudentsApproval)){

                $yearOfImport = ImportNSFASApproval::where('id', $nsfasStudentsApproval->importId)->first();
                $this_year = Lookups::where('group_id', '4')->first();

                if(!empty($nsfasStudentsApproval) && $yearOfImport->year == $this_year->lookup_name){
                    
                    User::where('id', $user->id)->update(["rob_30_funds" => 2]);

                    UserFiles::create([
                        'userUid' => $user->id,
                        'fileId' => 'bursaryApproval',
                        'status' => 'Approved'
                    ]);
    
                    ActionList::create([
                        "userUid" => $user->id,
                        "action" => "NSFAS Approval has been Approved via import",
                        "actionUserUid" => $user->id,
                        "actionUserDisplayName" => $user->userFirstName,
                        "actionUserRole" => $user->userRole,
                    ]);
                }


            }
        }

        $userToken = ApplicationToken::create(["userUid" => $user->id, "applicationToken" => $this->generateRandomLongString(), 'verified' => 0, 'security_token' => $this->generateRandomShortString()]);

        $generator = new LoginUrl($user);
        $generator->setRedirectUrl('/verify-password');
        $url = $generator->generate();

        $bookinfRefNo = Booking::where('id', $booking->id)->first();

        $message_replace['bookingRef'] = $bookinfRefNo->booking_code;
        $message_replace['name'] = $request->firstName;
        $message_replace['security_token'] = $userToken->security_token;
        $message_replace['application_link'] = "<a href='".$url."' target='_blank'>";
        $message_replace['close_application_link']="</a>";

        $mailerForMessage = MailerList::where("mailer_name", "Application Thank You")->first();

        if($mailerForMessage->status == '1'){
            
            $mailerMessage = $mailerForMessage->message;
    
            $newMessage = str_replace("[application_number]", "#".$message_replace['bookingRef'], $mailerMessage);
            $newMessage = str_replace("[name]", $message_replace['name'], $newMessage);
            $newMessage = str_replace("[security_code]", $message_replace['security_token'], $newMessage);
            $newMessage = str_replace("[application_link]", $message_replace['application_link'], $newMessage);
            $newMessage = str_replace("[close_application_link]", $message_replace['close_application_link'], $newMessage);
    
            $data['name'] = $request->firstName;
            $data['email'] = $request->email;
            $data['messageForMail'] = $newMessage;
            $data['subject'] = str_replace("[application_number]", $message_replace['bookingRef'], $mailerForMessage->subject);

            $allowedMails = Lookups::where('group_id', '10')->pluck('lookup_name')->first();
            $mailArr = explode(";", $allowedMails);

            $allowedMailsCC = Lookups::where('group_id', '11')->pluck('lookup_name')->first();
            $data['cc'] = explode(";", $allowedMailsCC);

            if(in_array(explode("@", $data['email'])[1], $mailArr)){
                $data['email'] = $user->email;
            }else{
                $data['email'] = "conurbandigital@gmail.com";
            }
    
            // if(explode("@", $data['email'])[1] == "conurban.co.za"){
            //     $data['email'] = $user->email;
            // }else{
            //     $data['email'] = "conurbandigital@gmail.com";
            // }
    
            Mail::send('email.yellow_back', $data, function($message) use ($data) {
                // $message->to($data['email'], $data['name'])->subject
                //     ('Campus Africa User Application');
                $message->to($data['email'])->subject($data['subject']);
                $message->cc($data['cc']);
                // $message->cc(['conurbandigital@gmail.com', 'roger@whitehart.co.za']);
                $message->from('noreply@campusafrica.co.za','Campus Africa');
            });
        }

        return $userToken->applicationToken;
    }

    protected function registration_step_one_update(Request $request){
        return User::where('id', $request->userUid)->update([
            'name' => $request->firstName,
            'userFirstName' => $request->firstName,
            'email' => $request->email,
            'password' => Hash::make($request->password),
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
        ]);
    }

    protected function registration_step_two (Request $request){
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

    protected function registration_step_three (Request $request){
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

    protected function fileUpload(Request $request){
        $userUid = $request->userUid;
        $fileType = $request->fileType;

        if ($request->hasFile('file')) {
            
            if ($request->file('file')->isValid()) {

                $filenameWithExt  = $request->file('file')->getClientOriginalName();
                $fileName = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                $extension = $request->file('file')->extension();
                $fileNameToStore = $fileName. '_'. time().'.'.$extension;

                $request->file('file')->storeAs('public/files/'.$userUid.'/', $fileNameToStore);

                $fileUrl = 'files/'.$userUid.'/'. $fileNameToStore;

                UserFiles::create([
                    'fileId' => $fileType,
                    'userUid' => $userUid,
                    'fileName' => $fileNameToStore,
                    'fileUrl' => $fileUrl,
                    'status' => 'Uploaded',
                ]);

                if($fileType == "copyID"){
                    $typeName = "Copy of ID Document";
                }elseif($fileType == "studentCard"){
                    $typeName = "Copy of Student Card";
                }elseif($fileType == "bankStatement"){
                    $typeName = "Bank Statements";
                }elseif($fileType == "proofRegistration"){
                    $typeName = "Proof of Registration";
                }elseif($fileType == "proof_of_payment"){
                    $typeName = "Booking Fee Proof of Payment";
                }

                $user = User::where('id', $userUid)->first();

                ActionList::create([
                    "userUid" => $userUid,
                    "action" => $typeName." Uploaded for review",
                    "actionUserUid" => $userUid,
                    "actionUserDisplayName" => $user->userFirstName,
                    "actionUserRole" => "0",
                ]);

            }
        }


        return "done";
    }
}

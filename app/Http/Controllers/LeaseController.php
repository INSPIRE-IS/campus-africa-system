<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserLeaseToken;
use App\Models\User;
use App\Models\UserFiles;
use App\Models\Booking;
use App\Models\Property;
use App\Models\Residence;
use App\Models\Lookups;
use PDF;
use Auth;
use Mail;
use App\Models\MailerList;
use Grosv\LaravelPasswordlessLogin\LoginUrl;
use App\Models\StudentsInfo;
use App\Models\MDAInfo;
use App\Models\RoomTypes;
use DateTime;
use App\Models\ActionList;
use App\Models\BookingPayment;
use App\Jobs\SendResendLeaseMail;
use Illuminate\Support\Facades\Log;

class LeaseController extends Controller
{
    public function view_sign_lease(Request $request, $lease_link_url)
    {

        $lease_link = UserLeaseToken::where('leaseToken', $lease_link_url)->where('active', '1')->first();

        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        if ($lease_link && !empty($lease_link)) {
            // $user = User::where('id', $lease_link->userUid)->first();
            $user = StudentsInfo::where('userUid', $lease_link->userUid)->where('year', $this_year)->first();
            $file = UserFiles::where('userUid', $lease_link->userUid)->where('fileId', 'moveIn')->where('year', $this_year)->first();

            // $data = User::where('id', $lease_link->userUid)->first();
            $data = StudentsInfo::where('userUid', $lease_link->userUid)->where('year', $this_year)->first();
            $booking = Booking::where('userId', $lease_link->userUid)->where('year', $this_year)->first();
            $propertyData = Property::where('id', $booking->property_data_id)->first();
            $residence = Residence::where('id', $propertyData->residence)->first();
            $leaseAdminFee = Lookups::where('group_id', '8')->first();
            $owner = storage_path() . '/leases/juanita-cronje.jpg';

            $paymentBooking = BookingPayment::where('bookingId', $booking->id)->first();

            $pdf = view('PDF.leasesForScreen', compact('data', 'propertyData', 'residence', 'booking', 'leaseAdminFee', 'owner', 'paymentBooking'));

            return view('outSession.signLease', ["headerTitle" => "Sign Your Lease", "userCustomId" => $lease_link_url, "user" => $user, "file" => $file, "pdf" => $pdf, "active" => 0]);
        } else {
            return redirect('/dashboard');
        }
    }

    public function save_signed_lease(Request $request, $lease_link)
    {

        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        $lease_link = UserLeaseToken::where('leaseToken', $lease_link)->where('active', '1')->first();
        // $user = User::where('id', $lease_link->userUid)->first();
        $user = StudentsInfo::where('userUid', $lease_link->userUid)->where('year', $this_year)->first();
        $booking = Booking::where('userId', $lease_link->userUid)->where('year', $this_year)->first();

        // get the base64 string
        $data = $request->signature_string;

        // clean any symbols to decode
        list($type, $data) = explode(';', $data);
        list(, $extension) = explode('/', $type);
        list(, $data)      = explode(',', $data);

        // decode the string to the correct format
        $data = base64_decode($data);

        if ($user->userId == "") {
            $userIdentification = $user->userPassport;
        } else {
            $userIdentification = $user->userId;
        }


        // generate a random name
        $new_name = $userIdentification . '_' . strtotime("now") . '_' . substr(str_shuffle(str_repeat($x = '0123456789abcdefghijklmnopqrstuvwxyz', ceil(20 / strlen($x)))), 1, 20) . '.' . $extension;


        file_put_contents(storage_path() . '/app/public/files/' . $userIdentification . '/' . $new_name, $data);

        //generate the signed lease

        // retreive all records from db
        // $data = User::where('id', $user->userUid)->first();
        $data = StudentsInfo::where('userUid', $lease_link->userUid)->where('year', $this_year)->first();
        $booking = Booking::where('userId', $user->userUid)->where('year', $this_year)->first();
        $propertyData = Property::where('id', $booking->property_data_id)->first();
        $residence = Residence::where('id', $propertyData->residence)->first();
        $leaseAdminFee = Lookups::where('group_id', '8')->first();
        $signed_at = $request->signed_at;
        $signature = 'storage/files/' . $userIdentification . '/' . $new_name;
        $owner = 'storage/leases/juanita-cronje.jpg';

        $paymentBooking = BookingPayment::where('bookingId', $booking->id)->first();

        // share data to view
        $pdf = PDF::loadView('PDF.leaseSign', compact('data', 'propertyData', 'residence', 'booking', 'leaseAdminFee', 'signed_at', 'signature', 'owner', 'paymentBooking'));

        // download PDF file with download method
        // return $pdf->download('lease.pdf');

        $bookingYear = substr($booking->year, -2);
        $bookingPrefix = Residence::where('id', $booking->property)->pluck('building_prefix')->first();

        $fileNameToSave = $bookingYear . $bookingPrefix . "_" . $booking->bed_code . "_" . $user->userLastname . "_" . $user->userFirstName . ".pdf";

        $content = $pdf->download($fileNameToSave)->getOriginalContent();
        // $user = User::where('id', $user->id)->first();
        $user = StudentsInfo::where('userUid', $lease_link->userUid)->where('year', $this_year)->first();

        \Storage::put('public/files/' . $userIdentification . '/' . $fileNameToSave, $content);

        //Data to update
        $data = array(
            'fileName' => $fileNameToSave,
            'fileUrl' => 'files/' . $userIdentification . '/' . $fileNameToSave,
            'status' => 'Uploaded',
        );

        UserFiles::where('fileId', 'moveIn')->where('userUid', $user->userUid)->where('year', $this_year)->update($data);

        // User::where('id', $user->id)->update(['rob_55_lease' => 1]);
        StudentsInfo::where('userUid', $lease_link->userUid)->where('year', $this_year)->update(['rob_55_lease' => 1]);

        //Update Token to signed & inactive
        UserLeaseToken::where('leaseToken', $lease_link->leaseToken)->update(['active' => 0, 'signed' => 1, 'signature_url' => $new_name]);

        return 'Completed';
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

    public function resendLease(Request $request)
    {

        $prevLeaseToken = UserLeaseToken::where("userUid", $request->userId)->where("active", 1)->where("signed", 0)->get();

        if (!empty($prevLeaseToken)) {
            foreach ($prevLeaseToken as $token) {
                UserLeaseToken::where('id', $token->id)->update(["active" => 0]);
            }
        }

        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        // Create Lease Link
        $leaseToken = UserLeaseToken::create(["userUid" => $request->userId, "leaseToken" => $this->generateRandomLongString(), "active" => 1, "signed" => 0]);

        $leaseOpenTag = "<a href='https://" . $_SERVER['HTTP_HOST'] . "/sign-lease/" . $leaseToken->leaseToken . "' target='_blank'>";

        $userUidData = User::where('id', $request->userId)->first();
        try {
            Log::info('Sending email to ');
            $this->dispatch(new SendResendLeaseMail($userUidData, $this->generateRandomLongString(), $leaseOpenTag));
        } catch (\Exception $e) {
            Log::error('Failed to dispatch SendResendLeaseMail job', [
                'error' => $e->getMessage(),
                'userUidData' => $userUidData,
            ]);
        }


        // $userForLease = StudentsInfo::where('userUid', $request->userId)->where('year', $this_year)->first();
        // $booking = Booking::where('userId', $request->userId)->where('year', $this_year)->first();

        // $message_replace['booking_application'] = "#".$booking->booking_code;
        // $message_replace['name'] = $userForLease->userFirstName;
        // $message_replace['lease_link'] = "<a href='https://".$_SERVER['HTTP_HOST']."/sign-lease/".$leaseToken->leaseToken."' target='_blank'>";
        // $message_replace['close_lease_link']="</a>";

        // $mailerForMessage = MailerList::where("mailer_name", "Application Approved")->first();

        // if($mailerForMessage->status == '1'){

        //     $mailerMessage = $mailerForMessage->message;

        //     $newMessage = str_replace("[booking_application]", $message_replace['booking_application'], $mailerMessage);
        //     $newMessage = str_replace("[name]", $message_replace['name'], $newMessage);
        //     $newMessage = str_replace("[lease_link]", $message_replace['lease_link'], $newMessage);
        //     $newMessage = str_replace("[close_lease_link]", $message_replace['close_lease_link'], $newMessage);

        //     $data['messageForMail'] = $newMessage;
        //     $data['email'] = $userForLease->userEmail;
        //     $data['name'] = $userForLease->userFirstName;
        //     $data['subject'] = str_replace("[application_number]", $booking->booking_code, $mailerForMessage->subject);



        //     $allowedMailsCC = Lookups::where('group_id', '11')->pluck('lookup_name')->first();
        //     $data['cc'] = explode(";", $allowedMailsCC);

        //     $allowedMails = Lookups::where('group_id', '10')->pluck('lookup_name')->first();

        //     if(!empty($allowedMails)){

        //         $mailArr = explode(";", $allowedMails);

        //         if(in_array(explode("@", $data['email'])[1], $mailArr)){
        //             $data['email'] = $userForLease->userEmail;
        //         }else{
        //             $data['email'] = "conurbandigital@gmail.com";
        //         }
        //     }else{
        //         $data['email'] = $userForLease->userEmail;
        //     }

        //     // if(explode("@", $data['email'])[1] == "conurban.co.za"){
        //     //     $data['email'] = $userForLease->userEmail;
        //     // }else{
        //     //     $data['email'] = "conurbandigital@gmail.com";
        //     // }

        //     Mail::send('email.green_back', $data, function($message) use ($data) {
        //         $message->to($data['email'])->subject($data['subject']);
        //         $message->bcc($data['cc']);
        //         // $message->cc(['conurbandigital@gmail.com', 'roger@whitehart.co.za']);
        //         $message->from('noreply@campusafrica.co.za','Campus Africa');
        //     });
        // }

    }

    public function mda_tenant_save(Request $request)
    {

        // //get login details
        // $curl = curl_init();

        // curl_setopt_array($curl, array(
        // CURLOPT_URL => 'https://api.mdapropsys.com/api/v2/token',
        // CURLOPT_RETURNTRANSFER => true,
        // CURLOPT_ENCODING => '',
        // CURLOPT_MAXREDIRS => 10,
        // CURLOPT_TIMEOUT => 0,
        // CURLOPT_FOLLOWLOCATION => true,
        // CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        // CURLOPT_CUSTOMREQUEST => 'GET',
        // CURLOPT_POSTFIELDS => 'grant_type=password&username=mri.api%40conurban.co.za&password=3ub4%5E2t2Y9PhPTEq',
        // CURLOPT_HTTPHEADER => array(
        //     'cache-control: no-cache',
        //     'Authorization: Basic your-API-key',
        //     'Content-Type: application/x-www-form-urlencoded',
        //     'Cookie: NSC_ESNS=0a80b654-6595-1303-9678-00505691daed_3897103846_3196003446_00000000000176021283'
        // ),
        // ));

        // $response = curl_exec($curl);

        // curl_close($curl);

        // //Get Users Information
        // $this_year = Lookups::where('group_id', '4')->first();
        // $this_year = $this_year->lookup_name;
        // $userForLease = StudentsInfo::where('userUid', $request->userUid)->where('year', $this_year)->first();
        // $bookingForLease = Booking::where('id', $userForLease->bookingId)->first();
        // $bookingProperty = Residence::where('id', $bookingForLease->property)->first();
        // $lookupsInstit = Lookups::where('id', $userForLease->institutionStudy)->first();

        // $propertyData = Property::where('id', $bookingForLease->property_data_id)->first();

        // //login Token
        // $loginToken = json_decode($response)->access_token;

        // //MDA Get Bookable Rooms for property
        // $curl = curl_init();

        // curl_setopt_array($curl, array(
        // CURLOPT_URL => 'https://api.mdapropsys.com/api/v2/PropertyRentableUnits/GetPropertyRentableUnitsListForProperty/'.$bookingProperty->mda_id,
        // CURLOPT_RETURNTRANSFER => true,
        // CURLOPT_ENCODING => '',
        // CURLOPT_MAXREDIRS => 10,
        // CURLOPT_TIMEOUT => 0,
        // CURLOPT_FOLLOWLOCATION => true,
        // CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        // CURLOPT_CUSTOMREQUEST => 'GET',
        // CURLOPT_HTTPHEADER => array(
        //     'DatabaseIdentifier: CC84465A-301E-4C19-AFA0-1939AB658425',
        //     'Authorization: Bearer '.$loginToken,
        //     'Cookie: NSC_ESNS=0a8b0f78-699b-1303-9678-00505691daed_3807934398_3033416358_00000000000176129249'
        // ),
        // ));

        // $response = curl_exec($curl);
        // curl_close($curl);

        // $mdaPropertyList = json_decode($response);
        // $RentableId = "";

        // foreach($mdaPropertyList as $list){
        //     $bedCode = explode("-", $list->Description);

        //     $int = (int) filter_var($bedCode[0], FILTER_SANITIZE_NUMBER_INT);


        //     if($propertyData->unit == $int){
        //         $bedUnit = explode($int, trim($bedCode[0]));
        //         if($propertyData->bed == null){
        //             $propertyData->bed = $propertyData->room;
        //         }

        //         if(strtoupper($bedUnit[1]) == strtoupper($propertyData->bed)){
        //             $RentableId = $list->Id;
        //             break;
        //         }
        //     }

        // }




        // //Create & SAVE Tenant

        // $studentType = "";
        // $lesseeName = "";
        // $listOrTradingName = "";

        // if($userForLease->studentType == "Cash / Direct"){
        //     $studentType = "Cash";
        //     $lesseeName = $listOrTradingName =  '"'.$userForLease->userFirstName.' '.$userForLease->userLastname.' | '.$studentType.'"';
        // }elseif($userForLease->studentType == "Bursary"){
        //     $studentType = $userForLease->studentType;
        //     $lesseeName = '"'.$userForLease->burContactPerson.' | '.$studentType.'"';
        //     $listOrTradingName = '"'.$userForLease->burCompanyName.' | '.$studentType.'"';
        // }else{
        //     $studentType = $userForLease->studentType;
        //     $lesseeName = $listOrTradingName = '"'.$userForLease->userFirstName.' '.$userForLease->userLastname.' | '.$studentType.'"';
        // }

        // $tenatntId = $userForLease->mda_tenant_id;

        // if($tenatntId == null){
        //     $curl = curl_init();

        //     curl_setopt_array($curl, array(
        //     CURLOPT_URL => 'https://api.mdapropsys.com/api/v2/Tenants/Save/',
        //     CURLOPT_RETURNTRANSFER => true,
        //     CURLOPT_ENCODING => '',
        //     CURLOPT_MAXREDIRS => 10,
        //     CURLOPT_TIMEOUT => 0,
        //     CURLOPT_FOLLOWLOCATION => true,
        //     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        //     CURLOPT_CUSTOMREQUEST => 'POST',
        //     CURLOPT_POSTFIELDS =>'{
        //         "Dto" : {
        //             "PropertyId" : '.$bookingProperty->mda_id.',
        //             "SendSmsOnSave" : false, 
        //             "CorrespondenceTypePref" : 3, 
        //             "StatementsTo" : 2, 
        //             "StatementDeliveryMethod" : 4, 
        //             "LesseeName" : '.$lesseeName.', 
        //             "ListOrTradingAsName" : '.$listOrTradingName.', 
        //             "GroupTenantId" : 3, 
        //             "ControlOfficeId" : 1, 
        //             "PaymentMethod" : 1, 
        //             "LesseeSalutation" : "'.$userForLease->userTitle.'", 
        //             "GeneralContactEmailNos" : "'.$userForLease->userEmail.'", 
        //             "GeneralContactTelephoneNos" : "'.$userForLease->studentNo.' | '.$lookupsInstit->lookup_name.'", 
        //             "GeneralContactName" : "'.$userForLease->userFirstName.' '.$userForLease->userLastname.' | '.$studentType.'", 
        //             "GeneralContactMobileNos" : "'.str_replace("+", "", $userForLease->userCellCode).''.$userForLease->userCell.'",
        //             "CustomerServiceContactUserId": 4
        //         },
        //         "ConfirmationResponses" : {}
        //     }',
        //     CURLOPT_HTTPHEADER => array(
        //         'DatabaseIdentifier: CC84465A-301E-4C19-AFA0-1939AB658425',
        //         'Authorization: Bearer '.$loginToken,
        //         'Content-Type: application/json'
        //     ),
        //     ));

        //     $response = curl_exec($curl);
        //     curl_close($curl);

        //     $tenatntId = json_decode($response)->TenantId;
        // }


        // //get rental prices
        // $jsonDataResidence = json_decode(file_get_contents('https://www.campusafrica.co.za/wp-json/wp/v2/residences'));
        // $jsonDataRooms = json_decode(file_get_contents('https://www.campusafrica.co.za/wp-json/wp/v2/rooms?per_page=100'));

        // $roomTypeData = RoomTypes::where('id', $bookingForLease->roomType)->first();
        // $roomTypeId = $roomTypeData->room_map_slug;
        // $deposit = 0;
        // $residenceId = 0;

        // foreach($jsonDataResidence as $residence){
        //     if($residence->title->rendered == $bookingProperty->residence){
        //         $residenceId = $residence->id;
        //     }

        //     foreach($jsonDataRooms as $room){
        //         if($residenceId == $room->acf->residence){    
        //             if($room->id == $roomTypeId){
        //                 $deposit = $room->acf->price_from;
        //             }
        //         }
        //     }
        // }

        // if($roomTypeData && $roomTypeData->pull_price == null){
        //     $deposit = $roomTypeData->rent_price;
        // }

        // if($userForLease->mda_tenant_id == null){
        //     StudentsInfo::where('id', $userForLease->id)->update(['mda_tenant_id' => $tenatntId]);
        //     MDAInfo::create(["userUid" => $userForLease->userUid, "mda_tenant_id" => $tenatntId, "mda_data" => $response, 'call_type' => '1']);
        // }


        // //Create MDA Lease
        // $curl = curl_init();

        // $leasefrom = $bookingForLease->lease_from_date.' '.$bookingForLease->lease_from_time.':00';
        // $leaseTo = $bookingForLease->lease_to_date.' '.$bookingForLease->lease_to_time.':00';

        // curl_setopt_array($curl, array(
        // CURLOPT_URL => 'https://api.mdapropsys.com/api/v2/TenantLeases/Save?',
        // CURLOPT_RETURNTRANSFER => true,
        // CURLOPT_ENCODING => '',
        // CURLOPT_MAXREDIRS => 10,
        // CURLOPT_TIMEOUT => 0,
        // CURLOPT_FOLLOWLOCATION => true,
        // CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        // CURLOPT_CUSTOMREQUEST => 'POST',
        // CURLOPT_POSTFIELDS =>'{
        //     "Dto": {
        //         "LeaseRentalTypes": [
        //             {
        //                 "RentPremises": [
        //                     {
        //                         "PropertyRentableUnitId": '.$RentableId.'
        //                     }
        //                 ],
        //                 "TransactionCodeId": 1024,
        //                 "TaxTypeId": 101,
        //                 "RentAmounts": [
        //                     {
        //                         "FromDate": "'.$leasefrom.'",
        //                         "ToDate": "'.$leaseTo.'",
        //                         "Amount": '.$deposit.',
        //                         "Rate": '.$deposit.',
        //                         "EscalationPerc": 0,
        //                         "TransactionRemarks": ""
        //                     }
        //                 ]
        //             }
        //         ],
        //         "TenantId": '.$tenatntId.',
        //         "LeaseStartDate": "'.$leasefrom.'",
        //         "OccupationDate": "'.$leasefrom.'",
        //         "LeaseExpiryDate": "'.$leaseTo.'",
        //         "RentReviewFrequency": 1,
        //         "LeaseDocFeePayableBy": 1,
        //         "DefaultRecoveryPerc": 0,
        //         "LeaseAuthoriseUserId": 4,
        //         "AuthoriseUserId": 4
        //     },
        //     "ConfirmationResponses": {
        //         "TenantLeases_SetMainLease": true
        //     }
        // }',
        // CURLOPT_HTTPHEADER => array(
        //     'DatabaseIdentifier: CC84465A-301E-4C19-AFA0-1939AB658425',
        //     'Authorization: Bearer '.$loginToken,
        //     'Content-Type: application/json'
        // ),
        // ));

        // $response = curl_exec($curl);
        // $responseData = json_decode($response);

        // curl_close($curl);

        // MDAInfo::create(["userUid" => $userForLease->userUid, "mda_tenant_id" => $tenatntId, "mda_data" => $response, 'call_type' => '2']);

        // $mdaFile = UserFiles::where('userUid', $userForLease->userUid)->where('year', $this_year)->where('fileId', 'MDA')->first();
        // if(empty($mdaFile)){

        //     if(property_exists($responseData, "ErrorCode")){
        //         UserFiles::create(['userUid'=> $userForLease->userUid, 'year' => $this_year, 'fileId' => 'MDA', 'status' => 'Uploaded']);

        //         ActionList::create([
        //             "userUid" => $userForLease->userUid,
        //             "action" => "MDA Lease Creation Failed",
        //             "actionUserUid" => Auth::user()->id,
        //             "actionUserDisplayName" => Auth::user()->userFirstName,
        //             "actionUserRole" => Auth::user()->userRole,
        //             "year" => $this_year,
        //         ]);

        //     }else{
        //         UserFiles::create(['userUid'=> $userForLease->userUid, 'year' => $this_year, 'fileId' => 'MDA', 'status' => 'Approved']);
        //     }
        // }else{
        //     if(property_exists($responseData, "ErrorCode")){
        //         UserFiles::where('userUid', $userForLease->userUid)->where('year', $this_year)->where('fileId', 'MDA')->update(['status' => 'Uploaded']);

        //         ActionList::create([
        //             "userUid" => $userForLease->userUid,
        //             "action" => "MDA Lease Creation Failed",
        //             "actionUserUid" => Auth::user()->id,
        //             "actionUserDisplayName" => Auth::user()->userFirstName,
        //             "actionUserRole" => Auth::user()->userRole,
        //             "year" => $this_year,
        //         ]);

        //     }else{
        //         UserFiles::where('userUid', $userForLease->userUid)->where('year', $this_year)->where('fileId', 'MDA')->update(['status' => 'Approved']);
        //     }
        // }

        // return UserFiles::where('userUid', $userForLease->userUid)->where('year', $this_year)->where('fileId', 'MDA')->first();

    }
}

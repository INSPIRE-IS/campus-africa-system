<?php

namespace App\Http\Controllers;

use App\Jobs\SendParentLeaseMail;
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
use Illuminate\Support\Facades\Session;

class LeaseController extends Controller
{
    public function view_sign_lease(Request $request, $lease_link_url)
    {
        // dd($request);
        $lease_link = UserLeaseToken::where('leaseToken', $lease_link_url)->where('active', '1')->first();
        if (!$lease_link) {
            return redirect('/dashboard');
        } else if ($lease_link->year != null) {
            $this_year = $lease_link->year;
        } else {
            $this_year = Lookups::where('group_id', '4')->first();
            if (Session::has('year')) {
                $year = Session::get('year');
                $this_year = Lookups::where('group_id', '4')->where('lookup_name', '=', $year)->first();
            }
            $this_year = $this_year->lookup_name;
        }


        if ($lease_link && !empty($lease_link)) {
            // $user = User::where('id', $lease_link->userUid)->first();
            $user = StudentsInfo::where('userUid', $lease_link->userUid)->where('year', $lease_link->year)->first();
            $file = UserFiles::where('userUid', $lease_link->userUid)->where('fileId', 'moveIn')->where('year', $lease_link->year)->first();

            // $data = User::where('id', $lease_link->userUid)->first();
            $data = StudentsInfo::where('userUid', $lease_link->userUid)->where('year', $lease_link->year)->first();
            $booking = Booking::where('userId', $lease_link->userUid)->where('year', $lease_link->year)->first();
            $propertyData = Property::where('id', $booking->property_data_id)->first();
            $residence = Residence::where('id', $propertyData->residence)->first();
            $leaseAdminFee = Lookups::where('group_id', '8')->first();
            $owner = storage_path() . '/leases/juanita-cronje.jpg';

            $paymentBooking = BookingPayment::where('bookingId', $booking->id)->first();
            // === Determine role (student or parent) ===
            $role = $lease_link->role ?? 'student';  // default for backwards compatibility

            // Student signature (if already signed)
            $studentSignature = null;
            if ($role === 'parent') {
                $studentToken = UserLeaseToken::where('userUid', $lease_link->userUid)
                    ->where('role', 'student')
                    ->where('signed', 1)
                    ->latest()
                    ->first();

                if ($studentToken && $studentToken->signature_url) {
                    $signature = 'storage/files/' . ($user->userId ?: $user->userPassport) . '/' . $studentToken->signature_url;
                }
                $signed_at = $studentToken->signed_at;
                // Build preview PDF view
                $pdf = view('PDF.leasesForParentScreen', compact(
                    'data',
                    'propertyData',
                    'residence',
                    'booking',
                    'leaseAdminFee',
                    'owner',
                    'paymentBooking',
                    'signature',
                    'signed_at',
                    'role'
                ));
            } else {
                // Build preview PDF view
                $pdf = view('PDF.leasesForScreen', compact(
                    'data',
                    'propertyData',
                    'residence',
                    'booking',
                    'leaseAdminFee',
                    'owner',
                    'paymentBooking',
                    'studentSignature',
                    'role'
                ));
            }


            return view('outSession.signLease', ["headerTitle" => "Sign Your Lease", "userCustomId" => $lease_link_url, "user" => $user, "file" => $file, "pdf" => $pdf, "active" => 0, "role" => $role, "studentSignature" => $studentSignature]);
        } else {
            return redirect('/dashboard');
        }
    }

    public function save_signed_lease(Request $request, $lease_link)
    {
        $lease_link = UserLeaseToken::where('leaseToken', $lease_link)->where('active', '1')->first();
        if (!$lease_link) {
            return redirect('/login');
        } else if ($lease_link->year != null) {
            $this_year = $lease_link->year;
        } else {
            $this_year = Lookups::where('group_id', '4')->first();
            if (Session::has('year')) {
                $year = Session::get('year');
                $this_year = Lookups::where('group_id', '4')->where('lookup_name', '=', $year)->first();
            }
            $this_year = $this_year->lookup_name;
        }

        $student = User::where('id', $lease_link->userUid)->first();
        $studentInfo = StudentsInfo::where('userUid', $lease_link->userUid)->where('year', $lease_link->year)->first();
        $booking = Booking::where('userId', $lease_link->userUid)->where('year', $lease_link->year)->first();

        // get the base64 string
        $data = $request->signature_string;

        // clean any symbols to decode
        list($type, $data) = explode(';', $data);
        list(, $extension) = explode('/', $type);
        list(, $data)      = explode(',', $data);

        // decode the string to the correct format
        $data = base64_decode($data);

        if ($studentInfo->userId == "") {
            $userIdentification = $studentInfo->userPassport;
        } else {
            $userIdentification = $studentInfo->userId;
        }


        // generate a random name
        $new_name = $userIdentification . '_' . strtotime("now") . '_' . substr(str_shuffle(str_repeat($x = '0123456789abcdefghijklmnopqrstuvwxyz', ceil(20 / strlen($x)))), 1, 20) . '.' . $extension;


        // file_put_contents(storage_path() . '/app/public/files/' . $userIdentification . '/' . $new_name, $data);
        \Storage::put('public/files/' . $userIdentification . '/' . $new_name, $data);

        //generate the signed lease

        // retreive all records from db
        // $data = User::where('id', $user->userUid)->first();
        $data = StudentsInfo::where('userUid', $lease_link->userUid)->where('year', $lease_link->year)->first();
        $booking = Booking::where('userId', $studentInfo->userUid)->where('year', $lease_link->year)->first();
        $propertyData = Property::where('id', $booking->property_data_id)->first();
        $residence = Residence::where('id', $propertyData->residence)->first();
        $leaseAdminFee = Lookups::where('group_id', '8')->first();
        $signed_at = $request->signed_at;
        $signature = 'storage/files/' . $userIdentification . '/' . $new_name;
        $owner = 'images/lease/juanita-cronje.jpg';
        // $signatureRelative  = 'storage/files/' . $userIdentification . '/' . $new_name;
        // $ownerRelative      = 'storage/leases/juanita-cronje.jpg';
        // $signature = pdfImagePath($signatureRelative);
        // $owner     = pdfImagePath($ownerRelative);
        // Log::info('Sending email to ' . $signature);
        // Log::info('Sending email to ' . $owner);


        $paymentBooking = BookingPayment::where('bookingId', $booking->id)->first();
        // check environment for dev or prod
        if (env('APP_ENV') == 'local') {
            $pdf = PDF::loadView('PDF.leaseSignDev', compact('data', 'propertyData', 'residence', 'booking', 'leaseAdminFee', 'signed_at', 'signature', 'owner', 'paymentBooking'));
        } else {
            $pdf = PDF::loadView('PDF.leaseSign', compact('data', 'propertyData', 'residence', 'booking', 'leaseAdminFee', 'signed_at', 'signature', 'owner', 'paymentBooking'));
        }
        // share data to view


        // download PDF file with download method
        // return $pdf->download('lease.pdf');

        $bookingYear = substr($booking->year, -2);
        $bookingPrefix = Residence::where('id', $booking->property)->pluck('building_prefix')->first();
        $timestamp = date('YmdHis');
        $fileNameToSave = $bookingYear . $bookingPrefix . "_" . $booking->bed_code . "_" . $data->userLastname . "_" . $data->userFirstName . "_" . $timestamp . ".pdf";
        //$fileNameToSave = $bookingYear . $bookingPrefix . "_" . $booking->bed_code . "_" . $user->userLastname . "_" . $user->userFirstName . ".pdf";

        $content = $pdf->download($fileNameToSave)->getOriginalContent();


        \Storage::put('public/files/' . $userIdentification . '/' . $fileNameToSave, $content);
        $parentEmail = $studentInfo->sponEmail ?? null;

        if ($parentEmail && $parentEmail !== "") {
            //Data to update
            $data = array(
                'fileName' => $fileNameToSave,
                'fileUrl' => 'files/' . $userIdentification . '/' . $fileNameToSave,
                'status' => 'Student Signed',
                'rejectReason' => '',
            );
            ActionList::create([
                "userUid" => $studentInfo->userUid,
                "action" => "Student Signed, Pending Parent Signature",
                "actionUserUid" => $studentInfo->userUid,
                "actionUserDisplayName" => $studentInfo->userFirstName,
                "actionUserRole" => "Applicant",
                "year" => $lease_link->year,
            ]);
        } else {
            //Data to update
            $data = array(
                'fileName' => $fileNameToSave,
                'fileUrl' => 'files/' . $userIdentification . '/' . $fileNameToSave,
                'status' => 'Uploaded',
                'rejectReason' => '',
            );

            ActionList::create([
                "userUid" => $studentInfo->userUid,
                "action" => "Student Signed, Pending approval",
                "actionUserUid" => $studentInfo->userUid,
                "actionUserDisplayName" => $studentInfo->userFirstName,
                "actionUserRole" => "Applicant",
                "year" => $lease_link->year,
            ]);
        }

        UserFiles::where('fileId', 'moveIn')->where('userUid', $studentInfo->userUid)->where('year', $lease_link->year)->update($data);

        // User::where('id', $user->id)->update(['rob_55_lease' => 1]);
        $studentInfo->update(['rob_55_lease' => 1]);

        //Update Token to signed & inactive
        UserLeaseToken::where('leaseToken', $lease_link->leaseToken)
            ->update(['active' => 0, 'signed' => 1, 'signature_url' => $new_name, 'year' => $this_year]);
        // Find parent email (assuming in StudentsInfo or related table)
        $parentEmail = $studentInfo->sponEmail ?? null;

        if ($parentEmail && $parentEmail !== "") {
            try {
                // Create Parent Lease Token
                $parentLeaseToken = UserLeaseToken::create([
                    "userUid"   => $studentInfo->userUid,
                    "leaseToken" => $this->generateRandomLongString(),
                    "active"    => 1,
                    "signed"    => 0,
                    "role"      => "parent", // you may want to add a column to distinguish tokens
                    "year" => $lease_link->year
                ]);

                // Build link for parent
                $leaseOpenTag = "<a href='https://" . $_SERVER['HTTP_HOST'] . "/sign-lease/" . $parentLeaseToken->leaseToken . "' target='_blank'>";
                Log::info('Sending parent signing link: ' . $leaseOpenTag);

                $this->dispatch(new SendParentLeaseMail($student, $this->generateRandomLongString(), $leaseOpenTag, $lease_link->year));

                ActionList::create([
                    "userUid" => $studentInfo->userUid,
                    "action" => "Parent Email Sent",
                    "actionUserUid" => $studentInfo->userUid,
                    "actionUserDisplayName" => $studentInfo->userFirstName,
                    "actionUserRole" => "Applicant",
                    "year" => $lease_link->year,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to dispatch SendParentLeaseMail job', [
                    'error' => $e->getMessage(),
                    'user'  => $student,
                ]);
                ActionList::create([
                    "userUid" => $studentInfo->userUid,
                    "action" => "Parent Email Sent, Failed",
                    "actionUserUid" => $studentInfo->userUid,
                    "actionUserDisplayName" => $studentInfo->userFirstName,
                    "actionUserRole" => "Applicant",
                    "year" => $lease_link->year,
                ]);
            }
        } else {
            Log::info('Failed to dispatch SendParentLeaseMail job', [
                'info' => 'Parent email not found',
                'userUid',
                $lease_link->userUid,
                "year" => $lease_link->year,
            ]);
        }

        return 'Completed';
    }

    public function view_sign_temp_lease(Request $request, $lease_link_url)
    {
        // dd($request);
        $lease_link = UserLeaseToken::where('leaseToken', $lease_link_url)->where('active', '1')->first();
        if (!$lease_link) {
            return redirect('/dashboard');
        } else if ($lease_link->year != null) {
            $this_year = $lease_link->year;
        } else {
            $this_year = Lookups::where('group_id', '4')->first();
            if (Session::has('year')) {
                $year = Session::get('year');
                $this_year = Lookups::where('group_id', '4')->where('lookup_name', '=', $year)->first();
            }
            $this_year = $this_year->lookup_name;
        }


        if ($lease_link && !empty($lease_link)) {
            // $user = User::where('id', $lease_link->userUid)->first();
            $user = StudentsInfo::where('userUid', $lease_link->userUid)->where('year', $this_year)->first();

            $file = UserFiles::where('userUid', $lease_link->userUid)->where('fileId', 'tempMoveIn')->where('year', $this_year)->first();

            // $data = User::where('id', $lease_link->userUid)->first();
            $data = StudentsInfo::where('userUid', $lease_link->userUid)->where('year', $this_year)->first();
            $booking = Booking::where('userId', $lease_link->userUid)->where('year', $this_year)->first();

            $owner = '/images/lease/juanita-cronje.jpg';
            $logo = '/images/logo/temp_lease_logo.png';

            $paymentBooking = BookingPayment::where('bookingId', $booking->id)->first();
            if ($data->userId == "") {
                $userIdentification = $data->userPassport;
            } else {
                $userIdentification = $data->userId;
            }

            $pdfdata = [
                'tenantName' => $data->userLastname . " " . $data->userFirstName,
                'tenantId' => $userIdentification,
            ];
            // dd($pdfdata);
            $pdf = view('PDF.tempLeasesForScreen', compact('pdfdata', 'owner', 'logo'));
            // $pdf = PDF::loadView('PDF.temp_lease', $pdfdata);

            // return $pdf->stream('test.pdf');
            return view('outSession.signTempLease', ["headerTitle" => "Sign Your Lease", "userCustomId" => $lease_link_url, "user" => $user, "file" => $file, "pdf" => $pdf, "active" => 0]);
        } else {
            return redirect('/dashboard');
        }
    }

    public function save_signed_temp_lease(Request $request, $lease_link)
    {
        $lease_link = UserLeaseToken::where('leaseToken', $lease_link)->where('active', '1')->first();
        if (!$lease_link) {
            return redirect('/login');
        } else if ($lease_link->year != null) {
            $this_year = $lease_link->year;
        } else {
            $this_year = Lookups::where('group_id', '4')->first();
            if (Session::has('year')) {
                $year = Session::get('year');
                $this_year = Lookups::where('group_id', '4')->where('lookup_name', '=', $year)->first();
            }
            $this_year = $this_year->lookup_name;
        }

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
        $location = 'public/files/' . $userIdentification . '/' . $new_name;
        Log::info('Location of the lease file: ' . $location);
        // file_put_contents(storage_path() . '/app/public/files/' . $userIdentification . '/' . $new_name, $data);
        \Storage::put('public/files/' . $userIdentification . '/' . $new_name, $data);

        //generate the signed lease

        // retreive all records from db
        // $data = User::where('id', $user->userUid)->first();
        $user = StudentsInfo::where('userUid', $lease_link->userUid)->where('year', $this_year)->first();
        $booking = Booking::where('userId', $user->userUid)->where('year', $this_year)->first();

        $signed_at = $request->signed_at;
        $signature = 'storage/files/' . $userIdentification . '/' . $new_name;
        Log::info('signature of the lease file: ' . $signature);
        $owner = '/images/lease/juanita-cronje.jpg';
        $logo = '/images/logo/temp_lease_logo.png';

        $paymentBooking = BookingPayment::where('bookingId', $booking->id)->first();

        if ($user->userId == "") {
            $userIdentification = $user->userPassport;
        } else {
            $userIdentification = $user->userId;
        }

        $pdfdata = [
            'tenantName' => $user->userLastname . " " . $user->userFirstName,
            'tenantId' => $userIdentification,
        ];

        // check environment for dev or prod
        if (env('APP_ENV') == 'local') {
            $pdf = PDF::loadView('PDF.tempLeaseSignDev', compact('pdfdata', 'signed_at', 'signature', 'owner', 'logo'));
        } else {
            $pdf = PDF::loadView('PDF.tempLeaseSign', compact('pdfdata', 'signed_at', 'signature', 'owner', 'logo'));
        }
        // share data to view


        // download PDF file with download method
        // return $pdf->download('lease.pdf');

        $bookingYear = substr($booking->year, -2);
        $bookingPrefix = Residence::where('id', $booking->property)->pluck('building_prefix')->first();
        // create a 5 digit random number
        $timestamp = date('YmdHis');
        $fileNameToSave = $bookingYear . $bookingPrefix . "_" . $booking->bed_code . "_" . $user->userLastname . "_" . $user->userFirstName . "_" . $timestamp . ".pdf";

        $content = $pdf->download($fileNameToSave)->getOriginalContent();

        \Storage::put('public/files/' . $userIdentification . '/' . $fileNameToSave, $content);

        //Data to update
        $data = array(
            'fileName' => $fileNameToSave,
            'fileUrl' => 'files/' . $userIdentification . '/' . $fileNameToSave,
            'status' => 'Uploaded',
        );

        UserFiles::where('fileId', 'tempMoveIn')->where('userUid', $user->userUid)->where('year', $this_year)->update($data);
        // test
        // User::where('id', $user->id)->update(['rob_55_lease' => 1]);
        StudentsInfo::where('userUid', $lease_link->userUid)->where('year', $this_year)->update(['rob_51_temp_lease_approve' => 2]);

        //Update Token to signed & inactive
        UserLeaseToken::where('leaseToken', $lease_link->leaseToken)
            ->update(['active' => 0, 'signed' => 1, 'signature_url' => $new_name, 'year' => $this_year]);

        return 'Completed';
    }

    public function save_parent_signed_lease(Request $request, $lease_link)
    {
        // dd($request->all());
        $lease_link_parent = UserLeaseToken::where('leaseToken', $lease_link)->where('active', 1)->where('role', 'parent')->firstOrFail();
        $lease_link_student = UserLeaseToken::where('userUid', $lease_link_parent->userUid)->where('signed', 1)->where('role', 'student')->firstOrFail();

        $user = StudentsInfo::where('userUid', $lease_link_parent->userUid)->where('year', $lease_link_parent->year)->first();

        // === Decode parent signature ===
        $data = $request->signature_string;
        list($type, $data) = explode(';', $data);
        list(, $extension) = explode('/', $type);
        list(, $data) = explode(',', $data);
        $data = base64_decode($data);

        $userIdentification = $user->userId ?: $user->userPassport;

        $new_name = $userIdentification . '_parent_' . strtotime("now") . '.' . $extension;
        file_put_contents(storage_path() . '/app/public/files/' . $userIdentification . '/' . $new_name, $data);

        $parentSignaturePath = 'storage/files/' . $userIdentification . '/' . $new_name;

        // Student signature (previously saved)
        $studentSignature = 'storage/files/' . $userIdentification . '/' . $lease_link_student->signature_url;

        $data = StudentsInfo::where('userUid', $lease_link_parent->userUid)->where('year', $lease_link_parent->year)->first();
        $booking = Booking::where('userId', $user->userUid)->where('year', $lease_link_parent->year)->first();
        $propertyData = Property::where('id', $booking->property_data_id)->first();
        $residence = Residence::where('id', $propertyData->residence)->first();
        $leaseAdminFee = Lookups::where('group_id', '8')->first();
        $signed_at = $request->signed_at;
        // $signature = 'storage/files/' . $userIdentification . '/' . $new_name;
        // $owner = 'storage/leases/juanita-cronje.jpg';

        $ownerRelative      = 'storage/leases/juanita-cronje.jpg';
        $signature = pdfImagePath($studentSignature);
        $owner     = pdfImagePath($ownerRelative);
        $parent     = pdfImagePath($parentSignaturePath);
        // Log::info('Sending email to ' . $signature);
        // Log::info('Sending email to ' . $owner);
        $paymentBooking = BookingPayment::where('bookingId', $booking->id)->first();

        $pdf = PDF::loadView('PDF.leaseSignParent', compact(
            'data',
            'propertyData',
            'residence',
            'booking',
            'leaseAdminFee',
            'signed_at',
            'parent',
            'signature',
            'owner',
            'paymentBooking'
        ));

        $fileNameToSave = $booking->year . "_" . $booking->bed_code . "_" . $user->userLastname . "_" . $user->userFirstName . "_final.pdf";

        $content = $pdf->download($fileNameToSave)->getOriginalContent();
        \Storage::put('public/files/' . $userIdentification . '/' . $fileNameToSave, $content);

        // Update user file
        UserFiles::where('fileId', 'moveIn')->where('userUid', $user->userUid)->where('year', $lease_link_parent->year)
            ->update([
                'fileName' => $fileNameToSave,
                'fileUrl'  => 'files/' . $userIdentification . '/' . $fileNameToSave,
                'status'   => 'Uploaded',
            ]);

        // Mark parent token signed
        $lease_link_parent->update([
            'active' => 0,
            'signed' => 1,
            'signature_url' => $new_name
        ]);
        ActionList::create([
            "userUid" => $lease_link_parent->userUid,
            "action" => "Parent/Sponser Signed",
            "actionUserUid" => $data->userUid,
            "actionUserDisplayName" => $data->userFirstName,
            "actionUserRole" => "Applicant",
            "year" => $lease_link_parent->year,
        ]);
        return 'Parent Signing Completed';
    }

    public function resendLease(Request $request)
    {
        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;
        $session_year = Session::get('year');
        if (!empty($session_year)) {
            $this_year = Lookups::where('group_id', '4')->where('lookup_name', '=', $session_year)->first();
            $this_year = $this_year->lookup_name;
        }
        $userLeaseToken = UserLeaseToken::where("userUid", $request->userId)->where("signed", 0)->where("role", 'student')->where("year", $this_year)->where('active', '1')->first();
        if (empty($userLeaseToken)) {
            // Create Lease Link
            $userLeaseToken = UserLeaseToken::create(["userUid" => $request->userId, "leaseToken" => $this->generateRandomLongString(), "active" => 1, "signed" => 0, "role" => 'student', "year" => $this_year]);
        }
        $leaseOpenTag = "<a href='https://" . $_SERVER['HTTP_HOST'] . "/sign-lease/" . $userLeaseToken->leaseToken . "' target='_blank'>";

        $userUidData = User::where('id', $request->userId)->first();
        try {
            Log::info('Sending email to ' . $leaseOpenTag);
            $this->dispatch(new SendResendLeaseMail($userUidData, $this->generateRandomLongString(), $leaseOpenTag, $this_year));
        } catch (\Exception $e) {
            Log::error('Failed to dispatch SendResendLeaseMail job', [
                'error' => $e->getMessage(),
                'userUidData' => $userUidData,
            ]);
        }
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

    public function resendLease1(Request $request)
    {

        $prevLeaseToken = UserLeaseToken::where("userUid", $request->userId)->where("active", 1)->where("signed", 0)->get();

        if (!empty($prevLeaseToken)) {
            foreach ($prevLeaseToken as $token) {
                UserLeaseToken::where('id', $token->id)->update(["active" => 0]);
            }
        }

        $this_year = Lookups::where('group_id', '4')->first();
        if (Session::has('year')) {
            $year = Session::get('year');
            $this_year = Lookups::where('group_id', '4')->where('lookup_name', '=', $year)->first();
        }
        $this_year = $this_year->lookup_name;

        // Create Lease Link
        $leaseToken = UserLeaseToken::create(
            [
                "userUid" => $request->userId,
                "leaseToken" => $this->generateRandomLongString(),
                "active" => 1,
                "signed" => 0,
                "year" => $this_year
            ]
        );

        $leaseOpenTag = "<a href='https://" . $_SERVER['HTTP_HOST'] . "/sign-lease/" . $leaseToken->leaseToken . "' target='_blank'>";

        $userUidData = User::where('id', $request->userId)->first();
        try {
            Log::info('Sending email to ');
            // $this->dispatch(new SendResendLeaseMail($userUidData, $this->generateRandomLongString(), $leaseOpenTag));
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
    public function resendTempLease(Request $request)
    {

        $prevLeaseToken = UserLeaseToken::where("userUid", $request->userId)->where("lease_type", "temp_lease")->where("active", 1)->where("signed", 0)->get();



        if (!empty($prevLeaseToken)) {
            foreach ($prevLeaseToken as $token) {
                UserLeaseToken::where('id', $token->id)->update(["active" => 0]);
            }
        }

        $this_year = Lookups::where('group_id', '4')->first();
        if (Session::has('year')) {
            $year = Session::get('year');
            $this_year = Lookups::where('group_id', '4')->where('lookup_name', '=', $year)->first();
        }
        $this_year = $this_year->lookup_name;

        $studentInfo = StudentsInfo::where('userUid', $request->userId)->where('year', $this_year)->first();
        $studentInfo->update(['rob_51_temp_lease_approve' => 0]);

        // Create Lease Link
        $leaseToken = UserLeaseToken::create(
            [
                "userUid" => $request->userId,
                "leaseToken" => $this->generateRandomLongString(),
                "lease_type" => "temp_lease",
                "active" => 1,
                "signed" => 0,
                "year" => $this_year
            ]
        );

        $leaseOpenTag = "<a href='https://" . $_SERVER['HTTP_HOST'] . "/sign-temp-lease/" . $leaseToken->leaseToken . "' target='_blank'>";

        $userUidData = User::where('id', $request->userId)->first();
        try {
            Log::info('Sending email to ');
            $this->dispatch(new SendResendLeaseMail($userUidData, $leaseToken->leaseToken, $leaseOpenTag, $this_year));
        } catch (\Exception $e) {
            Log::error('Failed to dispatch SendResendLeaseMail job', [
                'error' => $e->getMessage(),
                'userUidData' => $userUidData,
            ]);
        }
    }
    public function debug_lease_preview(Request $request, $userId, $year)
    {
        // dd($request->all());
        // dd($userId);
        $userUid = $userId;
        $signed_at = 'Regenerated via CLI';
        $role = 'student';
        $studentRelative      = 'images/lease/test_student_001.png';
        $parentRelative      = 'images/lease/test_parent_001.png';
        $ownerRelative      = 'images/lease/juanita-cronje.jpg';
        try {
            if ($role == 'parent') {
                $this->info("parent lease");
                $lease_link = UserLeaseToken::where('userUid', $userUid)
                    ->where('active', 0)
                    ->where('signed', 1)
                    ->where('role', 'student')
                    ->where('year', $year)
                    ->orderBy('created_at', 'desc')
                    ->first();
                $lease_link_parent = UserLeaseToken::where('userUid', $userUid)
                    ->where('active', 0)
                    ->where('signed', 1)
                    ->where('role', 'parent')
                    ->where('year', $year)
                    ->orderBy('created_at', 'desc')
                    ->first();

                $this->info("Found lease linkId {$lease_link->id} and year {$year}");

                if (!$lease_link) {
                    $this->error("No lease token found for userId {$userUid} and year {$year}");
                }

                $user = User::where('id', $lease_link->userUid)->first();
                $data = StudentsInfo::where('userUid', $lease_link->userUid)
                    ->where('year', $lease_link->year)
                    ->first();
                $booking = Booking::where('userId', $lease_link->userUid)
                    ->where('year', $lease_link->year)
                    ->first();

                if (!$booking) {
                    $this->error("No booking found for user {$lease_link->userUid} and year {$lease_link->year}");
                }

                $propertyData = Property::where('id', $booking->property_data_id)->first();
                $residence = Residence::where('id', $propertyData->residence)->first();
                $leaseAdminFee = Lookups::where('group_id', '8')->first();

                $userIdentification = !empty($user->userId) ? $user->userId : $user->userPassport;

                // $signature = pdfImagePath($studentRelative);
                // $owner     = pdfImagePath($ownerRelative);
                // //$parentRelative      = 'storage/files/' . $userIdentification . '/' . $lease_link_parent?->signature_url;
                // $parentSignature = pdfImagePath($parentRelative);
                // $parent = pdfImagePath($parentRelative);
                $signature = 'storage/files/' . $userIdentification . '/' . $lease_link->signature_url;
                $owner = 'images/lease/juanita-cronje.jpg';
                // $signature =  pdfImagePath($signature); // 'storage/files/' . $userIdentification . '/' . $lease_link->signature_url;
                // $this->info("Signature URL: {$signature}");
                // $owner     = pdfImagePath($owner);

                $paymentBooking = BookingPayment::where('bookingId', $booking->id)->first();

                // generate PDF
                $baseUrl = config('app.url') ?? 'https://campusafrica.co.za';

                if (env('APP_ENV') == 'local') {
                    //  $pdf = PDF::loadView('PDF.leaseSignDev', compact('data', 'propertyData', 'residence', 'booking', 'leaseAdminFee', 'signed_at', 'signature', 'owner', 'paymentBooking'));
                    $pdf = PDF::loadView('PDF.leaseSignDev', compact(
                        'data',
                        'propertyData',
                        'residence',
                        'booking',
                        'leaseAdminFee',
                        'signed_at',
                        'studentSignature',
                        'parentSignature',
                        'parent',
                        'signature',
                        'owner',
                        'paymentBooking'
                    ));
                } else {
                    // $pdf = PDF::loadView('PDF.leaseSign', compact('data', 'propertyData', 'residence', 'booking', 'leaseAdminFee', 'signed_at', 'signature', 'owner', 'paymentBooking'));
                    $pdf = PDF::loadView('PDF.leaseSign', compact(
                        'data',
                        'propertyData',
                        'residence',
                        'booking',
                        'leaseAdminFee',
                        'signed_at',
                        'studentSignature',
                        'parentSignature',
                        'parent',
                        'signature',
                        'owner',
                        'paymentBooking'
                    ));
                }
                $bookingYear = substr($booking->year, -2);
                $bookingPrefix = Residence::where('id', $booking->property)->pluck('building_prefix')->first();
                $timestamp = date('YmdHis');
                $fileNameToSave = $bookingYear . $bookingPrefix . "_" .
                    $booking->bed_code . "_" .
                    $user->userLastname . "_" .
                    $user->userFirstName .
                    $timestamp . ".pdf";
            } else {

                $lease_link = UserLeaseToken::where('userUid', $userUid)
                    ->where('active', 1)
                    ->where('signed', 0)
                    ->where('role', 'student')
                    ->where('year', $year)
                    ->orderBy('created_at', 'desc')
                    ->first();


                if (!$lease_link) {
                    throw new \Exception("No lease token found for userId {$userUid} and year {$year}");
                }

                $user = User::where('id', $lease_link->userUid)->first();
                $data = StudentsInfo::where('userUid', $lease_link->userUid)
                    ->where('year', $lease_link->year)
                    ->first();
                if (!$user || !$data) {
                    Log::error("User or StudentsInfo record not found for userId {$userUid} and year {$year}");
                }
                //dd('here 1');
                $booking = Booking::where('userId', $lease_link->userUid)
                    ->where('year', $lease_link->year)
                    ->first();

                if (!$booking) {
                    Log::error("No booking found for user {$lease_link->userUid} and year {$lease_link->year}");
                }

                // property_data property_data_id
                $propertyData = Property::where('id', $booking->property_data_id)->first();

                // property property
                $residence = Residence::where('id', $booking->property)->first();

                $leaseAdminFee = Lookups::where('group_id', '8')->first();


                $userIdentification = !empty($user->userId) ? $user->userId : $user->userPassport;
                $signature = 'storage/files/' . $userIdentification . '/' . $lease_link->signature_url;
                $owner = 'images/lease/juanita-cronje.jpg';

                // $signature =  pdfImagePath($signature); // 'storage/files/' . $userIdentification . '/' . $lease_link->signature_url;
                // $this->info("Signature URL: {$signature}");
                // $owner     = pdfImagePath($owner);
                $paymentBooking = BookingPayment::where('bookingId', $booking->id)->first();

                // generate PDF
                $baseUrl = config('app.url') ?? 'https://campusafrica.co.za';
                if (env('APP_ENV') == 'local') {
                    $pdf = PDF::loadView('PDF.leaseSignDev', compact(
                        'data',
                        'propertyData',
                        'residence',
                        'booking',
                        'leaseAdminFee',
                        'signed_at',
                        'signature',
                        'owner',
                        'paymentBooking',
                        'baseUrl'
                    ));
                } else {
                    $pdf = PDF::loadView('PDF.leaseSign', compact(
                        'data',
                        'propertyData',
                        'residence',
                        'booking',
                        'leaseAdminFee',
                        'signed_at',
                        'signature',
                        'owner',
                        'paymentBooking',
                        'baseUrl'
                    ));
                }



                $bookingYear = substr($booking->year, -2);
                $bookingPrefix = Residence::where('id', $booking->property)->pluck('building_prefix')->first();
                $timestamp = date('YmdHis');
                $fileNameToSave = $bookingYear . $bookingPrefix . "_" .
                    $booking->bed_code . "_" .
                    $data->userLastname . "_" .
                    $data->userFirstName .
                    $timestamp . ".pdf";
            }

            $content = $pdf->download($fileNameToSave)->getOriginalContent();
            $filePath = "public/files/leases/{$userIdentification}/{$fileNameToSave}";


            // Storage::put($filePath, $pdf->output());
            //file_put_contents($filePath, $content);
            //  \Storage::put($filePath, $content);
            // Storage::disk('local')->put($filePath, $content);
            // Log::info("Lease PDF regenerated for user {$userUid} ({$year}): {$filePath}");


            return $pdf->stream("lease-preview.pdf");


            return view('PDF.leaseSign', compact(
                'data',
                'propertyData',
                'residence',
                'booking',
                'leaseAdminFee',
                'signed_at',
                'signature',
                'owner',
                'paymentBooking',
                'baseUrl'
            ));
        } catch (\Exception $e) {
            Log::error("Error in TestLeasePDFGeneration job", [
                'error' => $e->getMessage(),
                'user_id' => $userUid ?? null,
            ]);
        }
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

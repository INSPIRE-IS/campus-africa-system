<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OnlinePayments;
use App\Models\UserFiles;
use App\Models\ActionList;
use App\Models\User;
use App\Models\Booking;
use App\Models\Lookups;
use DateTime;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Auth;
use App\Models\StudentsInfo;
use App\Services\AssurityService;

class OnlinePaymentController extends Controller
{
    /** @var AssurityService */
    protected $assurityService;

    public function __construct(AssurityService $assurityService)
    {
        $this->assurityService = $assurityService;
    }

    public function initiate_payment(Request $request)
    {

        //encryption key set in the Merchant Access Portal
        // $encryptionKey = 'secret';
        // $paygateId = 10011072130;

        $encryptionKey = 'wUrEU5MlbopX';
        $paygateId = 1039719100014;

        $DateTime = new DateTime();

        if ($request->req_type == "new" || $request->req_type == "retry") {
            $req_url = "online-application-payment";
        } else {
            $req_url = "online-application-payment-on-screen";
        }

        $data = array(
            'PAYGATE_ID'        => $paygateId,
            'REFERENCE'         => $request->reference,
            'AMOUNT'            => floatval($request->amount) * 100,
            'CURRENCY'          => 'ZAR',
            'RETURN_URL'        => 'https://' . $_SERVER['HTTP_HOST'] . '/' . $req_url . '/' . $request->id,
            // 'RETURN_URL'        => 'http://'.$_SERVER['HTTP_HOST'].'/'.$req_url.'/'.$request->id,
            'TRANSACTION_DATE'  => $DateTime->format('Y-m-d H:i:s'),
            'LOCALE'            => 'en-za',
            'COUNTRY'           => 'ZAF',
            'EMAIL'             => 'customer@paygate.co.za',
            'NOTIFY_URL'        => 'https://' . $_SERVER['HTTP_HOST'] . '/notify-data',
            // 'NOTIFY_URL'        => 'http://'.$_SERVER['HTTP_HOST'].'/notify-data',
        );

        $checksum = md5(implode('', $data) . $encryptionKey);

        $data['CHECKSUM'] = $checksum;

        $fieldsString = http_build_query($data);

        //open connection
        $ch = curl_init();

        //set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, 'https://secure.paygate.co.za/payweb3/initiate.trans');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_NOBODY, false);
        curl_setopt($ch, CURLOPT_REFERER, $_SERVER['HTTP_HOST']);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fieldsString);

        //execute post
        $result = curl_exec($ch);

        // dd($result);

        OnlinePayments::create([
            "userUid" => $request->id,
            "requestID" => explode("=", explode("&", $result)[1])[1],
            "checkSum" => explode("=", explode("&", $result)[3])[1],
            "reference" => $request->reference,
            "amount" => $request->amount,
            "status" => "Initiated",
        ]);

        $this_year = Lookups::resolveYearForUser((int) $request->id, Session::get('year'));

        if ($request->payment_type !== '') {
            Booking::where('userId', $request->id)->where('year', $this_year)->update(['payment_type' => $request->payment_type, 'booking_payment_ref' => $request->reference]);
        }

        // $currUser = User::where('id', $request->id)->first();
        $currUser = StudentsInfo::where('userUid', $request->id)->where('year', $this_year)->first();
        $displayName = $currUser ? $currUser->userFirstName : (User::where('id', $request->id)->value('userFirstName') ?? '');
        ActionList::create([
            "userUid" => $request->id,
            "action" => "Online Payment Initiated",
            "actionUserUid" => $request->id,
            "actionUserDisplayName" => $displayName,
            "actionUserRole" => 0,
            "year" => $this_year,
        ]);

        //close connection
        curl_close($ch);

        return [explode("=", explode("&", $result)[1])[1], explode("=", explode("&", $result)[3])[1]];
    }

    public function notify_data(Request $request)
    {

        $transaction = OnlinePayments::where("requestID", $request->PAY_REQUEST_ID)->where("reference", $request->REFERENCE)->first();

        if (!empty($transaction)) {
            // Derive the booking/year from the payment reference first (no Session context on notify callbacks).
            $bookingForPayment = Booking::where('booking_payment_ref', $transaction->reference)->orderByDesc('year')->first();
            $this_year = $bookingForPayment ? (string) $bookingForPayment->year : Lookups::resolveYearForUser((int) $transaction->userUid, null);

            if ($request->TRANSACTION_STATUS == '0') {
                $status = "Not Done";
            } else if ($request->TRANSACTION_STATUS == '1') {
                $status = "Approved";
            } else if ($request->TRANSACTION_STATUS == '2') {
                $status = "Declined";
            } else if ($request->TRANSACTION_STATUS == '3') {
                $status = "Cancelled";
            } else if ($request->TRANSACTION_STATUS == '4') {
                $status = "User Cancelled";
            } else if ($request->TRANSACTION_STATUS == '5') {
                $status = "Received by PayGate";
            } else if ($request->TRANSACTION_STATUS == '7') {
                $status = "Settlement Voided";
            }
            OnlinePayments::where('id', $transaction->id)->update(['status' => $status, 'post_data' => $_REQUEST, 'payment_amount' => $request->AMOUNT, 'payment_transaction_id' => $request->TRANSACTION_ID, 'payment_method' => $request->PAY_METHOD, 'payment_method_detail' => $request->PAY_METHOD_DETAIL, 'payment_status' => $request->TRANSACTION_STATUS]);

            $hasFile = UserFiles::where('userUid', $transaction->userUid)->where('fileId', 'proof_of_payment')->where('year', $this_year)->first();

            if (!empty($hasFile) && $status == "Approved") {
                UserFiles::where('id', $hasFile->id)->update(["status" => "Approved"]);
                // User::where('id', $hasFile->userUid)->update(["rob_10_fee" => 2, "fee_approved" => 1]);
                StudentsInfo::where('userUid', $hasFile->userUid)->where('year', $this_year)->update(["rob_10_fee" => 2, "fee_approved" => 1]);
            }
            $paymentMethod = null;
            if ($request->PAY_METHOD == "CC" || $request->PAY_METHOD == "DC") {
                $paymentMethod = "Credit/Debit Card";
            } else if ($request->PAY_METHOD == "BT") {
                $paymentMethod = "Online EFT";
            }

            if ($bookingForPayment && $paymentMethod !== null && $paymentMethod !== $bookingForPayment->payment_type) {
                Booking::where('id', $bookingForPayment->id)->update(['payment_type' => $paymentMethod]);
            }

            // $currUser = User::where('id', $transaction->userUid)->first();
            $currUser = StudentsInfo::where('userUid', $transaction->userUid)->where('year', $this_year)->first();
            $displayName = $currUser ? $currUser->userFirstName : (User::where('id', $transaction->userUid)->value('userFirstName') ?? '');
            ActionList::create([
                "userUid" => $transaction->userUid,
                "action" => "Online Payment Status: " . $status,
                "actionUserUid" => $transaction->userUid,
                "actionUserDisplayName" => $displayName,
                "actionUserRole" => 0,
                "year" => $this_year,
            ]);
        }
    }

    public function online_application_payment($id)
    {

        sleep(5);

        $fileType = "proof_of_payment";

        $paymentUpdate = OnlinePayments::where('userUid', $id)->orderBy('created_at', 'desc')->first();
        $bookingForPayment = null;
        if ($paymentUpdate && !empty($paymentUpdate->reference)) {
            $bookingForPayment = Booking::where('booking_payment_ref', $paymentUpdate->reference)->orderByDesc('year')->first();
        }
        $this_year = $bookingForPayment ? (string) $bookingForPayment->year : Lookups::resolveYearForUser((int) $id, Session::get('year'));

        $hasFile = UserFiles::where('userUid', $id)->where('fileId', 'proof_of_payment')->where('year', $this_year)->first();

        $user_status = $this->get_user_status($fileType, "",  StudentsInfo::where('userUid', $id)->where('year', $this_year)->first());
        $field_data = $user_status['field_data'];
        $field_doc = $user_status['field_doc'];
        if (!$paymentUpdate) {
            return redirect('/retry/online-application');
        }

        if (empty($hasFile)) {

            if ($paymentUpdate->status == "Approved") {
                UserFiles::create([
                    'fileId' => $fileType,
                    'userUid' => $id,
                    'fileName' => "",
                    'fileUrl' => "",
                    'status' => "Approved",
                    'uploaded_by' => $id,
                    'year' => $this_year,
                ]);
                // User::where('id', $id)->update(["rob_10_fee" => 2, "fee_approved" => 1]);
                StudentsInfo::where('userUid', $id)->where('year', $this_year)->update(["rob_10_fee" => 2, "fee_approved" => 1]);
            } else {
                UserFiles::create([
                    'fileId' => $fileType,
                    'userUid' => $id,
                    'fileName' => "",
                    'fileUrl' => "",
                    'status' => "Uploaded",
                    'uploaded_by' => $id,
                    'year' => $this_year,
                ]);
            }
        } else {
            if ($paymentUpdate->status == "Approved") {
                UserFiles::where('id', $hasFile->id)->update([
                    'status' => "Approved",
                ]);
                // User::where('id', $id)->update(["rob_10_fee" => 2, "fee_approved" => 1]);
                StudentsInfo::where('userUid', $id)->where('year', $this_year)->update(["rob_10_fee" => 2, "fee_approved" => 1]);
            } else {
                UserFiles::where('id', $hasFile->id)->update([
                    'status' => "Uploaded",
                ]);
            }
        }



        $text = "Uploaded Proof of payment for review through PayGate";

        // $cur_status = User::find($id);
        $cur_status = StudentsInfo::where('userUid', $id)->where('year', $this_year)->first();

        if ($paymentUpdate->status != "Approved") {
            //Update data status back to pending
            if (!empty($field_data)) {
                //If the data was already approved then unapprove
                if ($cur_status->{$field_data} == 1) {
                    // User::where('id', $id)->update([$field_data => '0']);       
                    StudentsInfo::where('userUid', $id)->where('year', $this_year)->update([$field_data => '0']);
                }
            }

            //Update Robot status back to pending
            if (!empty($field_doc)) {
                // User::where('id', $id)->update([$field_doc => '1']);
                StudentsInfo::where('userUid', $id)->where('year', $this_year)->update([$field_doc => '1']);
            }
        }


        //Get the file
        $file = UserFiles::where('userUid', $id)->where('fileId', $fileType)->where('year', $this_year)->first();
        // $currUser = User::where('id', $id)->first();
        $currUser = StudentsInfo::where('userUid', $id)->where('year', $this_year)->first();

        ActionList::create([
            "userUid" => $id,
            "action" => $text,
            "actionUserUid" => $id,
            "actionUserDisplayName" => $currUser->userFirstName,
            "actionUserRole" => 0,
            "year" => $this_year,
        ]);

        $this->update_user_status($id);

        // After Step 5 payment submission, auto-request Debt Assurity for Cash students (sponsor signing link).
        $this->assurityService->maybeAutoRequestAfterStep5($id, $this_year, [
            'user_uid' => $id,
            'display_name' => $currUser->userFirstName,
            'role' => 0,
        ], 'step5-paygate');

        /** @disregard P1009 */
        if (Auth::check() === false) {
            /** @disregard P1009 */
            Auth::loginUsingId($id, true);
        }

        $paymentStatus = OnlinePayments::where('userUid', $id)->orderBy('created_at', 'desc')->first();

        if ($paymentStatus->status == "Approved" || $paymentStatus->status ==  "Received by PayGate" || $paymentStatus->status ==  "Initiated") {
            return redirect('/complete/online-application');
        } else {
            return redirect('/retry/online-application');
        }
    }

    public function online_application_payment_on_screen($id)
    {

        sleep(5);

        $paymentUpdate = OnlinePayments::where('userUid', $id)->orderBy('created_at', 'desc')->first();
        $bookingForPayment = null;
        if ($paymentUpdate && !empty($paymentUpdate->reference)) {
            $bookingForPayment = Booking::where('booking_payment_ref', $paymentUpdate->reference)->orderByDesc('year')->first();
        }
        $this_year = $bookingForPayment ? (string) $bookingForPayment->year : Lookups::resolveYearForUser((int) $id, Session::get('year'));

        $fileType = "proof_of_payment";
        $hasFile = UserFiles::where('userUid', $id)->where('fileId', 'proof_of_payment')->where('year', $this_year)->first();

        $user_status = $this->get_user_status($fileType, "", StudentsInfo::where('userUid', $id)->where('year', $this_year)->first());
        $field_data = $user_status['field_data'];
        $field_doc = $user_status['field_doc'];

        if (!$paymentUpdate) {
            return redirect('/retry/online-application');
        }

        if (empty($hasFile)) {

            if ($paymentUpdate->status == "Approved") {
                UserFiles::create([
                    'fileId' => $fileType,
                    'userUid' => $id,
                    'fileName' => "",
                    'fileUrl' => "",
                    'status' => "Approved",
                    'uploaded_by' => $id,
                    'year' => $this_year,
                ]);
                // User::where('id', $id)->update(["rob_10_fee" => 2, "fee_approved" => 1]);
                StudentsInfo::where('userUid', $id)->where('year', $this_year)->update(["rob_10_fee" => 2, "fee_approved" => 1]);
            } else {
                UserFiles::create([
                    'fileId' => $fileType,
                    'userUid' => $id,
                    'fileName' => "",
                    'fileUrl' => "",
                    'status' => "Uploaded",
                    'uploaded_by' => $id,
                    'year' => $this_year,
                ]);
            }
        } else {
            if ($paymentUpdate->status == "Approved") {
                UserFiles::where('id', $hasFile->id)->update([
                    'status' => "Approved",
                ]);
                // User::where('id', $id)->update(["rob_10_fee" => 2, "fee_approved" => 1]);
                StudentsInfo::where('userUid', $id)->where('year', $this_year)->update(["rob_10_fee" => 2, "fee_approved" => 1]);
            } else {
                UserFiles::where('id', $hasFile->id)->update([
                    'status' => "Uploaded",
                ]);
            }
        }

        $text = "Uploaded Proof of payment for review through PayGate";

        // $cur_status = User::find($id);
        $cur_status = StudentsInfo::where('userUid', $id)->where('year', $this_year)->first();

        if ($paymentUpdate->status != "Approved") {

            //Update data status back to pending
            if (!empty($field_data)) {
                //If the data was already approved then unapprove
                if ($cur_status->{$field_data} == 1) {
                    // User::where('id', $id)->update([$field_data => '0']);    
                    StudentsInfo::where('userUid', $id)->where('year', $this_year)->update([$field_data => '0']);
                }
            }

            //Update Robot status back to pending
            if (!empty($field_doc)) {
                // User::where('id', $id)->update([$field_doc => '1']);
                StudentsInfo::where('userUid', $id)->where('year', $this_year)->update([$field_doc => '1']);
            }
        }

        //Get the file
        $file = UserFiles::where('userUid', $id)->where('fileId', $fileType)->where('year', $this_year)->first();
        // $currUser = User::where('id', $id)->first();
        $currUser = StudentsInfo::where('userUid', $id)->where('year', $this_year)->first();

        ActionList::create([
            "userUid" => $id,
            "action" => $text,
            "actionUserUid" => $id,
            "actionUserDisplayName" => $currUser->userFirstName,
            "actionUserRole" => 0,
            "year" => $this_year,
        ]);

        $this->update_user_status($id);

        // After Step 5 payment submission, auto-request Debt Assurity for Cash students (sponsor signing link).
        $this->assurityService->maybeAutoRequestAfterStep5($id, $this_year, [
            'user_uid' => $id,
            'display_name' => $currUser->userFirstName,
            'role' => 0,
        ], 'step5-paygate-on-screen');
        /** @disregard P1009 */
        if (Auth::check() === false) {
            /** @disregard P1009 */
            Auth::loginUsingId($id, true);
        }

        $paymentStatus = OnlinePayments::where('userUid', $id)->orderBy('created_at', 'desc')->first();

        if ($paymentStatus->status == "Approved" || $paymentStatus->status ==  "Received by PayGate" || $paymentStatus->status ==  "Initiated") {
            return redirect('/dashboard');
        } else {
            return redirect('/retry/online-application');
        }
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
        } elseif ($fileType == "assurity_doc") {
            $typeName = "Debt Assurity";
            $field_data = "";
            $field_doc = "rob_65_debt";
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

        $this_year = Lookups::resolveYearForUser((int) $user_id, Session::get('year'));

        $userInfo = StudentsInfo::where('userUid',  $user_id)->where('year', $this_year)->first();
        $userFiles = UserFiles::where('userUid',  $user_id)->where('year', $this_year)->get();

        $user = User::with('files')->where('id', $user_id)->first();
        if (!$userInfo) {
            return;
        }

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
}

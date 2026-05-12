<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Booking;
use App\Models\Property;
use App\Models\Residence;
use App\Models\ActionList;
use PDF;
use Auth;
use App\Models\UserFiles;
use App\Models\Lookups;
use App\Models\UserLeaseToken;
use App\Models\MailerList;
use Mail;

use App\Models\StudentsInfo;
use App\Models\BookingPayment;
use App\Services\LeaseContactService;
use App\Jobs\SendResendLeaseMail;
use App\Jobs\SendResendTempLeaseMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;


class PDFController extends Controller
{


  public function createPDF(Request $request, $studentId)
  {
    $bookingId = $request->bookingId;
    $property = $request->property;
    $roomType = $request->roomType;

    $yearUserId = (int) ($bookingId ?: $studentId);
    $this_year = Lookups::resolveYearForUser($yearUserId, Session::get('year'));

    if (!$request->type) {

      $oldData = Booking::where('userId', $bookingId)->where('year', $this_year)->first();

      $newData = ['property|Property' => $property, 'roomType|Room Type' => $roomType, "floor|Floor" => $request->floor, "unit|Unit" => $request->unit, "room|Room" => $request->room, "bed|Bed" => $request->bed, "bed_code|Bed Code" => $request->bed_code, "lease_from_date|Lease From Date" => $request->lease_from_date, "lease_from_time|Lease From Time" => $request->lease_from_time, "lease_to_date|Lease To Date" => $request->lease_to_date, "lease_to_time|Lease To Time" => $request->lease_to_time];

      $propertyOld = Property::where('user_id_room', $bookingId)->update(["user_id_room" => null, "room_available" => null]);

      Property::where('residence', $property)->where('room_type', $roomType)->where("floor", $request->floor)->where('unit', $request->unit)->where('room', $request->room)->where('bed', $request->bed)->where('bed_code', $request->bed_code)->update(["room_available" => "1", "user_id_room" => $bookingId]);

      if ($roomType == "Single") {
        $roomType = "Single Room";
      }

      $propertyData = Property::where('residence', $property)->where('room_type', $roomType)->where("floor", $request->floor)->where('unit', $request->unit)->where('room', $request->room)->where('bed', $request->bed)->where('bed_code', $request->bed_code)->first();

      $studentInfo = StudentsInfo::where('userUid', $bookingId)->where('year', $this_year)->first();
      $leaseAdminFee = 0;
      if ($studentInfo) {
        $fundingId = Lookups::fundingTypeIdForLabel($studentInfo->studentType);
        if ($fundingId) {
          $yearLookup = Lookups::where('group_id', '4')->where('lookup_name', $this_year)->first();
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
          if (array_key_exists($fundingId, $leaseFees)) {
            $leaseAdminFee = floatval($leaseFees[$fundingId]);
          }
        }
      }
      $studentInfo = StudentsInfo::where('userUid', $bookingId)->where('year', $this_year)->first();
      $leaseAdminFee = 0;
      if ($studentInfo) {
        $fundingId = Lookups::fundingTypeIdForLabel($studentInfo->studentType);
        if ($fundingId) {
          $yearLookup = Lookups::where('group_id', '4')->where('lookup_name', $this_year)->first();
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
          if (array_key_exists($fundingId, $leaseFees)) {
            $leaseAdminFee = floatval($leaseFees[$fundingId]);
          }
        }
      }



      foreach ($newData as $key => $new) {

        $info = explode("|", $key);
        $key = $info[0];
        $changedField = $info[1];

        if ($oldData->$key !== $new) {
          if ($oldData->$key == "" || $oldData->$key == null) {
            $oldData->$key = "Unassigned";
          }
          ActionList::create([
            "userUid" => $request->bookingId,
            "action" => "Updated " . $changedField . " From " . $oldData->$key . " to " . $new,
            /** @disregard P1009 */
            "actionUserUid" => Auth::user()->id,
            "actionUserDisplayName" => Auth::user()->userFirstName,
            "actionUserRole" => Auth::user()->userRole,
            "year" => $this_year,
          ]);
        }
      }

      ActionList::create([
        "userUid" => $request->bookingId,
        "action" => "Generated a Lease",
        "actionUserUid" => Auth::user()->id,
        "actionUserDisplayName" => Auth::user()->userFirstName,
        "actionUserRole" => Auth::user()->userRole,
        "year" => $this_year,
      ]);

      Booking::where('userId', $bookingId)->where('year', $this_year)->update(["property" => $property, "roomType" => $roomType, "floor" => $request->floor, "unit" => $request->unit, "room" => $request->room, "bed" => $request->bed, "bed_code" => $request->bed_code, "property_data_id" => $propertyData->id, "lease_from_date" => $request->lease_from_date, "lease_from_time" => $request->lease_from_time, "lease_to_date" => $request->lease_to_date, "lease_to_time" => $request->lease_to_time, "leaseAdminFee" => $leaseAdminFee]);
    } else {

      // retreive all records from db
      // $data = User::where('id', $studentId)->first();
      $data = StudentsInfo::where('userUid', $studentId)->where('year', $this_year)->first();
      //dd($studentId);
      if (!$data) {
        return response()->json([
          'message' => 'Cannot generate lease. Student info not found.',
        ], 404);
      }
      $missingFields = LeaseContactService::missingFields($data);
      if (!empty($missingFields)) {
        return response()->json([
          'message' => 'Kin detail is missing required fields. Please ensure these are available to continue. Missing: ' . implode(', ', $missingFields) . '.',
          'missing' => $missingFields,
        ], 422);
      }
      // Debt Assurity must be approved (1) before a lease can be generated for Cash students.
      if ($data->studentType == 'Cash / Direct' && (int) $data->rob_65_debt !== 1) {
        \Log::warning('LEASE_GATE: blocked lease generation due to debt assurity status', [
          'studentId' => $studentId,
          'year' => $this_year,
          'rob_65_debt' => (int) $data->rob_65_debt,
        ]);
        return response()->json([
          'message' => 'Debt Assurity must be approved before generating a lease.',
        ], 422);
      }

      $booking = Booking::where('userId', $studentId)->where('year', $this_year)->first();
      if (!$booking) {
        return response()->json([
          'message' => 'Cannot generate lease. Booking not found.',
        ], 404);
      }
      $propertyData = Property::where('id', $booking->property_data_id)->first();
      if (!$propertyData) {
        return response()->json([
          'message' => 'Cannot generate lease. Booking property data not found.',
        ], 422);
      }
      $residence = Residence::where('id', $propertyData->residence)->first();
      if (!$residence) {
        return response()->json([
          'message' => 'Cannot generate lease. Residence not found.',
        ], 422);
      }
      $leaseAdminFee = 0;
      $fundingId = Lookups::fundingTypeIdForLabel($data->studentType);
      if ($fundingId) {
        $yearLookup = Lookups::where('group_id', '4')->where('lookup_name', $this_year)->first();
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
        if (array_key_exists($fundingId, $leaseFees)) {
          $leaseAdminFee = floatval($leaseFees[$fundingId]);
        }
      }
      $owner = storage_path() . '/leases/juanita-cronje.jpg';

      $paymentBooking = BookingPayment::where('bookingId', $booking->id)->first();

      $leaseContact = LeaseContactService::resolve($data);

      // share data to view
      $pdf = PDF::loadView('PDF.leases', compact('data', 'propertyData', 'residence', 'booking', 'leaseAdminFee', 'owner', 'paymentBooking', 'leaseContact'));

      // download PDF file with download method
      // return $pdf->download('lease.pdf');

      if ($data->userId == "") {
        $userIdentification = $data->userPassport;
      } else {
        $userIdentification = $data->userId;
      }

      $bookingYear = substr($booking->year, -2);
      $bookingPrefix = Residence::where('id', $booking->property)->pluck('building_prefix')->first();

      $fileNameToSave = $bookingYear . $bookingPrefix . "_" . $booking->bed_code . "_" . $data->userLastname . "_" . $data->userFirstName . ".pdf";
      // $fileNameToSave = 'lease.pdf';

      $content = $pdf->download($fileNameToSave)->getOriginalContent();
      // $user = User::where('id', $studentId)->first();
      /** @disregard P1009 */
      \Storage::put('public/files/' . $userIdentification . '/' . $fileNameToSave, $content);

      //Check if there is an existing lease to overwrite
      $lease = UserFiles::where('fileId', 'moveIn')->where('userUid', $studentId)->where('year', $this_year)->first();

      $data = array(
        'userUid' => $studentId,
        'fileName' => $fileNameToSave,
        'fileUrl' => 'files/' . $userIdentification . '/' . $fileNameToSave,
        'status' => 'Rejected',
        /** @disregard P1009 */
        'uploaded_by' => Auth::user()->name,
        'created_at' => date('Y-m-d h:m:s'),
        'year' => $this_year,
      );

      if (!empty($lease)) {
        UserFiles::where('fileId', 'moveIn')->where('userUid', $studentId)->where('year', $this_year)->update($data);
        $action_type = "Regenerated Lease";
      } else {
        $data['fileId'] = 'moveIn';
        UserFiles::insert($data);
        $action_type = "Generated Lease";
      }

      ActionList::create([
        "userUid" => $studentId,
        "action" => $action_type,
        "actionUserUid" => Auth::user()->id,
        "actionUserDisplayName" => Auth::user()->userFirstName,
        "actionUserRole" => Auth::user()->userRole,
        "year" => $this_year,
      ]);

      // User::where('id', $studentId)->update(['rob_55_lease' => 0]);
      StudentsInfo::where('userUid', $studentId)->where('year', $this_year)->update(['rob_55_lease' => 0]);
      $prevLeaseToken = UserLeaseToken::where("userUid", $studentId)->get(); // ->where("active", 1)->where("signed", 0)

      if (!empty($prevLeaseToken)) {
        foreach ($prevLeaseToken as $token) {
          // UserLeaseToken::where('id', $token->id)->update(["active" => 0]);
          $token->delete();
        }
      }
      // Create Lease Link
      $leaseToken = UserLeaseToken::create(
        [
          "userUid" => $studentId,
          "leaseToken" => $this->generateRandomLongString(),
          "active" => 1,
          "signed" => 0,
          "year" => $this_year
        ]
      );
      // $userForLease = User::where('id', $studentId)->first();
      // $userForLease = StudentsInfo::where('userUid', $studentId)->where('year', $this_year)->first();

      $leaseOpenTag = "<a href='https://" . $_SERVER['HTTP_HOST'] . "/sign-lease/" . $leaseToken->leaseToken . "' target='_blank'>";
      Log::info('Sending email to ' . $leaseOpenTag);
      $userUidData = User::where('id', $studentId)->first();
      $this->dispatch(new SendResendLeaseMail($userUidData, $leaseToken->leaseToken, $leaseOpenTag, $this_year));

      // $message_replace['booking_application'] = "#".$booking->booking_code;
      // $message_replace['name'] = $userForLease->userFirstName;
      // $message_replace['lease_link'] = "<a href='https://".$_SERVER['HTTP_HOST']."/sign-lease/".$leaseToken->leaseToken."' target='_blank'>";
      // $message_replace['close_lease_link']="</a>";

      // $mailerForMessage = MailerList::where("mailer_name", "Application Approved")->first();

      // if($mailerForMessage->status == '1'){

      //   $mailerMessage = $mailerForMessage->message;

      //   $newMessage = str_replace("[booking_application]", $message_replace['booking_application'], $mailerMessage);
      //   $newMessage = str_replace("[name]", $message_replace['name'], $newMessage);
      //   $newMessage = str_replace("[lease_link]", $message_replace['lease_link'], $newMessage);
      //   $newMessage = str_replace("[close_lease_link]", $message_replace['close_lease_link'], $newMessage);

      //   $data['messageForMail'] = $newMessage;
      //   $data['email'] = $userForLease->userEmail;
      //   $data['name'] = $userForLease->userFirstName;
      //   $data['subject'] = str_replace("[application_number]", $booking->booking_code, $mailerForMessage->subject);



      //   $allowedMailsCC = Lookups::where('group_id', '11')->pluck('lookup_name')->first();
      //   $data['cc'] = explode(";", $allowedMailsCC);

      //   $allowedMails = Lookups::where('group_id', '10')->pluck('lookup_name')->first();

      //   if(!empty($allowedMails)){

      //     $mailArr = explode(";", $allowedMails);

      //     if(in_array(explode("@", $data['email'])[1], $mailArr)){
      //         $data['email'] = $userForLease->userEmail;
      //     }else{
      //         $data['email'] = "studentsca00@gmail.com";
      //     }
      //   }else{
      //     $data['email'] = $userForLease->userEmail;
      //   }

      //   Mail::send('email.green_back', $data, function($message) use ($data) {
      //       // $message->to($data['email'], $data['name'])->subject('Application Approved');
      //       // $message->to($data['email'])->subject($data['subject']);
      //       // $message->cc(['studentsca00@gmail.com', 'roger@whitehart.co.za', 'mirichea@whitehart.co.za']);
      //       $message->to($data['email'])->subject($data['subject']);
      //         $message->bcc($data['cc']);
      //         $message->from('noreply@campusafrica.co.za','Campus Africa');
      //       // $message->cc(['campusafricaforms@gmail.com', 'roger@whitehart.co.za']);
      //   });
      // }


    }

    return [UserFiles::where('fileId', 'moveIn')->where('userUid', $studentId)->where('year', $this_year)->first(), StudentsInfo::where('userUid', $studentId)->where('year', $this_year)->first()];
  }


  public function createTempLeasePDF(Request $request, $studentId)
  {

    $bookingId = $request->bookingId;
    $property = $request->property;
    $roomType = $request->roomType;

    $this_year = Lookups::resolveYearForUser((int) $studentId, Session::get('year'));


    $data = StudentsInfo::where('userUid', $studentId)->where('year', $this_year)->first();
    //dd($studentId);
    $booking = Booking::where('userId', $studentId)->where('year', $this_year)->first();
    // $propertyData = Property::where('id', $booking->property_data_id)->first();
    $residence = Residence::where('id', $booking->property)->first();
    // $leaseAdminFee = Lookups::where('group_id', '8')->first();
    $owner1 = storage_path() . '/leases/juanita-cronje.jpg';
    // Log::info('owner of the lease file: ' . $owner1);
    $owner = '/images/lease/juanita-cronje.jpg';
    // Log::info('owner of the lease file: ' . $owner);
    $logo = '/images/logo/temp_lease_logo.png';
    // Log::info('logo of the lease file: ' . $logo);
    $paymentBooking = BookingPayment::where('bookingId', $booking->id)->first();
    if ($data->userId == "") {
      $userIdentification = $data->userPassport;
    } else {
      $userIdentification = $data->userId;
    }


    $pdfdata = [
      'tenantName' => $data->userLastname . " " . $data->userFirstName,
      'tenantId' => $userIdentification,
      'propertyName' => $residence->residence,
      'propertyOwner' => $residence->owner,
      'propertyAddress' => $residence->address,
      'CompanyReg' => $residence->company_reg,
      'CompanyVat' => $residence->company_vat,
    ];
    /** @disregard P1009 */
    $pdf = PDF::loadView('PDF.temp_lease', compact('pdfdata', 'logo'));

    $bookingYear = substr($booking->year, -2);
    // $bookingPrefix = Residence::where('id', $booking->property)->pluck('building_prefix')->first();

    // $fileNameToSave = $bookingYear . $bookingPrefix . "_" . $booking->bed_code . "_" . $data->userLastname . "_" . $data->userFirstName . ".pdf";
    $fileNameToSave = $bookingYear . "_" . $data->userLastname . "_" . $data->userFirstName . ".pdf";
    // $fileNameToSave = 'lease.pdf';

    $content = $pdf->download($fileNameToSave)->getOriginalContent();
    // $user = User::where('id', $studentId)->first();
    /** @disregard P1009 */
    \Storage::put('public/files/' . $userIdentification . '/' . $fileNameToSave, $content);

    //Check if there is an existing lease to overwrite
    $tempLease = UserFiles::where('fileId', 'tempMoveIn')->where('userUid', $studentId)->where('year', $this_year)->first();

    $data = array(
      'userUid' => $studentId,
      'fileName' => $fileNameToSave,
      'fileUrl' => 'files/' . $userIdentification . '/' . $fileNameToSave,
      'status' => 'Rejected',
      'uploaded_by' => auth()->user()->name,
      'created_at' => date('Y-m-d h:m:s'),
      'year' => $this_year,
    );

    if (!empty($tempLease)) {
      UserFiles::where('fileId', 'tempMoveIn')->where('userUid', $studentId)->where('year', $this_year)->update($data);
      $action_type = "Regenerated Temp Lease";
    } else {
      $data['fileId'] = 'tempMoveIn';
      UserFiles::insert($data);
      $action_type = "Generated Temp Lease";
    }

    ActionList::create([
      "userUid" => $studentId,
      "action" => $action_type,
      "actionUserUid" => auth()->user()->id,
      "actionUserDisplayName" => auth()->user()->userFirstName,
      "actionUserRole" => auth()->user()->userRole,
      "year" => $this_year,
    ]);

    StudentsInfo::where('userUid', $studentId)->where('year', $this_year)->update(['rob_51_temp_lease_approve' => 0]);

    // Create Lease Link
    $leaseToken = UserLeaseToken::create(
      [
        "userUid" => $studentId,
        "leaseToken" => $this->generateRandomLongString(),
        "lease_type" => "temp_lease",
        "active" => 1,
        "signed" => 0,
        "year" => $this_year
      ]
    );

    $leaseOpenTag = "<a href='https://" . $_SERVER['HTTP_HOST'] . "/sign-temp-lease/" . $leaseToken->leaseToken . "' target='_blank'>";
    $userUidData = User::where('id', $studentId)->first();
    Log::info('send temp lease url : ' . $leaseOpenTag);
    SendResendTempLeaseMail::dispatch($userUidData, $leaseToken->leaseToken, $leaseOpenTag, $this_year);

    return [UserFiles::where('fileId', 'tempMoveIn')->where('userUid', $studentId)->where('year', $this_year)->first(), StudentsInfo::where('userUid', $studentId)->where('year', $this_year)->first()];
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
}

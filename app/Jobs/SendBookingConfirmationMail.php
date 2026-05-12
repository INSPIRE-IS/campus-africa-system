<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Models\Lookups;
use App\Models\User;
use App\Models\StudentsInfo;
use App\Models\Booking;
use App\Models\MailerList;
use App\Models\UserFiles;

use Illuminate\Support\Facades\Mail;
use App\Mail\GreenBackMail;
use Illuminate\Support\Facades\Session;

class SendBookingConfirmationMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $userUid;
    public $returnUrl;
    public $year;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $userUid, $returnUrl, $year = null)
    {
        $this->userUid = $userUid;
        $this->returnUrl = $returnUrl;
        $this->year = $year;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $userMain = $this->userUid;

        $user = User::where('id', $userMain->id)->first();
        if (!$user) {
            return;
        }

        // Prefer an explicit year passed from the controller; otherwise fall back to latest StudentsInfo, then active year.
        $this_year = (string) (int) $this->year;
        if ($this_year === '0') {
            $this_year = (string) (int) StudentsInfo::where('userUid', $userMain->id)->orderByDesc('year')->value('year');
        }
        if ($this_year === '0' || $this_year === '') {
            $this_year = (string) (int) Lookups::activeYearValue();
        }

        $userInfo = StudentsInfo::where('userUid', $userMain->id)->where('year', $this_year)->first();
        if (!$userInfo) {
            $userInfo = StudentsInfo::where('userUid', $userMain->id)->orderByDesc('year')->first();
        }
        if (!$userInfo) {
            return;
        }

        $booking = Booking::where('id', $userInfo->bookingId)->first();
        if (!$booking) {
            return;
        }


        $all = ['studentCard', 'proofRegistration'];
        $outstanding = [];

        if ($userInfo->studentType == "Cash / Direct") {
            array_push($all, "bankStatement");
        }
        if ($userInfo->studentType !== "Cash / Direct") {
            array_push($all, "bursaryApproval");
        }

        $allSearchable = UserFiles::where("userUid", $userMain->id)->where('year', $this->year)->get();

        foreach ($allSearchable as $search) {
            if (explode("_", $search->fileId)[0] == "other") {
                array_push($all, $search->fileId);
            }
        }

        foreach ($all as $file) {
            $file_search = UserFiles::where("userUid", $userMain->id)->where('fileId', $file)->where('year', $this->year)->first();

            if (($userInfo->studentType == "Cash / Direct" && $file !== 'bursaryApproval') || $userInfo->studentType != "Cash / Direct" && $file !== 'bankStatement') {

                if ($file == "studentCard") {
                    $fileName = "Student Card";
                } elseif ($file == "proofRegistration") {
                    $fileName = "Proof of Registration";
                } elseif ($file == "bankStatement") {
                    $fileName = "Bank Statements";
                } elseif ($file == "bursaryApproval") {
                    if ($userInfo->studentType != "NSFAS") {
                        $fileName = "Bursary Approval";
                    } else {
                        $fileName = "NSFAS Approval";
                    }
                } elseif (explode("_", $file)[0] == "other") {
                    $fileName = strtoupper(str_replace("_", " ", str_replace("other", "other |", $file)));
                }

                if ($file_search && !empty($file_search)) {
                    if ($file_search->status !== "Approved" && $file_search->status !== "Uploaded") {
                        array_push($outstanding, $fileName);
                    }
                } else {
                    array_push($outstanding, $fileName);
                }
            }
        }

        $outstandingDocsText = "<ol>";

        foreach ($outstanding as $out) {
            $outstandingDocsText .= "<li style='color: #fff; font-size: 21px; font-weight: 400; line-height: 30px;'>" . $out . "</li>";
        }

        $outstandingDocsText .= "</ol>";

        $genUser = User::where('id', $userMain->id)->first();

        $message_replace['bookingRef'] = $booking->booking_code;
        $message_replace['name'] = $userInfo->userFirstName;
        $message_replace['approvedDocs'] = "<ol><li style='color: #fff; font-size: 21px; font-weight: 400; line-height: 30px;'>Copy of ID Document</li><li style='color: #fff; font-size: 21px; font-weight: 400; line-height: 30px;'>Booking Fee Proof of Payment</li></ol>";
        $message_replace['missing'] = $outstandingDocsText;
        $message_replace['application_link'] = "<a href='" . $this->returnUrl . "' target='_blank'>";
        $message_replace['close_application_link'] = "</a>";

        $mailerForMessage = MailerList::where("mailer_name", "Booking Confirmation")->first();
        if (!$mailerForMessage) {
            return;
        }

        if ($mailerForMessage->status == '1') {

            $mailerMessage = $mailerForMessage->message;

            $newMessage = str_replace("[application_number]", "#" . $message_replace['bookingRef'], $mailerMessage);
            $newMessage = str_replace("[name]", $message_replace['name'], $newMessage);
            $newMessage = str_replace("[approved_document_list]", $message_replace['approvedDocs'], $newMessage);
            $newMessage = str_replace("[outstanding_document_list]", $message_replace['missing'], $newMessage);
            $newMessage = str_replace("[application_link]", $message_replace['application_link'], $newMessage);
            $newMessage = str_replace("[close_application_link]", $message_replace['close_application_link'], $newMessage);

            $data['name'] = $userInfo->userFirstName;
            $data['email'] = $userInfo->email;
            $data['messageForMail'] = $newMessage;
            $data['subject'] = str_replace("[application_number]", $message_replace['bookingRef'], $mailerForMessage->subject);

            $data['cc'] = "";
            $allowedMailsCC = Lookups::where('group_id', '11')->pluck('lookup_name')->first();
            if (!empty($allowedMailsCC)) {
                $data['cc'] = explode(";", $allowedMailsCC);
            }

            $allowedMails = Lookups::where('group_id', '10')->pluck('lookup_name')->first();

            if (!empty($allowedMails)) {
                $mailArr = explode(";", $allowedMails);

                if (in_array(explode("@", $data['email'])[1], $mailArr)) {
                    $data['email'] = $userInfo->email;
                } else {
                    $data['email'] = "studentsca00@gmail.com";
                }
            } else {
                $data['email'] = $userInfo->email;
            }

            $email = new GreenBackMail($newMessage, $data['subject'], 'noreply@campusafrica.co.za', 'Campus Africa', $data['cc']);
            Mail::mailer('smtp')->to($data['email'])->send($email);
        }
    }
}

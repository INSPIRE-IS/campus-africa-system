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
use App\Mail\YellowBackMail;
use Illuminate\Support\Facades\Session;

class SendReminderMail implements ShouldQueue
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
    public function __construct(User $userUid, $returnUrl, $year)
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
        $studentInfoForMail = StudentsInfo::where('userUid',  $userMain->id)->where('year', $this->year)->first();
        $booking = Booking::where('id', $studentInfoForMail->bookingId)->first();


        $all = ['studentCard', 'proofRegistration', 'copyID', 'proof_of_payment'];
        $outstanding = [];

        if ($studentInfoForMail->studentType == "Cash / Direct") {
            array_push($all, "bankStatement");
        }
        if ($studentInfoForMail->studentType !== "Cash / Direct") {
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

            if (($studentInfoForMail->studentType == "Cash / Direct" && $file !== 'bursaryApproval') || $studentInfoForMail->studentType != "Cash / Direct" && $file !== 'bankStatement') {

                if ($file == "studentCard") {
                    $fileName = "Student Card";
                } elseif ($file == "copyID") {
                    $fileName = "ID Document";
                } elseif ($file == "proof_of_payment") {
                    $fileName = "Proof of Payment";
                } elseif ($file == "proofRegistration") {
                    $fileName = "Proof of Registration";
                } elseif ($file == "bankStatement") {
                    $fileName = "Bank Statements";
                } elseif ($file == "bursaryApproval") {
                    if ($studentInfoForMail->studentType != "NSFAS") {
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
            $outstandingDocsText .= "<li style='color: #000; font-size: 21px; font-weight: 400; line-height: 30px;'>" . $out . "</li>";
        }

        $outstandingDocsText .= "</ol>";

        $message_replace['bookingRef'] = $booking->booking_code;
        $message_replace['name'] = $studentInfoForMail->userFirstName;
        $message_replace['application_link'] = "<a href='" . $this->returnUrl . "' target='_blank'>";
        $message_replace['close_application_link'] = "</a>";
        $message_replace['missing'] = $outstandingDocsText;

        $mailerForMessage = MailerList::where("mailer_name", "Reminder Mail")->first();

        if ($mailerForMessage->status == '1') {

            $mailerMessage = $mailerForMessage->message;

            $newMessage = str_replace("[application_number]", "#" . $message_replace['bookingRef'], $mailerMessage);
            $newMessage = str_replace("[name]", $message_replace['name'], $newMessage);
            $newMessage = str_replace("[outstanding_document_list]", $message_replace['missing'], $newMessage);
            $newMessage = str_replace("[application_link]", $message_replace['application_link'], $newMessage);
            $newMessage = str_replace("[close_application_link]", $message_replace['close_application_link'], $newMessage);

            $mailData['name'] = $studentInfoForMail->userFirstName;
            $mailData['email'] = $user->email;
            $mailData['messageForMail'] = $newMessage;
            $mailData['subject'] = str_replace("[application_number]", $message_replace['bookingRef'], $mailerForMessage->subject);

            $mailData['cc'] = "";
            $allowedMailsCC = Lookups::where('group_id', '11')->pluck('lookup_name')->first();
            if (!empty($allowedMailsCC)) {
                $mailData['cc'] = explode(";", $allowedMailsCC);
            }

            $allowedMails = Lookups::where('group_id', '10')->pluck('lookup_name')->first();

            if (!empty($allowedMails)) {

                $mailArr = explode(";", $allowedMails);

                if (in_array(explode("@", $mailData['email'])[1], $mailArr)) {
                    $mailData['email'] = $studentInfoForMail->userEmail;
                } else {
                    $mailData['email'] = "studentsca00@gmail.com";
                }
            } else {
                $mailData['email'] = $studentInfoForMail->userEmail;
            }

            $email = new YellowBackMail($newMessage, $mailData['subject'], 'noreply@campusafrica.co.za', 'Campus Africa', $mailData['cc']);
            Mail::mailer('smtp')->to($mailData['email'])->send($email);
        }
    }
}

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

use Illuminate\Support\Facades\Mail;
use App\Mail\GreenBackMail;
use Illuminate\Support\Facades\Session;

class SendApproveDocMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $userUid;
    public $typeName;
    public $returnUrl;
    public $year;
    public $year;

    /**
     * Create a new job instance.
     *
     * @param App\Models\User $userUid
     * @return void
     */
    public function __construct(User $userUid, $typeName, $returnUrl, $year = null)
    {
        //
        $this->userUid = $userUid;
        $this->typeName = $typeName;
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
        $userId = $this->userUid;

        $user = User::where('id', $userId->id)->first();
        if (!$user) {
            return;
        }

        // Prefer an explicit year passed from the controller (doc approval happens in a year-specific context).
        $this_year = (string) (int) $this->year;
        if ($this_year === '0') {
            $this_year = (string) (int) StudentsInfo::where('userUid', $userId->id)->orderByDesc('year')->value('year');
        }
        if ($this_year === '0' || $this_year === '') {
            $this_year = (string) (int) Lookups::activeYearValue();
        }

        $studentInfoForMail = StudentsInfo::where('userUid', $userId->id)->where('year', $this_year)->first();
        if (!$studentInfoForMail) {
            // Fallback: use the latest StudentsInfo row if a year-specific row doesn't exist.
            $studentInfoForMail = StudentsInfo::where('userUid', $userId->id)->orderByDesc('year')->first();
        }
        if (!$studentInfoForMail) {
            return;
        }

        $booking = Booking::where('id', $studentInfoForMail->bookingId)->first();
        if (!$booking) {
            return;
        }

        $message_replace['bookingRef'] = $booking->booking_code;
        $message_replace['name'] = $studentInfoForMail->userFirstName;
        $message_replace['document_name'] =  $this->typeName;
        $message_replace['application_link'] = "<a href='" . $this->returnUrl . "' target='_blank'>";
        $message_replace['close_application_link'] = "</a>";

        $mailerForMessage = MailerList::where("mailer_name", "Document Approved")->first();
        if (!$mailerForMessage) {
            return;
        }

        if ($mailerForMessage->status == '1') {

            $mailerMessage = $mailerForMessage->message;

            $newMessage = str_replace("[application_number]", "#" . $message_replace['bookingRef'], $mailerMessage);
            $newMessage = str_replace("[document_name]", $message_replace['document_name'], $newMessage);
            $newMessage = str_replace("[document_name]", $message_replace['document_name'], $newMessage);
            $newMessage = str_replace("[name]", $message_replace['name'], $newMessage);
            $newMessage = str_replace("[application_link]", $message_replace['application_link'], $newMessage);
            $newMessage = str_replace("[close_application_link]", $message_replace['close_application_link'], $newMessage);

            $mailData['name'] = $studentInfoForMail->userFirstName;
            $mailData['email'] = $studentInfoForMail->userEmail;
            $mailData['messageForMail'] = $newMessage;
            $mailData['subject'] = str_replace("[document_name]", $this->typeName, str_replace("[application_number]", $message_replace['bookingRef'], $mailerForMessage->subject));

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

            $email = new GreenBackMail($newMessage, $mailData['subject'], 'noreply@campusafrica.co.za', 'Campus Africa', $mailData['cc']);

            Mail::mailer('smtp')->to($mailData['email'])->send($email);
        }
    }
}

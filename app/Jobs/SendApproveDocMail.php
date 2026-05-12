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

class SendApproveDocMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $userUid;
    public $typeName;
    public $returnUrl;
    public $year;

    /**
     * Create a new job instance.
     *
     * @param App\Models\User $userUid
     * @return void
     */
    public function __construct(User $userUid, $typeName, $returnUrl)
    {
        //
        $this->userUid = $userUid;
        $this->typeName = $typeName;
        $this->returnUrl = $returnUrl;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        $userId = $this->userUid;

        $user = User::where('id', $userId->id)->first();
        $studentInfoForMail = StudentsInfo::where('userUid',  $userId->id)->where('year', $this_year)->first();
        $booking = Booking::where('id', $studentInfoForMail->bookingId)->first();

        $message_replace['bookingRef'] = $booking->booking_code;
        $message_replace['name'] = $studentInfoForMail->userFirstName;
        $message_replace['document_name'] =  $this->typeName;
        $message_replace['application_link'] = "<a href='" . $this->returnUrl . "' target='_blank'>";
        $message_replace['close_application_link'] = "</a>";

        $mailerForMessage = MailerList::where("mailer_name", "Document Approved")->first();

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

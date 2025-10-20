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
use App\Mail\RedBackMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class SendDeclinedDocMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $userUid;
    public $typeName;
    public $reason;
    public $redirectUrl;
    public $year;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $userUid, $typeName, $reason, $redirectUrl, $year)
    {
        //
        $this->userUid = $userUid;
        $this->typeName = $typeName;
        $this->reason = $reason;
        $this->redirectUrl = $redirectUrl;
        $this->year = $year;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {

            $userId = $this->userUid;

            $userMain = User::where('id', $userId->id)->first();
            $userInfo = StudentsInfo::where('userUid', $userId->id)->where('year', $this->year)->first();
            $booking = Booking::where('id', $userInfo->bookingId)->first();


            $message_replace['bookingRef'] = $booking->booking_code;
            $message_replace['name'] = $userInfo->userFirstName;
            $message_replace['document_name'] = $this->typeName;
            $message_replace['decline_reason'] = $this->reason;
            $message_replace['application_link'] = "<a href='" . $this->redirectUrl . "' target='_blank'>";
            $message_replace['close_application_link'] = "</a>";

            $mailerForMessage = MailerList::where("mailer_name", "Document Declined")->first();

            if ($mailerForMessage->status == '1') {

                $mailerMessage = $mailerForMessage->message;

                $newMessage = str_replace("[application_number]", "#" . $message_replace['bookingRef'], $mailerMessage);
                $newMessage = str_replace("[name]", $message_replace['name'], $newMessage);
                $newMessage = str_replace("[document_name]", $message_replace['document_name'], $newMessage);
                $newMessage = str_replace("[decline_reason]", $message_replace['decline_reason'], $newMessage);
                $newMessage = str_replace("[application_link]", $message_replace['application_link'], $newMessage);
                $newMessage = str_replace("[close_application_link]", $message_replace['close_application_link'], $newMessage);

                $newSubject = str_replace("[application_number]", $message_replace['bookingRef'], $mailerForMessage->subject);
                $newSubject = str_replace("[document_name]", $message_replace['document_name'], $newSubject);

                $mailData['name'] = $userInfo->userFirstName;
                $mailData['email'] = $userInfo->userEmail;
                $mailData['messageForMail'] = $newMessage;
                $mailData['subject'] = $newSubject;


                $mailData['cc'] = "";
                $allowedMailsCC = Lookups::where('group_id', '11')->pluck('lookup_name')->first();
                if (!empty($allowedMailsCC)) {
                    $mailData['cc'] = explode(";", $allowedMailsCC);
                }

                $allowedMails = Lookups::where('group_id', '10')->pluck('lookup_name')->first();

                if (!empty($allowedMails)) {

                    $mailArr = explode(";", $allowedMails);

                    if (in_array(explode("@", $mailData['email'])[1], $mailArr)) {
                        $mailData['email'] = $userInfo->userEmail;
                    } else {
                        $mailData['email'] = "conurbandigital@gmail.com";
                    }
                } else {
                    $mailData['email'] = $userInfo->userEmail;
                }

                $email = new RedBackMail($newMessage, $mailData['subject'], 'noreply@campusafrica.co.za', 'Campus Africa', $mailData['cc']);
                Mail::mailer('smtp')->to($mailData['email'])->send($email);
            }
        } catch (\Exception $e) {
            Log::error('Error in SendDeclinedDocMail job', [
                'error' => $e->getMessage(),
                'user_id' => $this->userUid->id ?? null,
                'year' => $this->year,
                'bookingId' => $userInfo->bookingId,
            ]);
        }
    }
}

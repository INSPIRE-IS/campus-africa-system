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

class SendApplicationThankYouMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $userUid;
    public $securityToken;
    public $returnUrl;
    public $year;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $userUid, $securityToken, $returnUrl)
    {
        $this->userUid = $userUid;
        $this->securityToken = $securityToken;
        $this->returnUrl = $returnUrl;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $userMain = $this->userUid;

        $this_year = Lookups::where('group_id', '4')->first();
        $this_year = $this_year->lookup_name;

        $user = User::where('id', $userMain->id)->first();
        $userForBooking = StudentsInfo::where('userUid', $userMain->id)->where('year', $this_year)->first();

        $bookinfRefNo = Booking::where('id', $userForBooking->bookingId)->first();

        $message_replace['bookingRef'] = $bookinfRefNo->booking_code;
        $message_replace['name'] = $userForBooking->userFirstName;
        $message_replace['security_token'] = $this->securityToken;
        $message_replace['application_link'] = "<a href='" . $this->returnUrl . "' target='_blank'>";
        $message_replace['close_application_link'] = "</a>";

        $mailerForMessage = MailerList::where("mailer_name", "Application Thank You")->first();

        if ($mailerForMessage->status == '1') {

            $mailerMessage = $mailerForMessage->message;

            $newMessage = str_replace("[application_number]", "#" . $message_replace['bookingRef'], $mailerMessage);
            $newMessage = str_replace("[name]", $message_replace['name'], $newMessage);
            $newMessage = str_replace("[security_code]", $message_replace['security_token'], $newMessage);
            $newMessage = str_replace("[application_link]", $message_replace['application_link'], $newMessage);
            $newMessage = str_replace("[close_application_link]", $message_replace['close_application_link'], $newMessage);

            $data['name'] = $userForBooking->userFirstName;
            $data['email'] = $userForBooking->userEmail;
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
                    $data['email'] = $userForBooking->userEmail;
                } else {
                    $data['email'] = "studentsca00@gmail.com";
                }
            } else {
                $data['email'] = $userForBooking->userEmail;
            }

            $email = new YellowBackMail($newMessage, $data['subject'], 'noreply@campusafrica.co.za', 'Campus Africa', $data['cc']);
            Mail::mailer('smtp')->to($data['email'])->send($email);
        }
    }
}

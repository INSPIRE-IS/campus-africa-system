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
use App\Models\MailerList;

use Illuminate\Support\Facades\Mail;
use App\Mail\YellowBackMail;

class SendStudentVerifyMailByAdminMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $userUid;
    public $bookingGenRef;
    public $url;
    public $this_year;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $userUid, $this_year, $bookingGenRef, $url)
    {
        $this->userUid = $userUid;
        $this->bookingGenRef = $bookingGenRef;
        $this->url = $url;
        $this->this_year = $this_year;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $userMain = $this->userUid;

        // $this_year = Lookups::where('group_id', '4')->where('is_active_year', '1')->first();
        // $this_year = $this_year->lookup_name;

        $user = User::where('id', $userMain->id)->first();
        $userInfo = StudentsInfo::where('userUid', $userMain->id)->where('year', $this->this_year)->first();

        $message_replace['bookingRef'] = $this->bookingGenRef;
        $message_replace['name'] = $userInfo->userFirstName;
        $message_replace['application_link'] = "<a href='" . $this->url . "' target='_blank'>";
        $message_replace['close_application_link'] = "</a>";

        $mailerForMessage = MailerList::where("mailer_name", "Password Validation From Admin")->first();

        if ($mailerForMessage->status == '1') {

            $mailerMessage = $mailerForMessage->message;

            $newMessage = str_replace("[application_number]", "#" . $message_replace['bookingRef'], $mailerMessage);
            $newMessage = str_replace("[name]", $message_replace['name'], $newMessage);
            $newMessage = str_replace("[application_link]", $message_replace['application_link'], $newMessage);
            $newMessage = str_replace("[close_application_link]", $message_replace['close_application_link'], $newMessage);

            $data['name'] = $userInfo->userFirstName;
            $data['email'] = $userInfo->userEmail;
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
                    $data['email'] = $user->email;
                } else {
                    $data['email'] = "studentsca00@gmail.com";
                }
            } else {
                $data['email'] = $user->email;
            }

            $email = new YellowBackMail($newMessage, $data['subject'], 'noreply@campusafrica.co.za', 'Campus Africa', $data['cc']);
            Mail::mailer('smtp')->to($data['email'])->send($email);
        }
    }
}

<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use Grosv\LaravelPasswordlessLogin\LoginUrl;

use App\Models\Lookups;
use App\Models\User;
use App\Models\StudentsInfo;
use App\Models\Booking;
use App\Models\MailerList;
use App\Models\UserFiles;

use Illuminate\Support\Facades\Mail;
use App\Mail\YellowBackMail;
use Illuminate\Support\Facades\Session;

class SendNewBookingMail implements ShouldQueue
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

        $genUser = User::where('id', $userMain->id)->first();
        if (!$genUser) {
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

        $userForBooking = StudentsInfo::where('userUid', $userMain->id)->where('year', $this_year)->first();
        if (!$userForBooking) {
            $userForBooking = StudentsInfo::where('userUid', $userMain->id)->orderByDesc('year')->first();
        }
        if (!$userForBooking) {
            return;
        }
        $newBooking = Booking::where('id', $userForBooking->bookingId)->first();
        if (!$newBooking) {
            return;
        }

        $message_replace['bookingRef'] = $newBooking->booking_code;
        $message_replace['name'] = $userForBooking->userFirstName;
        $message_replace['application_link'] = "<a href='" . $this->returnUrl . "' target='_blank'>";
        $message_replace['close_application_link'] = "</a>";

        $mailerForMessage = MailerList::where("mailer_name", "New Booking")->first();
        if (!$mailerForMessage) {
            return;
        }

        if ($mailerForMessage->status == '1') {

            $mailerMessage = $mailerForMessage->message;

            $newMessage = str_replace("[application_number]", "#" . $message_replace['bookingRef'], $mailerMessage);
            $newMessage = str_replace("[name]", $message_replace['name'], $newMessage);
            $newMessage = str_replace("[application_link]", $message_replace['application_link'], $newMessage);
            $newMessage = str_replace("[close_application_link]", $message_replace['close_application_link'], $newMessage);

            $data['name'] = $userForBooking->userFirstName;
            $data['email'] = $genUser->email;
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
                    $data['email'] = $genUser->email;
                } else {
                    $data['email'] = "studentsca00@gmail.com";
                }
            } else {
                $data['email'] = $genUser->email;
            }

            $email = new YellowBackMail($newMessage, $data['subject'], 'noreply@campusafrica.co.za', 'Campus Africa', $data['cc']);
            Mail::mailer('smtp')->to($data['email'])->send($email);
        }
    }
}

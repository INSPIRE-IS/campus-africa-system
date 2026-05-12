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

class SendLeaseApprovedMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $userUid;
    public $year;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $userUid)
    {
        $this->userUid = $userUid;
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

        $userForLease = StudentsInfo::where('userUid', $userMain->id)->where('year', $this_year)->first();
        $booking = Booking::where('id', $userForLease->bookingId)->first();


        $message_replace['booking_application'] = "#" . $booking->booking_code;
        $message_replace['name'] = $userForLease->userFirstName;

        $mailerForMessage = MailerList::where("mailer_name", "Lease Approved")->first();

        if ($mailerForMessage->status == '1') {

            $mailerMessage = $mailerForMessage->message;

            $newMessage = str_replace("[booking_application]", $message_replace['booking_application'], $mailerMessage);
            $newMessage = str_replace("[name]", $message_replace['name'], $newMessage);

            $fileForUser = UserFiles::where('userUid', $userMain->id)->where('year', $this_year)->where('fileId', 'moveIn')->first();

            $data['messageForMail'] = $newMessage;
            $data['email'] = $userForLease->userEmail;
            $data['name'] = $userForLease->userFirstName;
            $data['subject'] = str_replace("[application_number]", $booking->booking_code, $mailerForMessage->subject);

            $data['cc'] = "";
            $allowedMailsCC = Lookups::where('group_id', '11')->pluck('lookup_name')->first();
            if (!empty($allowedMailsCC)) {
                $data['cc'] = explode(";", $allowedMailsCC);
            }

            $allowedMails = Lookups::where('group_id', '10')->pluck('lookup_name')->first();

            if (!empty($allowedMails)) {

                $mailArr = explode(";", $allowedMails);

                if (in_array(explode("@", $data['email'])[1], $mailArr)) {
                    $data['email'] = $userForLease->userEmail;
                } else {
                    $data['email'] = "studentsca00@gmail.com";
                }
            } else {
                $data['email'] = $userForLease->userEmail;
            }

            $email = new GreenBackMail($newMessage, $data['subject'], 'noreply@campusafrica.co.za', 'Campus Africa', $data['cc']);
            Mail::mailer('smtp')->to($data['email'])->send($email);
        }
    }
}

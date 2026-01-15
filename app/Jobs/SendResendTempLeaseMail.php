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
use Illuminate\Support\Facades\Log;

class SendResendTempLeaseMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $userUid;
    public $randomString;
    public $leaseOpenTag;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $userUid, $randomString, $leaseOpenTag)
    {
        $this->userUid = $userUid;
        $this->randomString = $randomString;
        $this->leaseOpenTag = $leaseOpenTag;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            Log::info('SendResendLeaseMail job started for user: ' . $this->userUid->id);
            $userMain = $this->userUid;

            $this_year = Lookups::where('group_id', '4')->first();
            $this_year = $this_year->lookup_name;

            $userForLease = StudentsInfo::where('userUid', $userMain->id)->where('year', $this_year)->first();
            $booking = Booking::where('id', $userForLease->bookingId)->first();
            if (!$userForLease || !$booking) {
                Log::error('Temp lease mail aborted – missing data', [
                    'user_id' => $this->userUid->id
                ]);
                return;
            }
            $message_replace['booking_application'] = "#" . $booking->booking_code;
            $message_replace['name'] = $userForLease->userFirstName;
            $message_replace['lease_link'] = $this->leaseOpenTag;
            $message_replace['close_lease_link'] = "</a>";

            $mailerForMessage = MailerList::where("mailer_name", "Temp Lease Application")->first();
            Log::info($mailerForMessage);

            if (!$mailerForMessage || $mailerForMessage->status == '1') {

                $mailerMessage = $mailerForMessage->message;

                $newMessage = str_replace("[booking_application]", $message_replace['booking_application'], $mailerMessage);
                $newMessage = str_replace("[name]", $message_replace['name'], $newMessage);
                $newMessage = str_replace("[lease_link]", $message_replace['lease_link'], $newMessage);
                $newMessage = str_replace("[close_lease_link]", $message_replace['close_lease_link'], $newMessage);

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
                        $data['email'] = "conurbandigital@gmail.com";
                    }
                } else {
                    $data['email'] = $userForLease->userEmail;
                }

                Log::info('Sending email to ' . $data['email']);
                $email = new GreenBackMail($newMessage, $data['subject'], 'noreply@campusafrica.co.za', 'Campus Africa', $data['cc']);
                Mail::mailer('smtp')->to($data['email'])->send($email);
            } else {
                Log::error('Temp lease mail aborted – mailer not found', [
                    'user_id' => $this->userUid->id
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error in SendResendLeaseMail job', [
                'error' => $e->getMessage(),
                'user_id' => $this->userUid->id ?? null,
            ]);
        }
    }
}

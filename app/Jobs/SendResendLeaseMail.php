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
use Illuminate\Support\Facades\Session;

class SendResendLeaseMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $userUid;
    public $randomString;
    public $leaseOpenTag;
    public $year;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $userUid, $randomString, $leaseOpenTag, $year = null)
    {
        $this->userUid = $userUid;
        $this->randomString = $randomString;
        $this->leaseOpenTag = $leaseOpenTag;
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
            $now = date('Y-m-d h:m:s');
            Log::info('SendResendLeaseMail job started for user: ' . $this->userUid->id);
            Log::info('SendResendLeaseMail job started for at ' . $now);
            Log::info('SendResendLeaseMail job tag: ' . $this->leaseOpenTag);
            $userMain = $this->userUid;

            if (!$userMain) {
                return;
            }

            // Prefer an explicit year passed from the controller (lease resend is year-specific).
            $this_year = (string) (int) $this->year;
            if ($this_year === '0') {
                $this_year = (string) (int) StudentsInfo::where('userUid', $userMain->id)->orderByDesc('year')->value('year');
            }
            if ($this_year === '0' || $this_year === '') {
                $this_year = (string) (int) Lookups::activeYearValue();
            }

            $userForLease = StudentsInfo::where('userUid', $userMain->id)->where('year', $this_year)->first();
            if (!$userForLease) {
                // Fallback: use latest StudentsInfo row if the year-specific one doesn't exist.
                $userForLease = StudentsInfo::where('userUid', $userMain->id)->orderByDesc('year')->first();
            }
            if (!$userForLease) {
                return;
            }

            $booking = Booking::where('id', $userForLease->bookingId)->first();
            if (!$booking) {
                return;
            }

            $message_replace['booking_application'] = "#" . $booking->booking_code;
            $message_replace['name'] = $userForLease->userFirstName;
            $message_replace['lease_link'] = $this->leaseOpenTag;
            $message_replace['close_lease_link'] = "</a>";

            $mailerForMessage = MailerList::where("mailer_name", "Application Approved")->first();
            if (!$mailerForMessage) {
                return;
            }

            if ($mailerForMessage->status == '1') {

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
                        $data['email'] = "studentsca00@gmail.com";
                    }
                } else {
                    $data['email'] = $userForLease->userEmail;
                }

                //Log::info('Sending email to ' . $data['email']);
                $email = new GreenBackMail($newMessage, $data['subject'], 'noreply@campusafrica.co.za', 'Campus Africa', $data['cc']);
                Mail::mailer('smtp')->to($data['email'])->send($email);
            }
        } catch (\Exception $e) {
            Log::error('Error in SendResendLeaseMail job', [
                'error' => $e->getMessage(),
                'user_id' => $this->userUid->id ?? 'not set user id',
                'stud_id' => $userForLease->id ?? 'not set student id',
                'year' => $this_year ?? 'not set year',
                'bookingId' => $booking?->id ?? 'not set booking id',
            ]);
        }
    }
}

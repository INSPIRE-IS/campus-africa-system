<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Models\Booking;
use App\Models\Lookups;
use App\Models\MailerList;
use App\Models\StudentsInfo;
use App\Models\User;

use App\Mail\GreenBackMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendResendTempLeaseMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $userUid;
    public $randomString;
    public $leaseOpenTag;
    public $year;

    public function __construct(User $userUid, $randomString, $leaseOpenTag, $year = null)
    {
        $this->userUid = $userUid;
        $this->randomString = $randomString;
        $this->leaseOpenTag = $leaseOpenTag;
        $this->year = $year;
    }

    public function handle()
    {
        try {
            Log::info('SendResendTempLeaseMail job started for user: ' . ($this->userUid->id ?? 'null'));
            Log::info('SendResendTempLeaseMail job tag: ' . $this->leaseOpenTag);

            $userMain = $this->userUid;
            if (!$userMain) {
                return;
            }

            // Prefer an explicit year passed from the controller (temp-lease resend is year-specific).
            $this_year = (string) (int) $this->year;
            if ($this_year === '0') {
                $this_year = (string) (int) StudentsInfo::where('userUid', $userMain->id)->orderByDesc('year')->value('year');
            }
            if ($this_year === '0' || $this_year === '') {
                $this_year = (string) (int) Lookups::activeYearValue();
            }

            $userForLease = StudentsInfo::where('userUid', $userMain->id)->where('year', $this_year)->first();
            if (!$userForLease) {
                $userForLease = StudentsInfo::where('userUid', $userMain->id)->orderByDesc('year')->first();
            }
            if (!$userForLease) {
                Log::error('Temp lease mail aborted - missing StudentsInfo', ['user_id' => $userMain->id]);
                return;
            }

            $booking = Booking::where('id', $userForLease->bookingId)->first();
            if (!$booking) {
                Log::error('Temp lease mail aborted - missing Booking', ['user_id' => $userMain->id]);
                return;
            }

            $mailerForMessage = MailerList::where('mailer_name', 'Temp Lease Application')->first();
            if (!$mailerForMessage || $mailerForMessage->status != '1') {
                Log::error('Temp lease mail aborted - mailer not found/disabled', ['user_id' => $userMain->id]);
                return;
            }

            $message_replace['booking_application'] = '#' . $booking->booking_code;
            $message_replace['name'] = $userForLease->userFirstName;
            $message_replace['lease_link'] = $this->leaseOpenTag;
            $message_replace['close_lease_link'] = '</a>';

            $mailerMessage = $mailerForMessage->message;
            $newMessage = str_replace('[booking_application]', $message_replace['booking_application'], $mailerMessage);
            $newMessage = str_replace('[name]', $message_replace['name'], $newMessage);
            $newMessage = str_replace('[lease_link]', $message_replace['lease_link'], $newMessage);
            $newMessage = str_replace('[close_lease_link]', $message_replace['close_lease_link'], $newMessage);

            $data['messageForMail'] = $newMessage;
            $data['email'] = $userForLease->userEmail;
            $data['name'] = $userForLease->userFirstName;
            $data['subject'] = str_replace('[application_number]', $booking->booking_code, $mailerForMessage->subject);

            $data['cc'] = '';
            $allowedMailsCC = Lookups::where('group_id', '11')->pluck('lookup_name')->first();
            if (!empty($allowedMailsCC)) {
                $data['cc'] = explode(';', $allowedMailsCC);
            }

            $allowedMails = Lookups::where('group_id', '10')->pluck('lookup_name')->first();
            if (!empty($allowedMails)) {
                $mailArr = explode(';', $allowedMails);
                if (!in_array(explode('@', $data['email'])[1], $mailArr)) {
                    $data['email'] = 'studentsca00@gmail.com';
                }
            }

            $email = new GreenBackMail($newMessage, $data['subject'], 'noreply@campusafrica.co.za', 'Campus Africa', $data['cc']);
            Mail::mailer('smtp')->to($data['email'])->send($email);
        } catch (\Exception $e) {
            Log::error('Error in SendResendTempLeaseMail job', [
                'error' => $e->getMessage(),
                'user_id' => $this->userUid->id ?? null,
            ]);
        }
    }
}


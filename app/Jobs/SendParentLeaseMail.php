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
use Illuminate\Support\Facades\Log;

class SendParentLeaseMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $student;
    public $randomString;
    public $leaseOpenTag;
    public $year;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $student, $randomString, $leaseOpenTag, $year)
    {
        $this->student = $student;
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
            Log::info('SendParentLeaseMail job started for student: ' . $this->student->id);


            $studentInfo = StudentsInfo::where('userUid', $this->student->id)->where('year', $this->year)->first();

            if (!$studentInfo) {
                Log::error('No StudentsInfo found for student ' . $this->student->id . ' year ' . $this->year);
                return;
            }
            Log::info('SendParentLeaseMail 1');
            $booking = Booking::where('id', $studentInfo->bookingId)->first();

            $message_replace['booking_application'] = "#" . $booking->booking_code;
            $message_replace['name'] = $studentInfo->userFirstName;
            $message_replace['lease_link'] = $this->leaseOpenTag;
            $message_replace['close_lease_link'] = "</a>";
            $message_replace['sponsor_name'] = trim(($studentInfo->sponFirstName ?? '') . ' ' . ($studentInfo->sponLastName ?? ''));
            $message_replace['student_name'] = trim(($studentInfo->userFirstName ?? '') . ' ' . ($studentInfo->userLastname ?? ''));
            $message_replace['application_year'] = $this->year;
            $message_replace['property_name'] = $booking->propertyName ?? '';

            $mailerForMessage = MailerList::where("mailer_name", "Sponsor Agreement")->first();

            if ($mailerForMessage && $mailerForMessage->status == '1') {
                Log::info('SendParentLeaseMail 2');
                $mailerMessage = $mailerForMessage->message;

                $newMessage = str_replace("[booking_application]", $message_replace['booking_application'], $mailerMessage);
                $newMessage = str_replace("[name]", $message_replace['name'], $newMessage);
                $newMessage = str_replace("[lease_link]", $message_replace['lease_link'], $newMessage);
                $newMessage = str_replace("[close_lease_link]", $message_replace['close_lease_link'], $newMessage);
                $newMessage = str_replace("[sponsor_name]", $message_replace['sponsor_name'], $newMessage);
                $newMessage = str_replace("[student_name]", $message_replace['student_name'], $newMessage);
                $newMessage = str_replace("[application_year]", $message_replace['application_year'], $newMessage);
                $newMessage = str_replace("[property_name]", $message_replace['property_name'], $newMessage);
                // "sponFirstName":null,"sponLastName":null,"sponCellCode":null,"sponCell":null,"sponEmail":"test@email.com"
                // ✅ Choose email more safely
                $recipientEmail = $studentInfo->sponEmail
                    ?? $studentInfo->userEmail
                    ?? $this->student->email;

                if (empty($recipientEmail)) {
                    Log::error('Parent email not found for student ' . $this->student->id);
                    return;
                }
                Log::info('SendParentLeaseMail 3');
                $data['messageForMail'] = $newMessage;
                $data['email'] = $recipientEmail;
                $data['name'] = $studentInfo->userFirstName;
                $data['subject'] = str_replace("[application_number]", $booking->booking_code, $mailerForMessage->subject);

                $data['cc'] = "";
                $allowedMailsCC = Lookups::where('group_id', '11')->pluck('lookup_name')->first();
                if (!empty($allowedMailsCC)) {
                    $data['cc'] = explode(";", $allowedMailsCC);
                }
                Log::info('SendParentLeaseMail 4');
                $allowedMails = Lookups::where('group_id', '10')->pluck('lookup_name')->first();

                if (!empty($allowedMails)) {

                    $mailArr = explode(";", $allowedMails);

                    if (in_array(explode("@", $data['email'])[1], $mailArr)) {
                        $data['email'] = $recipientEmail;
                    } else {
                        $data['email'] = "conurbandigital@gmail.com";
                    }
                } else {
                    $data['email'] = $recipientEmail;
                }

                Log::info('Sending email to 5 ' . $data['email']);
                $email = new GreenBackMail($newMessage, $data['subject'], 'noreply@campusafrica.co.za', 'Campus Africa', $data['cc']);
                Mail::mailer('smtp')->to($data['email'])->send($email);
            }
        } catch (\Exception $e) {
            Log::error('Error in SendResendLeaseMail job', [
                'error' => $e->getMessage(),
                'student_id' => $this->student->id ?? null,
            ]);
        }
    }
}

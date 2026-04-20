<?php

namespace App\Jobs;

use App\Mail\GreenBackMail;
use App\Models\Booking;
use App\Models\Lookups;
use App\Models\StudentsInfo;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendAssurityMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $userUid;
    public $year;
    public $signUrl;

    public function __construct($userUid, $year, $signUrl)
    {
        $this->userUid = $userUid;
        $this->year = $year;
        $this->signUrl = $signUrl;
    }

    public function handle()
    {
        try {
            $student = StudentsInfo::where('userUid', $this->userUid)->where('year', $this->year)->first();
            if (!$student) {
                Log::warning('SendAssurityMail: missing student', ['userUid' => $this->userUid, 'year' => $this->year]);
                return;
            }

            $booking = Booking::where('userId', $this->userUid)->where('year', $this->year)->first();
            $bookingCode = $booking ? $booking->booking_code : '';

            $cc = [];
            $allowedMailsCC = Lookups::where('group_id', '11')->pluck('lookup_name')->first();
            if (!empty($allowedMailsCC)) {
                $cc = explode(';', $allowedMailsCC);
            }

            $allowedMails = Lookups::where('group_id', '10')->pluck('lookup_name')->first();
            $allowedDomains = [];
            if (!empty($allowedMails)) {
                $allowedDomains = explode(';', $allowedMails);
            }

            $sponsorEmail = trim((string) ($student->sponEmail ?? ''));
            if ($sponsorEmail !== '') {
                $sponsorName = trim(($student->sponFirstName ?? '') . ' ' . ($student->sponLastName ?? ''));
                if ($sponsorName === '') {
                    $sponsorName = 'Sponsor';
                }

                $sponsorMessage = implode('<br>', [
                    "Dear {$sponsorName},",
                    '',
                    "Please sign the Deed of Suretyship (Debt Assurity) for application #{$bookingCode}.",
                    "Sign here: <a href=\"{$this->signUrl}\" target=\"_blank\">Open Document</a>",
                    '',
                    'Thank you.',
                ]);

                $email = $sponsorEmail;
                if (!empty($allowedDomains)) {
                    $domain = explode('@', $email)[1] ?? '';
                    if (!in_array($domain, $allowedDomains)) {
                        $email = 'studentsca00@gmail.com';
                    }
                }

                $mail = new GreenBackMail($sponsorMessage, 'Debt Assurity Signature Required', 'noreply@campusafrica.co.za', 'Campus Africa', $cc);
                Mail::mailer('smtp')->to($email)->send($mail);
            }

            $studentEmail = trim((string) ($student->userEmail ?? ''));
            if ($studentEmail !== '') {
                $studentMessage = implode('<br>', [
                    "Dear {$student->userFirstName},",
                    '',
                    "Your sponsor has been requested to sign the Deed of Suretyship (Debt Assurity) for application #{$bookingCode}.",
                    'Please remind your sponsor to complete the signature.',
                    '',
                    'Thank you.',
                ]);

                $email = $studentEmail;
                if (!empty($allowedDomains)) {
                    $domain = explode('@', $email)[1] ?? '';
                    if (!in_array($domain, $allowedDomains)) {
                        $email = 'studentsca00@gmail.com';
                    }
                }

                $mail = new GreenBackMail($studentMessage, 'Debt Assurity Reminder', 'noreply@campusafrica.co.za', 'Campus Africa', $cc);
                Mail::mailer('smtp')->to($email)->send($mail);
            }
        } catch (\Exception $e) {
            Log::error('SendAssurityMail failed', [
                'error' => $e->getMessage(),
                'userUid' => $this->userUid,
                'year' => $this->year,
            ]);
        }
    }
}

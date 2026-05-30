<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Models\Lookups;
use App\Models\User;
use App\Models\StudentsInfo;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\GreenBackMail;

class SendStudentBannedMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $user;
    public $year;
    public $reason;

    public function __construct(User $user, $year, $reason)
    {
        $this->user = $user;
        $this->year = (string) $year;
        $this->reason = (string) $reason;
    }

    public function handle()
    {
        try {
            $userMain = $this->user;
            $year = $this->year;
            $reason = trim($this->reason);

            $studentForYear = StudentsInfo::where('userUid', $userMain->id)->where('year', $year)->first();

            $emailTo = $studentForYear && !empty($studentForYear->userEmail) ? $studentForYear->userEmail : ($userMain->userEmail ?? null);
            $name = $studentForYear && !empty($studentForYear->userFirstName) ? $studentForYear->userFirstName : ($userMain->userFirstName ?? 'there');

            if (empty($emailTo)) {
                Log::warning('SendStudentBannedMail: missing email', [
                    'user_id' => $userMain->id,
                    'year' => $year,
                ]);
                return;
            }

            $subject = 'Application Restricted';

            $message =
                "<p>Hi " . e($name) . ",</p>" .
                "<p>Your Campus Africa application has been restricted and cannot be processed at this time.</p>";

            if (!empty($reason)) {
                $message .= "<p><strong>Reason:</strong> " . nl2br(e($reason)) . "</p>";
            }

            $message .=
                "<p>If you believe this is an error, please contact an administrator and provide your ID number for assistance.</p>" .
                "<p>Regards,<br/>Campus Africa</p>";

            $bcc = "";
            $allowedMailsCC = Lookups::where('group_id', '11')->pluck('lookup_name')->first();
            if (!empty($allowedMailsCC)) {
                $bcc = explode(";", $allowedMailsCC);
            }

            // Restrict outbound mail to specific test domains when configured.
            $allowedMails = Lookups::where('group_id', '10')->pluck('lookup_name')->first();
            if (!empty($allowedMails)) {
                $mailArr = explode(";", $allowedMails);
                if (!in_array(explode("@", $emailTo)[1] ?? "", $mailArr)) {
                    $emailTo = "studentsca00@gmail.com";
                }
            }

            $email = new GreenBackMail($message, $subject, 'noreply@campusafrica.co.za', 'Campus Africa', $bcc);
            Mail::mailer('smtp')->to($emailTo)->send($email);
        } catch (\Exception $e) {
            Log::error('SendStudentBannedMail: error', [
                'error' => $e->getMessage(),
                'user_id' => $this->user->id ?? null,
                'year' => $this->year ?? null,
            ]);
        }
    }
}


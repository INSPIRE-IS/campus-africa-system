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

class SendStudentUnbannedMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $user;
    public $year;
    public $reason;

    public function __construct(User $user, $year, $reason = '')
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
                Log::warning('SendStudentUnbannedMail: missing email', [
                    'user_id' => $userMain->id,
                    'year' => $year,
                ]);
                return;
            }

            $subject = 'Access Restored';

            $message =
                "<p>Hi " . e($name) . ",</p>" .
                "<p>Your Campus Africa access restriction has been removed. You can now log in and continue with your application.</p>";

            if (!empty($reason)) {
                $message .= "<p><strong>Note:</strong> " . nl2br(e($reason)) . "</p>";
            }

            $message .=
                "<p>If you have any questions, please contact an administrator and provide your ID number for assistance.</p>" .
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
            Log::error('SendStudentUnbannedMail: error', [
                'error' => $e->getMessage(),
                'user_id' => $this->user->id ?? null,
                'year' => $this->year ?? null,
            ]);
        }
    }
}


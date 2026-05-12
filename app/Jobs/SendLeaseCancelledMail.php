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

class SendLeaseCancelledMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $user;
    public $year;
    public $bookingCode;

    public function __construct(User $user, $year, $bookingCode)
    {
        $this->user = $user;
        $this->year = (string) $year;
        $this->bookingCode = (string) $bookingCode;
    }

    public function handle()
    {
        try {
            $userMain = $this->user;
            $year = $this->year;
            $bookingCode = $this->bookingCode;

            $studentForYear = StudentsInfo::where('userUid', $userMain->id)->where('year', $year)->first();

            $emailTo = $studentForYear && !empty($studentForYear->userEmail) ? $studentForYear->userEmail : ($userMain->userEmail ?? null);
            $name = $studentForYear && !empty($studentForYear->userFirstName) ? $studentForYear->userFirstName : ($userMain->userFirstName ?? 'there');

            if (empty($emailTo)) {
                Log::warning('SendLeaseCancelledMail: missing email', [
                    'user_id' => $userMain->id,
                    'year' => $year,
                ]);
                return;
            }

            $subject = 'Lease Cancelled - Ref ' . $bookingCode;

            $message =
                "<p>Hi " . e($name) . ",</p>" .
                "<p>Your lease for booking <strong>#" . e($bookingCode) . "</strong> has been cancelled because your bed allocation has been unallocated by Campus Africa.</p>" .
                "<p>Your bed will be reallocated and you will receive updated details and a new lease link once a new bed is assigned.</p>" .
                "<p>If you have any questions, please contact us via the Campus Africa portal or WhatsApp.</p>" .
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
            Log::error('SendLeaseCancelledMail: error', [
                'error' => $e->getMessage(),
                'user_id' => $this->user->id ?? null,
                'year' => $this->year ?? null,
            ]);
        }
    }
}


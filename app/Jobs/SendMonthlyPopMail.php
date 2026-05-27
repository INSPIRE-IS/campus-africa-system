<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Models\ActionList;
use App\Mail\YellowBackMail;

class SendMonthlyPopMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $email;
    public $subject;
    public $message;
    public $cc;
    public $userUid;
    public $year;
    public $monthKey;
    public $recipientType;
    public $bookingCode;

    /**
     * Create a new job instance.
     */
    public function __construct($email, $subject, $message, $cc = [], $userUid = null, $year = null, $monthKey = null, $recipientType = null, $bookingCode = null)
    {
        $this->email = $email;
        $this->subject = $subject;
        $this->message = $message;
        $this->cc = $cc ?: [];
        $this->userUid = $userUid;
        $this->year = $year;
        $this->monthKey = $monthKey;
        $this->recipientType = $recipientType;
        $this->bookingCode = $bookingCode;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        try {
            $email = new YellowBackMail($this->message, $this->subject, 'noreply@campusafrica.co.za', 'Campus Africa', $this->cc);
            Mail::mailer('smtp')->to($this->email)->send($email);
        } catch (\Exception $e) {
            Log::error('SendMonthlyPopMail failed', [
                'email' => $this->email,
                'user_uid' => $this->userUid,
                'year' => $this->year,
                'month_key' => $this->monthKey,
                'recipient_type' => $this->recipientType,
                'booking_code' => $this->bookingCode,
                'error' => $e->getMessage(),
            ]);

            // Best-effort audit entry so we can trace failures back to the student/year without log digging.
            if (!empty($this->userUid) && !empty($this->year) && !empty($this->monthKey)) {
                try {
                    ActionList::create([
                        'userUid' => $this->userUid,
                        'action' => "Monthly POP email failed {$this->monthKey} ({$this->recipientType}) booking {$this->bookingCode} to {$this->email} ({$e->getMessage()})",
                        'actionUserUid' => 0,
                        'actionUserDisplayName' => 'System',
                        'actionUserRole' => 'system',
                        'year' => $this->year,
                    ]);
                } catch (\Exception $e2) {
                    Log::error('SendMonthlyPopMail failed to write ActionList audit', [
                        'email' => $this->email,
                        'user_uid' => $this->userUid,
                        'year' => $this->year,
                        'month_key' => $this->monthKey,
                        'error' => $e2->getMessage(),
                    ]);
                }
            }
        }
    }
}

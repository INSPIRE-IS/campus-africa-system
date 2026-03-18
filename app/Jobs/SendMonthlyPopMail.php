<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\YellowBackMail;

class SendMonthlyPopMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $email;
    public $subject;
    public $message;
    public $cc;

    /**
     * Create a new job instance.
     */
    public function __construct($email, $subject, $message, $cc = [])
    {
        $this->email = $email;
        $this->subject = $subject;
        $this->message = $message;
        $this->cc = $cc ?: [];
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
                'error' => $e->getMessage(),
            ]);
        }
    }
}

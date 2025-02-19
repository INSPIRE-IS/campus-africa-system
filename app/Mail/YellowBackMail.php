<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class YellowBackMail extends Mailable
{
    use Queueable, SerializesModels;

    public $messageForMail;
    protected $subjectLine;
    protected $fromMail;
    protected $fromName;
    protected $mailCc;

    /**
     * Create a new message instance.
     */
    public function __construct($messageForMail, $subject, $fromMail, $fromName, $mailCc = null)
    {
        $this->messageForMail = $messageForMail;
        $this->subjectLine = $subject;
        $this->fromMail = $fromMail;
        $this->fromName = $fromName;
        $this->mailCc = $mailCc;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $email = $this->from($this->fromMail, $this->fromName)
            ->subject($this->subjectLine)
            ->view('email.yellow_back')
            ->with(['messageForMail' => $this->messageForMail]);

        // Apply CC if it's not empty
        if (!empty($this->mailCc)) {
            $email->bcc($this->mailCc);
        }

        return $email;
    }
}

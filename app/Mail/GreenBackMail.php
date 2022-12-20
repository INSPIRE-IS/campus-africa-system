<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class GreenBackMail extends Mailable
{
    use Queueable, SerializesModels;

    public $messageForMail;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($messageForMail, $subject, $fromMail, $fromName, $mailCc)
    {
        //
        $this->messageForMail = $messageForMail;
        $this->subject = $subject;
        $this->fromMail = $fromMail;
        $this->fromName = $fromName;
        $this->mailCc = $mailCc;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        
        return $this->from($this->fromMail, $this->fromName)
            ->subject($this->subject)
            ->bcc($this->mailCc)
            ->view('email.green_back', [ 'messageForMail' => $this->messageForMail]);
    }
}
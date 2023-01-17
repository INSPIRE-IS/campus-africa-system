<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RedBackMail extends Mailable
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
        if (empty($this->mailCc) || $this->mailCc == "") {
            $email = $this->from($this->fromMail, $this->fromName)
            ->subject($this->subject)
            ->view('email.red_back', [ 'messageForMail' => $this->messageForMail]);
        } else {
            $email = $this->from($this->fromMail, $this->fromName)
            ->subject($this->subject)
            ->bcc($this->mailCc)
            ->view('email.red_back', [ 'messageForMail' => $this->messageForMail]);
        }

        return $email;
    }
}
<?php

use Illuminate\Mail\Mailable;

class CustomerNotification extends Mailable
{
    public $messageText;

    public function __construct($messageText)
    {
        $this->messageText = $messageText;
    }

    public function build()
    {
        return $this->subject('Notification from ServiceMeite')
            ->view('emails.customer');
    }
}
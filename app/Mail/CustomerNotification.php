<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Bus\Queueable;

class CustomerNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $messageText;

    public function __construct(string $messageText)
    {
        $this->messageText = $messageText;
    }

    public function build()
    {
        return $this->subject('Notification from ServiceMeite')
            ->view('emails.customer')
            ->with([
                'messageText' => $this->messageText
            ]);
    }
}
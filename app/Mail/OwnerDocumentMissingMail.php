<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OwnerDocumentMissingMail extends Mailable
{
    use SerializesModels;

    public $user;
    public $messageText;

    public function __construct($user, $messageText)
    {
        $this->user = $user;
        $this->messageText = $messageText;
    }

    public function build()
    {
        return $this->subject('Upload Verification Document')
            ->view('emails.owner-document-missing');
    }
}
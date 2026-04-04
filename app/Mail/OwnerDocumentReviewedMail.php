<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OwnerDocumentReviewedMail extends Mailable
{
    use SerializesModels;

    public $user;
    public $ownerDocument;
    public $messageText;

    public function __construct($user, $ownerDocument, $messageText)
    {
        $this->user = $user;
        $this->ownerDocument = $ownerDocument;
        $this->messageText = $messageText;
    }

    public function build()
    {
        return $this->subject('Owner Document Review Status')
            ->view('emails.owner-document-reviewed');
    }
}
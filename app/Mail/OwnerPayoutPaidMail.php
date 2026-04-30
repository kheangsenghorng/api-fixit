<?php

namespace App\Mail;

use App\Models\Owner;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OwnerPayoutPaidMail extends Mailable
{
    use Queueable, SerializesModels;

    public $owner;
    public $payouts;
    public $totalAmount;
    public $transactionReference;

    public function __construct(Owner $owner, $payouts, $totalAmount, $transactionReference)
    {
        $this->owner = $owner;
        $this->payouts = $payouts;
        $this->totalAmount = $totalAmount;
        $this->transactionReference = $transactionReference;
    }

    public function build()
    {
        return $this->subject('Owner Payout Paid')
            ->view('emails.owner-payout-paid');
    }
}
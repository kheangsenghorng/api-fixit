<?php

namespace App\Mail;

use App\Models\Owner;
use App\Models\OwnerPayout;
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

    public function __construct(Owner $owner, array $payoutIds, $totalAmount, $transactionReference)
    {
        $this->owner = $owner;
        $this->totalAmount = $totalAmount;
        $this->transactionReference = $transactionReference;

        $this->payouts = OwnerPayout::query()
            ->select('id', 'amount', 'method', 'status', 'transaction_reference', 'paid_at')
            ->whereIn('id', $payoutIds)
            ->get();
    }

    public function build()
    {
        return $this->subject('Owner Payout Paid')
            ->view('emails.owner-payout-paid');
    }
}
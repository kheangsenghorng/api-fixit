<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;

class OtpService
{
    public function sendEmailOtp($email, $otp)
    {
        Mail::send('emails.customer', ['otp' => $otp], function ($message) use ($email) {
            $message->to($email)
                ->subject('Your OTP Code');
        });
    }
}
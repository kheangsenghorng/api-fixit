<?php

namespace App\Services;

use App\Http\Controllers\Auth\OtpController;
use App\Http\Controllers\Auth\OtpEmailController;
use Illuminate\Http\Request;

class OtpRouterService
{
    public function send(string $type, string $value)
    {
        if ($type === 'email') {
            return app(OtpEmailController::class)->sendOtp(
                new Request(['email' => $value])
            );
        }

        if ($type === 'phone') {
            return app(OtpController::class)->send(
                new Request(['phone' => $value])
            );
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid OTP type'
        ], 400);
    }
}
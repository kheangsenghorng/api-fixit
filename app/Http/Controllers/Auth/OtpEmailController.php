<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Carbon;


class OtpEmailController extends Controller
{
    /**
     * 📧 Send OTP to Email
     */
    public function sendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);
    
        $user = User::where('email', $request->email)->first();
    
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }
    
        $cacheKey = 'otp_' . $user->email;
    
        if (Cache::has($cacheKey . '_lock')) {
            return response()->json([
                'success' => false,
                'message' => 'Please wait before requesting another OTP'
            ], 429);
        }
    
        // OTP length
        $length = 5;
    
        // Generate OTP
        $otp = str_pad(rand(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
    
        Cache::put($cacheKey, $otp, now()->addMinutes(5));
        Cache::put($cacheKey . '_lock', true, now()->addSeconds(60));
    
        app(OtpService::class)->sendEmailOtp($user->email, $otp);
    
        return response()->json([
            'success' => true,
            'message' => 'OTP sent successfully'
        ]);
    }

    /**
     * ✅ Verify OTP
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp'   => 'required|digits:5'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        $cacheKey = 'otp_' . $user->email;
        $cachedOtp = Cache::get($cacheKey);

        if (!$cachedOtp || $cachedOtp != $request->otp) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired OTP'
            ], 401);
        }

        // 🧹 Clear OTP
        Cache::forget($cacheKey);

        // ✅ Activate user
        $user->update([
            'is_active' => true,
            'email_verified_at' => Carbon::now()
        ]);

        // 🔑 Generate token (JWT / Sanctum)
        $token = auth('api')->login($user);

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'access_token' => $token,
            'user' => $user
        ]);
    }
    public function resendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);
    
        $user = User::where('email', $request->email)->first();
    
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }
    
        $cacheKey = 'otp_' . $user->email;
    
        if (Cache::has($cacheKey . '_lock')) {
            $ttl = Cache::getRedis()->ttl($cacheKey . '_lock');
    
            return response()->json([
                'success' => false,
                'message' => 'Please wait before resending OTP',
                'retry_after' => $ttl > 0 ? $ttl : 0
            ], 429);
        }
    
        $length = 5;
        $otp = str_pad(random_int(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
    
        Cache::put($cacheKey, $otp, now()->addMinutes(5));
        Cache::put($cacheKey . '_lock', true, now()->addSeconds(60));
    
        app(OtpService::class)->sendEmailOtp($user->email, $otp);
    
        return response()->json([
            'success' => true,
            'message' => 'OTP resent successfully',
            'resend_in' => 60
        ]);
    }
}
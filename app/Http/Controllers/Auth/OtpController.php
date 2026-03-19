<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Log;

class OtpController extends Controller
{
    protected string $baseUrl = 'https://gatewayapi.telegram.org';

    /**
     * Send verification code via Telegram
     */
    public function send(Request $request)
    {
        $request->validate([
            'phone' => ['required', 'string']
        ]);
    
        $phone = $this->formatPhoneNumber($request->phone);
    
        // Rate limiting: max 5 attempts per 5 minutes
        if (RateLimiter::tooManyAttempts("otp_send:$phone", 5)) {
            return response()->json([
                'success' => false,
                'message' => 'Too many attempts. Try again later.'
            ], 429);
        }
    
        RateLimiter::hit("otp_send:$phone", 300);
    
        $ttlSeconds = 300; // 5 minutes
    
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('services.telegram.gateway_token'),
            'Accept' => 'application/json',
        ])->post("{$this->baseUrl}/sendVerificationMessage", [
            'phone_number' => $phone,
            'code_length'  => 5,
            'ttl'          => $ttlSeconds,
        ]);
    
        $result = $response->json();
    
        if (!$response->ok() || !($result['ok'] ?? false)) {
            Log::error("Telegram OTP Send Failed", [
                'phone' => $phone,
                'response' => $result,
            ]);
    
            return response()->json([
                'success' => false,
                'error'   => $result['error'] ?? 'API_ERROR',
                'message' => 'Unable to send code. Verify the number is on Telegram.'
            ], 400);
        }
    
        $requestId = $result['result']['request_id'];
    
        // Store request_id + expiry (same as Telegram TTL)
        Cache::put("otp_request:$phone", [
            'request_id' => $requestId,
            'expires_at' => now()->addSeconds($ttlSeconds)->timestamp,
        ], $ttlSeconds);
    
        return response()->json([
            'success'    => true,
            'message'    => 'Verification code sent.',
            'request_id' => $requestId,
            'expires_in' => $ttlSeconds, // 👈 Flutter countdown
        ]);
    }
    

    /**
     * Verify the code provided by the user
     */
    public function verify(Request $request)
    {
        $request->validate([
            'phone' => ['required', 'string'],
            'code'  => ['required', 'string', 'min:4', 'max:8'],
        ]);
    
        $phone = $this->formatPhoneNumber($request->phone);
    
        $otpData = Cache::get("otp_request:$phone");
    
        // OTP missing or expired in cache
        if (!$otpData) {
            return response()->json([
                'success' => false,
                'message' => 'OTP expired. Request a new code.'
            ], 400);
        }
    
        // Explicit expiration check
        if (now()->timestamp > $otpData['expires_at']) {
            Cache::forget("otp_request:$phone");
    
            return response()->json([
                'success' => false,
                'message' => 'OTP expired. Request a new code.'
            ], 400);
        }
    
        $requestId = $otpData['request_id'];
    
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('services.telegram.gateway_token'),
        ])->post("{$this->baseUrl}/checkVerificationStatus", [
            'request_id' => $requestId,
            'code'       => $request->code,
        ]);
    
        $result = $response->json();
    
        if ($response->ok() && ($result['ok'] ?? false)) {
            $status = $result['result']['verification_status']['status'] ?? '';
    
            if ($status === 'code_valid') {
                Cache::forget("otp_request:$phone");
            
                $user = User::where('phone', $phone)->first();
            
                if (!$user) {
                    return response()->json([
                        'success' => false,
                        'message' => 'User not found.'
                    ], 404);
                }
            
                // Optional: allow login even if already active
                if (!$user->is_active) {
                    $user->update([
                        'is_active' => true,
                    ]);
                }
            
                // 🔑 GENERATE TOKEN HERE
                $token = auth('api')->login($user);
            
                return response()->json([
                    'success' => true,
                    'message' => 'OTP verified successfully.',
                    'access_token' => $token,
                    'token_type' => 'bearer',
                    'expires_in' => auth('api')->factory()->getTTL() * 60,
                    'user' => $user,
                ]);
            }
    
            return response()->json([
                'success' => false,
                'message' => "Verification failed: $status"
            ], 400);
        }
    
        return response()->json([
            'success' => false,
            'message' => 'API error during verification.'
        ], 400);
    }
    
    /**
     * Webhook for Telegram delivery reports
     */
    public function handleCallback(Request $request)
    {
        $token     = config('services.telegram.gateway_token');
        $timestamp = $request->header('X-Request-Timestamp');
        $signature = $request->header('X-Request-Signature');
        $body      = $request->getContent();

        $dataCheckString = $timestamp . "\n" . $body;
        $secretKey       = hash('sha256', $token, true);
        $expectedSign    = hash_hmac('sha256', $dataCheckString, $secretKey);

        if (hash_equals($expectedSign, (string) $signature)) {
            $data = json_decode($body, true);
            Log::info("Telegram Delivery Update", $data);
            return response('OK', 200);
        }

        return response('Unauthorized', 401);
    }

    /**
     * Internal helper to ensure E.164 format
     */
    private function formatPhoneNumber(string $phone): string
    {
        // Remove everything except digits
        $phone = preg_replace('/\D+/', '', $phone);
    
        // Already in international format (855...)
        if (str_starts_with($phone, '855')) {
            return '+' . $phone;
        }
    
        // Local Cambodian number starting with 0
        if (str_starts_with($phone, '0')) {
            return '+855' . substr($phone, 1);
        }
    
        // Local without leading zero (e.g. 10201011)
        return '+855' . $phone;
    }
    
    
}
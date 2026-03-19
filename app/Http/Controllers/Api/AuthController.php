<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Auth\OtpController;
use App\Http\Controllers\Auth\OtpEmailController;
use App\Services\OtpRouterService;

/**
 * @OA\Tag(
 *     name="Auth",
 *     description="Authentication endpoints"
 * )
 */
class AuthController extends Controller
{
    /**
     * Get current user
     */
    public function me()
    {
        return response()->json([
            'success' => true,
            'message' => 'User profile',
            'user' => new UserResource(auth('api')->user()),
        ]);
    }

    /**
     * Register new user
     */
    public function register(Request $request, OtpRouterService $otpRouter)
    {
        if ($request->phone) {
            $request->merge([
                'phone' => formatPhoneNumber($request->phone),
            ]);
        }
    
        $validated = $request->validate([
            'name' => ['required', 'string'],
            'email' => ['nullable', 'email', 'unique:users', 'required_without:phone'],
            'phone' => ['nullable', 'unique:users', 'required_without:email'],
            'password' => ['required', 'min:6', 'confirmed'],
            'role' => ['in:customer,provider,owner'],
        ]);
    
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'role' => $validated['role'] ?? 'customer',
            'is_active' => false,
            'password' => Hash::make($validated['password']),
        ]);
    
        $type = $user->email ? 'email' : 'phone';
        $value = $user->email ?? $user->phone;
    
        return $otpRouter->send($type, $value);
    }

    /**
     * Login user
     */

 
     public function login(Request $request, OtpRouterService $otpRouter)
     {
         $validated = $request->validate([
             'login' => ['required', 'string'],
             'password' => ['required', 'string'],
         ]);
     
         $login = $validated['login'];
         $isEmail = filter_var($login, FILTER_VALIDATE_EMAIL);
     
         $field = $isEmail ? 'email' : 'phone';
         $value = $isEmail ? $login : formatPhoneNumber($login);
     
         $token = auth('api')->attempt([
             $field => $value,
             'password' => $validated['password'],
         ]);
     
         if (!$token) {
             return response()->json([
                 'success' => false,
                 'message' => 'Invalid credentials',
             ], 401);
         }
     
         $user = auth('api')->user();
     
         auth('api')->logout();
     
         $otpRouter->send(
             $isEmail ? 'email' : 'phone',
             $isEmail ? $user->email : $user->phone
         );
     
         return response()->json([
             'success' => true,
             'message' => 'OTP sent successfully',
             'channel' => $isEmail ? 'email' : 'phone',
             'login' => $isEmail ? $user->email : $user->phone,
         ]);
     }
    /**
     * Refresh JWT token
     */
    public function refresh()
    {
        try {
            $token = auth('api')->refresh(true, true);
            $user = auth('api')->user();

            return $this->respondWithToken($token, $user);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Session expired',
            ], 401);
        }
    }

    /**
     * Logout user
     */
    public function logout()
    {
        try {
            $user = auth()->user(); // ✅ get logged user
    
            if ($user) {
                $user->update([
                    'is_active' => false
                ]);
            }
    
            JWTAuth::invalidate(JWTAuth::getToken());
    
            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully',
            ]);
    
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to logout',
            ], 500);
        }
    }
    /**
     * Standard token response
     */
    protected function respondWithToken($token, $user)
    {
        return response()->json([
            'success' => true,
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
            'user' => new UserResource($user),
        ]);
    }
}

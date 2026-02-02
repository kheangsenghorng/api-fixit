<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function me()
    {
        return response()->json([
            'success' => true,
            'message' => 'User profile',
            'user' => auth('api')->user(),
        ]);
    }
    
    /**
     * Register new user
     */public function register(Request $request)
    {
        if ($request->phone) {
            $request->merge([
                'phone' => formatPhoneNumber($request->phone),
            ]);
        }
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:users|required_without:phone',
            'phone' => 'nullable|unique:users|required_without:email',
            'password' => 'required|min:6|confirmed',
            'role' => 'in:customer,provider'
        ]);


        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone, // 👈 now always +855...
            'role' => $request->role ?? 'customer',
            'is_active' => false,
            'password' => Hash::make($request->password),
        ]);
        // Generate JWT
        $token = JWTAuth::fromUser($user);

        return response()->json([
            'success' => true,
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
            'user' => $user,
        ]);
    } 
        /**
     * Login user
     */
    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);
    
        $isEmail = filter_var($request->login, FILTER_VALIDATE_EMAIL);
    
        if ($isEmail) {
            $field = 'email';
            $value = $request->login;
        } else {
            $field = 'phone';
            $value = formatPhoneNumber($request->login); // 👈 normalize to +855
        }
    
        $credentials = [
            $field => $value,
            'password' => $request->password,
        ];
    
        if (! $token = auth('api')->attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials',
            ], 401);
        }
    
        $user = auth('api')->user();
    
        // Block inactive users
        if (! $user->is_active) {
            auth('api')->logout(true);
    
            return response()->json([
                'success' => false,
                'message' => 'Account disabled',
            ], 403);
        }
    
        return response()->json([
            'success' => true,
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
            'user' => $user,
        ]);
    }
    
    

    public function refresh()
    {
        try {
            $token = auth('api')->refresh(true, true);
    
            return response()->json([
                'success' => true,
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth('api')->factory()->getTTL() * 60,
                'user' => auth('api')->user(),
            ]);
    
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
    
            return response()->json([
                'success' => false,
                'message' => 'Refresh token expired'
            ], 401);
    
        } catch (\Throwable $e) {
    
            return response()->json([
                'success' => false,
                'message' => 'Unable to refresh token'
            ], 401);
        }
    }
    
    /**
     * Logout
     */
    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
    
            return response()->json([
                'message' => 'Logged out successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to logout'
            ], 500);
        }
    }
}

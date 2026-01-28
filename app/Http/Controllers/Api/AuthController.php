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
            'user' => auth::user(),
        ]);
    }
    
    /**
     * Register new user
     */public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            // Require at least one: email OR phone
            'email' => 'nullable|email|unique:users|required_without:phone',
            'phone' => 'nullable|unique:users|required_without:email',
            'password' => 'required|min:6|confirmed',
            'role' => 'in:customer,provider'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'role' => $request->role ?? 'customer',
            'is_active' => true,
            'password' => Hash::make($request->password),
        ]);

        // Generate JWT
        $token = JWTAuth::fromUser($user);

        return response()->json([
            'success' => true,
            'message' => 'Registered successfully',
            'token' => $token,
            'user' => $user,
        ], 201);
    } 
        /**
     * Login user
     */
    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required',
            'password' => 'required'
        ]);

        $loginType = filter_var($request->login, FILTER_VALIDATE_EMAIL)
        ? 'email'
        : 'phone';
    
        $credentials = [
            $loginType => $request->login,
            'password' => $request->password
        ];
    
        // Attempt JWT login
        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }
    
        $user = auth::user();
    
        if (!$user->is_active) {
            return response()->json(['message' => 'Account disabled'], 403);
        }
    
        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'user' => $user
        ]);
    }

        public function refresh()
    {
        try {
            $newToken = auth::refresh();

            return response()->json([
                'token' => $newToken,
                'user' => auth::user()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Token cannot be refreshed'
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

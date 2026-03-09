<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

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
    public function register(Request $request)
    {
        // Normalize phone if provided
        if ($request->phone) {
            $request->merge([
                'phone' => formatPhoneNumber($request->phone),
            ]);
        }

        // Validate
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'unique:users', 'required_without:phone'],
            'phone' => ['nullable', 'unique:users', 'required_without:email'],
            'password' => ['required', 'min:6', 'confirmed'],
            'role' => ['in:customer,provider,owner'],
        ]);

        // Create user
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'role' => $validated['role'] ?? 'customer',
            'is_active' => false,
            'password' => Hash::make($validated['password']),
        ]);

        $token = JWTAuth::fromUser($user);

        return $this->respondWithToken($token, $user);
    }

    /**
     * Login user
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $login = $validated['login'];
        $isEmail = filter_var($login, FILTER_VALIDATE_EMAIL);

        $field = $isEmail ? 'email' : 'phone';
        $value = $isEmail ? $login : formatPhoneNumber($login);

        $credentials = [
            $field => $value,
            'password' => $validated['password'],
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

        return $this->respondWithToken($token, $user);
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

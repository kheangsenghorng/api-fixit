<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;


class UserController extends Controller
{
    // List users with pagination and filtering
    public function index(Request $request)
    {   
        // Pagination with default 10 per page
        $perPage = $request->get('per_page', 10);
        /// filter by active status if provided
        $users = User::when($request->has('is_active'), function ($q) use ($request) {
            $q->where('is_active', filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN));
        })
        // filter by role if provided
        ->when($request->role, function ($q) use ($request) {
            $q->where('role', $request->role);
        })
        ->latest()
        ->paginate($perPage);
    
    
        return response()->json([
            'success' => true,
            'data' => UserResource::collection($users), // transform each user with UserResource
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ],
        ]);
    }

    // Update user active status

    public function updateStatus(Request $request, User $user)
    {
        $request->validate([
            'is_active' => 'required|boolean',
        ]);
    
        $user->update([
            'is_active' => $request->is_active,
        ]);
    
        return response()->json([
            'success' => true,
            'user' => new UserResource($user->fresh()),
        ]);
    }
    public function updateManyStatus(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:users,id',
            'is_active' => 'required|boolean',
        ]);

        User::whereIn('id', $request->ids)
            ->update([
                'is_active' => $request->boolean('is_active'),
            ]);

        $users = User::whereIn('id', $request->ids)->get();

        return response()->json([
            'success' => true,
            'users' => UserResource::collection($users),
        ]);
    }

    
    // Toggle user active status
        public function toggleStatus(User $user)
    {
        $user->update([
            'is_active' => ! $user->is_active,
        ]);

        return response()->json([
            'success' => true,
            'user' => new UserResource($user->fresh()),
        ]);
    }

    
/// Create new user
    public function store(StoreUserRequest $request)
    {
        $data = $request->validated();
    
        if ($request->phone) {
            $request->merge([
                'phone' => formatPhoneNumber($request->phone),
            ]);
        }
    
        // Default role
        $data['role'] = $data['role'] ?? 'customer';
        $data['is_active'] = true;

    
        // Hash password
        $data['password'] = Hash::make($data['password']);
    
        $user = User::create($data);
    
        return response()->json([
            'success' => true,
            'user' => new UserResource($user),
        ], 201);
    }
    
     // Show user details
    public function show(User $user)
    {
        return response()->json([
            'success' => true,
            'user' => new UserResource($user),
        ]);
    }



    // Update user details
    public function update(UpdateUserRequest $request, User $user)
    {
        $data = $request->validated();
    
        // Format phone if provided
        if (isset($data['phone'])) {
            $data['phone'] = formatPhoneNumber($data['phone']);
        }
    
        // Default role if missing
        $data['role'] = $data['role'] ?? $user->role;

    
        // Hash password only if sent
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']); // prevent overwriting with null
        }
    
        // Update user
        $user->update($data);
    
        return response()->json([
            'success' => true,
            'user' => new UserResource($user->fresh()),
        ]);
    }
    public function updateAvatar(Request $request, User $user)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        // Delete old avatar if exists
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        // Store new avatar
        $path = $request->file('avatar')->store('avatars', 'public');

        // Update user
        $user->update([
            'avatar' => $path,
        ]);

        return response()->json([
            'success' => true,
            'user' => new UserResource($user->fresh()),
        ]);
    }
    
// Delete user
    public function destroy(User $user)
    {
        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully'
        ]);
    }

    // Bulk delete users
    public function destroyMany(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:users,id',
        ]);

        User::whereIn('id', $request->ids)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Users deleted successfully',
        ]);
    }

  
}

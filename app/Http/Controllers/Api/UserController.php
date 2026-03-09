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
    /**
     * List users with pagination and filtering
     */
        private const ALLOWED_ROLES = ['owner', 'customer', 'provider'];
    
        public function index(Request $request)
        {
            $perPage = $this->perPage($request);
    
            $base = $this->baseUsersQuery($request);
    
            $users = $this->applyRoleFilter(clone $base, $request)
                ->orderByDesc('updated_at')
                ->paginate($perPage)
                ->withQueryString();
    
            $counts = $this->roleCounts(clone $base);
    
            return $this->indexResponse($users, $counts);
        }
    
        // -----------------------
        // Helpers
        // -----------------------
    
        private function perPage(Request $request): int
        {
            return min((int) $request->input('per_page', 10), 50);
        }
    
        private function baseUsersQuery(Request $request)
        {
            return User::query()
                ->whereIn('role', self::ALLOWED_ROLES)
                ->updatedAfter($request->updated_after)
                ->when($request->filled('search'), fn($q) => $this->applySearch($q, $request->search))
                ->when($request->filled('is_active'), fn($q) => $this->applyIsActive($q, $request->is_active));
        }
    
        private function applySearch($query, string $search)
        {
            return $query->where(function ($sub) use ($search) {
                $sub->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }
    
        private function applyIsActive($query, $isActive)
        {
            return $query->where('is_active', filter_var($isActive, FILTER_VALIDATE_BOOLEAN));
        }
    
        private function applyRoleFilter($query, Request $request)
        {
            if (!$request->filled('role') || $request->role === 'all') {
                return $query;
            }
    
            if (!in_array($request->role, self::ALLOWED_ROLES, true)) {
                return $query; // ignore invalid roles (including admin)
            }
    
            return $query->where('role', $request->role);
        }
    
        private function roleCounts($query)
        {
            // MySQL version (SUM(role='x'))
            return $query->selectRaw("
                SUM(role = 'owner') as owners,
                SUM(role = 'customer') as customers,
                SUM(role = 'provider') as providers,
                COUNT(*) as total
            ")->first();
    
            // If PostgreSQL, use CASE WHEN instead (tell me your DB and I’ll swap it)
        }
    
        private function indexResponse($users, $counts)
        {
            return response()->json([
                'success' => true,
                'data' => UserResource::collection($users),
                'meta' => [
                    'current_page' => $users->currentPage(),
                    'last_page'    => $users->lastPage(),
                    'per_page'     => $users->perPage(),
                    'total'        => $users->total(),
                    'last_sync_at' => optional($users->last())->updated_at,
                ],
                'counts' => [
                    'owners'    => (int) ($counts->owners ?? 0),
                    'customers' => (int) ($counts->customers ?? 0),
                    'providers' => (int) ($counts->providers ?? 0),
                    'total'     => (int) ($counts->total ?? 0),
                ],
            ]);
        }
    
    
    
    /**
     * Update single user status
     */
    public function updateStatus(Request $request, User $user)
    {
        $validated = $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);

        $user->update($validated);

        return response()->json([
            'success' => true,
            'user' => new UserResource($user->fresh()),
        ]);
    }

    /**
     * Bulk update status
     */
    public function updateManyStatus(Request $request)
    {
        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['exists:users,id'],
            'is_active' => ['required', 'boolean'],
        ]);

        User::whereIn('id', $validated['ids'])
            ->update(['is_active' => $validated['is_active']]);

        $users = User::whereIn('id', $validated['ids'])->get();

        return response()->json([
            'success' => true,
            'users' => UserResource::collection($users),
        ]);
    }

    /**
     * Toggle status
     */
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

    /**
     * Create new user
     */
    public function store(StoreUserRequest $request)
    {
        $data = $request->validated();

        if (!empty($data['phone'])) {
            $data['phone'] = formatPhoneNumber($data['phone']);
        }

        $data['role'] = $data['role'] ?? 'customer';
        $data['is_active'] = $data['is_active'] ?? true;
        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);

        return response()->json([
            'success' => true,
            'data' => new UserResource($user),
        ], 201);
    }

    /**
     * Show user details
     */
    public function show(User $user)
    {
        return response()->json([
            'success' => true,
            'user' => new UserResource($user),
        ]);
    }

    /**
     * Update user
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $data = $request->validated();

        if (isset($data['phone'])) {
            $data['phone'] = formatPhoneNumber($data['phone']);
        }

        $data['role'] = $data['role'] ?? $user->role;

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return response()->json([
            'success' => true,
            'user' => new UserResource($user->fresh()),
        ]);
    }

    /**
     * Update avatar
     */
    public function updateAvatar(Request $request, User $user)
    {
        $validated = $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        // Delete old avatar
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        $path = $validated['avatar']->store('avatars', 'public');

        $user->update([
            'avatar' => $path,
        ]);

        return response()->json([
            'success' => true,
            'user' => new UserResource($user->fresh()),
        ]);
    }

    /**
     * Delete single user
     */
    public function destroy(User $user)
    {
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully',
        ]);
    }

    /**
     * Bulk delete users
     */
    public function destroyMany(Request $request)
    {
        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['exists:users,id'],
        ]);

        User::whereIn('id', $validated['ids'])->delete();

        return response()->json([
            'success' => true,
            'message' => 'Users deleted successfully',
        ]);
    }
}

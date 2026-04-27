<?php

namespace App\Http\Controllers\Api\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Resources\UserResource;
use App\Mail\UserCreatedMail;
use App\Models\Owner;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class OwnerUserController extends Controller
{

    public function index()
{
    $authUser = Auth::user();

    if (!$authUser) {
        return response()->json([
            'success' => false,
            'message' => 'Unauthenticated.',
        ], 401);
    }

    $owner = Owner::where('user_id', $authUser->id)->first();

    if (!$owner) {
        return response()->json([
            'success' => false,
            'message' => 'Owner profile not found.',
        ], 404);
    }

    $users = User::where('owner_id', $owner->id)
        ->latest()
        ->paginate(10);

    return response()->json([
        'success' => true,
        'message' => 'Users fetched successfully.',
        'data' => UserResource::collection($users),
        'meta' => [
            'current_page' => $users->currentPage(),
            'last_page' => $users->lastPage(),
            'per_page' => $users->perPage(),
            'total' => $users->total(),
        ],
    ]);
}
    public function store(StoreUserRequest $request)
    {
        $authUser = Auth::user();

        if (!$authUser) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $owner = Owner::where('user_id', $authUser->id)->first();

        if (!$owner) {
            return response()->json([
                'success' => false,
                'message' => 'Owner profile not found.',
            ], 404);
        }

        $data = $request->validated();

        if (!empty($data['phone'])) {
            $data['phone'] = formatPhoneNumber($data['phone']);
        }

        $plainPassword = 'FIXIT' . random_int(1000000000, 9999999999);

        $data['owner_id'] = $owner->id;
        $data['role'] = $data['role'] ?? 'provider';
        $data['is_active'] = $data['is_active'] ?? true;
        $data['password'] = Hash::make($plainPassword);

        $user = User::create($data);

        if (!empty($user->email)) {
            Mail::to($user->email)->send(new UserCreatedMail($user, $plainPassword));
        }

        return response()->json([
            'success' => true,
            'message' => !empty($user->email)
                ? 'User created and email sent successfully.'
                : 'User created successfully.',
            'data' => new UserResource($user),
        ], 201);
    }

}
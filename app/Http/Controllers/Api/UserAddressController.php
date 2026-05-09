<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserAddress;
use Illuminate\Http\Request;

class UserAddressController extends Controller
{
    /**
     * Get all addresses of logged-in user
     */
    public function index(Request $request)
    {
        $addresses = UserAddress::where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'User addresses',
            'data' => $addresses,
        ]);
    }

    /**
     * Create new address
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'label' => ['nullable', 'string', 'max:255'],
            'street_number' => ['nullable', 'string', 'max:255'],
            'house_number' => ['nullable', 'string', 'max:255'],
            'address' => ['required', 'string'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'map_url' => ['nullable', 'string'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        $data['user_id'] = $request->user()->id;

        if ($request->boolean('is_default')) {
            UserAddress::where('user_id', $request->user()->id)
                ->update(['is_default' => false]);
        }

        $address = UserAddress::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Address created successfully',
            'data' => $address,
        ], 201);
    }

    /**
     * Show one address
     */
    public function show(Request $request, $id)
    {
        $address = UserAddress::where('user_id', $request->user()->id)
            ->where('id', $id)
            ->first();

        if (!$address) {
            return response()->json([
                'success' => false,
                'message' => 'Address not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Address detail',
            'data' => $address,
        ]);
    }

    /**
     * Update address
     */
    public function update(Request $request, $id)
    {
        $address = UserAddress::where('user_id', $request->user()->id)
            ->where('id', $id)
            ->first();

        if (!$address) {
            return response()->json([
                'success' => false,
                'message' => 'Address not found',
            ], 404);
        }

        $data = $request->validate([
            'label' => ['nullable', 'string', 'max:255'],
            'street_number' => ['nullable', 'string', 'max:255'],
            'house_number' => ['nullable', 'string', 'max:255'],
            'address' => ['sometimes', 'required', 'string'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'map_url' => ['nullable', 'string'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        if ($request->boolean('is_default')) {
            UserAddress::where('user_id', $request->user()->id)
                ->where('id', '!=', $address->id)
                ->update(['is_default' => false]);
        }

        $address->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Address updated successfully',
            'data' => $address,
        ]);
    }

    /**
     * Delete address
     */
    public function destroy(Request $request, $id)
    {
        $address = UserAddress::where('user_id', $request->user()->id)
            ->where('id', $id)
            ->first();

        if (!$address) {
            return response()->json([
                'success' => false,
                'message' => 'Address not found',
            ], 404);
        }

        $address->delete();

        return response()->json([
            'success' => true,
            'message' => 'Address deleted successfully',
        ]);
    }

    /**
     * Set default address
     */
    public function setDefault(Request $request, $id)
    {
        $address = UserAddress::where('user_id', $request->user()->id)
            ->where('id', $id)
            ->first();

        if (!$address) {
            return response()->json([
                'success' => false,
                'message' => 'Address not found',
            ], 404);
        }

        UserAddress::where('user_id', $request->user()->id)
            ->update(['is_default' => false]);

        $address->update(['is_default' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Default address updated successfully',
            'data' => $address,
        ]);
    }
}
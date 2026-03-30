<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProviderRequest;
use App\Http\Requests\UpdateProviderRequest;
use App\Http\Resources\ProviderResource;
use App\Models\Provider;

class ProviderController extends Controller
{
    public function index()
    {
        $providers = Provider::with(['user', 'owner', 'category'])
            ->latest()
            ->paginate(10);

        return ProviderResource::collection($providers);
    }

    public function store(StoreProviderRequest $request)
    {
        $provider = Provider::create([
            'user_id' => $request->user_id,
            'owner_id' => $request->owner_id,
            'category_id' => $request->category_id,
            'provider_type' => $request->provider_type,
            'status' => $request->status ?? 'active',
        ]);

        $provider->load(['user', 'owner', 'category']);

        return (new ProviderResource($provider))
            ->additional([
                'message' => 'Provider created successfully',
            ]);
    }

    public function show($id)
    {
        $provider = Provider::with(['user', 'owner', 'category'])
            ->findOrFail($id);

        return new ProviderResource($provider);
    }

    public function update(UpdateProviderRequest $request, $id)
    {
        $provider = Provider::findOrFail($id);

        $provider->update($request->validated());
        $provider->load(['user', 'owner', 'category']);

        return (new ProviderResource($provider))
            ->additional([
                'message' => 'Provider updated successfully',
            ]);
    }

    public function destroy($id)
    {
        $provider = Provider::findOrFail($id);
        $provider->delete();

        return response()->json([
            'message' => 'Provider deleted successfully',
        ]);
    }
}
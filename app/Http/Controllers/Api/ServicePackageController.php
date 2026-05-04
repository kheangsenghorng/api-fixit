<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ServicePackageStoreRequest;
use App\Http\Requests\ServicePackageUpdateRequest;
use App\Http\Resources\ServicePackageResource;
use App\Models\ServicePackage;
use Illuminate\Http\Request;

class ServicePackageController extends Controller
{
    public function index(Request $request)
    {
        $query = ServicePackage::with('service');

        if ($request->filled('service_id')) {
            $query->where('service_id', $request->service_id);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('billing_type')) {
            $query->where('billing_type', $request->billing_type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        $packages = $query->latest()->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Service packages list',
            'data' => ServicePackageResource::collection($packages),
            'meta' => [
                'current_page' => $packages->currentPage(),
                'last_page' => $packages->lastPage(),
                'total' => $packages->total(),
            ],
        ]);
    }

    public function store(ServicePackageStoreRequest $request)
    {
        $data = $request->validated();

        $data['billing_type'] = $data['billing_type'] ?? 'one_time';
        $data['status'] = $data['status'] ?? 'draft';

        $package = ServicePackage::create($data);
        $package->load('service');

        return response()->json([
            'success' => true,
            'message' => 'Service package created successfully',
            'data' => new ServicePackageResource($package),
        ], 201);
    }

    public function show(ServicePackage $servicePackage)
    {
        $servicePackage->load('service');

        return response()->json([
            'success' => true,
            'message' => 'Service package details',
            'data' => new ServicePackageResource($servicePackage),
        ]);
    }

    public function update(ServicePackageUpdateRequest $request, ServicePackage $servicePackage)
    {
        $data = $request->validated();

        $servicePackage->update($data);
        $servicePackage->refresh()->load('service');

        return response()->json([
            'success' => true,
            'message' => 'Service package updated successfully',
            'data' => new ServicePackageResource($servicePackage),
        ]);
    }

    public function destroy(ServicePackage $servicePackage)
    {
        $servicePackage->delete();

        return response()->json([
            'success' => true,
            'message' => 'Service package deleted successfully',
        ]);
    }

    public function destroyMany(Request $request)
    {
        $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['exists:service_packages,id'],
        ]);

        ServicePackage::whereIn('id', $request->ids)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Service packages deleted successfully',
        ]);
    }

    public function updateManyStatus(Request $request)
    {
        $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['exists:service_packages,id'],
            'status' => ['required', 'in:draft,active,paused'],
        ]);

        ServicePackage::whereIn('id', $request->ids)->update([
            'status' => $request->status,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Service packages status updated successfully',
        ]);
    }
}
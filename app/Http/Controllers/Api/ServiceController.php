<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ServiceResource;
use App\Models\Service;
use App\Models\Owner;
use Illuminate\Http\Request;

use App\Http\Requests\ServiceStoreRequest;
use App\Http\Requests\ServiceUpdateRequest;
use App\Services\ImageUploadService;

class ServiceController extends Controller
{

    public function index(Request $request)
    {
        $query = Service::with(['owner','category','type']);
    
        // Search by title
        if ($request->filled('search')) {

            $search = trim($request->search);
        
            $query->where(function ($q) use ($search) {
        
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
        
            });
        }
    
        // Filter by category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
    
        // Filter by type
        if ($request->filled('type_id')) {
            $query->where('type_id', $request->type_id);
        }
    
        // Filter by owner
        if ($request->filled('owner_id')) {
            $query->where('owner_id', $request->owner_id);
        }
    
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
    
        // Price range filter
        if ($request->filled('price_min')) {
            $query->where('base_price', '>=', $request->price_min);
        }
    
        if ($request->filled('price_max')) {
            $query->where('base_price', '<=', $request->price_max);
        }
    
        $services = $query->latest()->paginate(10);
    
        return response()->json([
            'success' => true,
            'message' => 'Services list',
            'data' => ServiceResource::collection($services),
            'meta' => [
                'current_page' => $services->currentPage(),
                'last_page' => $services->lastPage(),
                'total' => $services->total(),
            ]
        ]);
    }


    public function myServices()
    {
        $owner = Owner::where('user_id', auth()->id())->firstOrFail();

        $services = Service::where('owner_id', $owner->id)
            ->latest()
            ->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Owner services',
            'data' => ServiceResource::collection($services)
        ]);
    }

    public function activeServices()
    {
        $services = Service::where('status', 'active')
            ->latest()
            ->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Active services',
            'data' => ServiceResource::collection($services)
        ]);
    }


    public function store(ServiceStoreRequest $request)
    {
        $data = $request->validated();

        // Admin can select owner
        if (auth()->user()->role === 'admin') {

            if (!$request->owner_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'owner_id is required for admin'
                ], 422);
            }

            $data['owner_id'] = $request->owner_id;

        } else {

            // Owner can only create their own service
            $owner = Owner::where('user_id', auth()->id())->firstOrFail();

            $data['owner_id'] = $owner->id;
        }

        // Upload images
        if ($request->hasFile('images')) {

            $data['images'] = ImageUploadService::uploadMultiple(
                $request->file('images'),
                'services'
            );
        }

        $service = Service::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Service created successfully',
            'data' => new ServiceResource($service)
        ], 201);
    }


    public function show(Service $service)
    {
        $service->load(['owner','category','type']);

        return response()->json([
            'success' => true,
            'message' => 'Service details',
            'data' => new ServiceResource($service)
        ]);
    }


    public function update(ServiceUpdateRequest $request, Service $service)
    {
        $data = $request->validated();

        // Replace images
        if ($request->hasFile('images')) {

            ImageUploadService::delete($service->images);

            $data['images'] = ImageUploadService::uploadMultiple(
                $request->file('images'),
                'services'
            );
        }

        $service->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Service updated successfully',
            'data' => new ServiceResource($service)
        ]);
    }


    public function destroy(Service $service)
    {
        // delete images
        ImageUploadService::delete($service->images);

        $service->delete();

        return response()->json([
            'success' => true,
            'message' => 'Service deleted successfully'
        ]);
    }


    public function destroyMany(Request $request)
    {
        $ids = $request->ids;

        $services = Service::whereIn('id', $ids)->get();

        foreach ($services as $service) {

            ImageUploadService::delete($service->images);

            $service->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Services deleted successfully'
        ]);
    }

    public function updateManyStatus(Request $request)
    {
        $ids = $request->ids;
        $status = $request->status;
    
        Service::whereIn('id', $ids)->update([
            'status' => $status
        ]);
    
        return response()->json([
            'success' => true,
            'message' => 'Services status updated'
        ]);
    }

}
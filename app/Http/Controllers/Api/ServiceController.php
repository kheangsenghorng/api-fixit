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
        /*
        |--------------------------------------------------------------------------
        | Keyword Search
        |--------------------------------------------------------------------------
        */
    
        if ($request->filled('search')) {
    
            $search = trim($request->search);
    
            $query->where(function ($q) use ($search) {
    
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
    
                  ->orWhereHas('category', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  })
    
                  ->orWhereHas('type', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
    
            });
        }
        /*
        |--------------------------------------------------------------------------
        | Category Filter
        |--------------------------------------------------------------------------
        */
    
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
    
        /*
        |--------------------------------------------------------------------------
        | Service Type Filter
        |--------------------------------------------------------------------------
        */
    
        if ($request->filled('type_id')) {
            $query->where('type_id', $request->type_id);
        }
    
        /*
        |--------------------------------------------------------------------------
        | Owner Filter
        |--------------------------------------------------------------------------
        */
    
        if ($request->filled('owner_id')) {
            $query->where('owner_id', $request->owner_id);
        }
    
        /*
        |--------------------------------------------------------------------------
        | Status Filter
        |--------------------------------------------------------------------------
        */
    
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
    
        /*
        |--------------------------------------------------------------------------
        | 
        
        |--------------------------------------------------------------------------
        */
    
        if ($request->filled('price_min')) {
            $query->where('base_price', '>=', $request->price_min);
        }
    
        if ($request->filled('price_max')) {
            $query->where('base_price', '<=', $request->price_max);
        }
    
        /*
        |--------------------------------------------------------------------------
        | Rating Filter
        |--------------------------------------------------------------------------
        */
    
        if ($request->filled('rating')) {
            $query->whereHas('reviews', function ($q) use ($request) {
                $q->where('rating', '>=', $request->rating);
            });
        }
    
        /*
        |--------------------------------------------------------------------------
        | Location Radius Search (lat/lng)
        |--------------------------------------------------------------------------
        */
    
        if ($request->filled('lat') && $request->filled('lng') && $request->filled('radius')) {
    
            $lat = $request->lat;
            $lng = $request->lng;
            $radius = $request->radius;
    
            $query->selectRaw("
                services.*,
                (
                    6371 * acos(
                        cos(radians(?))
                        * cos(radians(lat))
                        * cos(radians(lng) - radians(?))
                        + sin(radians(?))
                        * sin(radians(lat))
                    )
                ) AS distance
            ", [$lat, $lng, $lat])
            ->having("distance", "<=", $radius)
            ->orderBy("distance");
        }
    
        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */
    
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


    public function myServices(Request $request)
    {
        $owner = Owner::where('user_id', auth()->id())->firstOrFail();
    
        $query = Service::with(['category','type','owner'])
            ->where('owner_id', $owner->id);
    
        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */
    
        if ($request->filled('search')) {
    
            $search = trim($request->search);
    
            $query->where(function ($q) use ($search) {
    
                $q->where('title','like',"%{$search}%")
                  ->orWhere('description','like',"%{$search}%")
    
                  ->orWhereHas('category', function ($q) use ($search) {
                      $q->where('name','like',"%{$search}%");
                  })
    
                  ->orWhereHas('type', function ($q) use ($search) {
                      $q->where('name','like',"%{$search}%");
                  });
    
            });
        }
    
        /*
        |--------------------------------------------------------------------------
        | Filters
        |--------------------------------------------------------------------------
        */
    
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
    
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
    
        if ($request->filled('type_id')) {
            $query->where('type_id', $request->type_id);
        }
    
        if ($request->filled('price_min')) {
            $query->where('base_price', '>=', $request->price_min);
        }
    
        if ($request->filled('price_max')) {
            $query->where('base_price', '<=', $request->price_max);
        }
    
        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */
    
        $services = $query->latest()->paginate(10);
    
        return response()->json([
            'success' => true,
            'message' => 'Owner services',
            'data' => ServiceResource::collection($services),
            'meta' => [
                'current_page' => $services->currentPage(),
                'last_page' => $services->lastPage(),
                'total' => $services->total(),
            ]
        ]);
    }

    public function serviceStats()
    {
        $owner = Owner::where('user_id', auth()->id())->firstOrFail();
    
        $services = Service::where('owner_id', $owner->id);
    
        return response()->json([
            'success' => true,
            'message' => 'Owner service statistics',
            'data' => [
                'total_services' => (clone $services)->count(),
                'active_services' => (clone $services)->where('status', 'active')->count(),
                'paused_services' => (clone $services)->where('status', 'paused')->count(),
                'draft_services' => (clone $services)->where('status', 'draft')->count(),
            ]
        ]);
    }

    public function stats()
    {
        $totalServices = Service::count();

        $activeServices = Service::where('status', 'active')->count();

        $pausedServices = Service::where('status', 'paused')->count();

        $draftServices = Service::where('status', 'draft')->count();

        $totalCategories = Service::distinct('category_id')->count('category_id');

        return response()->json([
            'success' => true,
            'message' => 'Service statistics',
            'data' => [
                'total_services' => $totalServices,
                'active_services' => $activeServices,
                'paused_services' => $pausedServices,
                'draft_services' => $draftServices,
                'total_categories' => $totalCategories,
            ]
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
    
        $data['status'] = 'active';
    
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
    
            $owner = Owner::where('user_id', auth()->id())->firstOrFail();
            $data['owner_id'] = $owner->id;
    
        }
    
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
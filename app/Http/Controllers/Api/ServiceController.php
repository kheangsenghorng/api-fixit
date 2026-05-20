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
use Illuminate\Support\Facades\Auth;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Service::with(['owner', 'category', 'type', 'packages'])
            ->withCount('packages'); // ✅ count service packages
    
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
    
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
    
        if ($request->filled('type_id')) {
            $query->where('type_id', $request->type_id);
        }
    
        if ($request->filled('owner_id')) {
            $query->where('owner_id', $request->owner_id);
        }
    
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
    
        if ($request->filled('rating')) {
            $query->whereHas('reviews', function ($q) use ($request) {
                $q->where('rating', '>=', $request->rating);
            });
        }
    
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

    public function searchActiveServices(Request $request)
    {
        $query = Service::with(['category', 'type', 'owner'])
            ->where('status', 'active')
            ->whereHas('category', function ($q) {
                $q->where('status', 'active');
            })
            ->whereHas('type', function ($q) {
                $q->where('status', 'active');
            });

        if ($request->filled('search')) {
            $keywords = explode(' ', $request->search);

            $query->where(function ($q) use ($keywords) {
                foreach ($keywords as $keyword) {
                    $keyword = trim($keyword);

                    if ($keyword === '') {
                        continue;
                    }

                    $q->where(function ($subQ) use ($keyword) {
                        $subQ->where('title', 'like', '%' . $keyword . '%')
                            ->orWhere('description', 'like', '%' . $keyword . '%')
                            ->orWhereHas('category', function ($categoryQuery) use ($keyword) {
                                $categoryQuery->where('name', 'like', '%' . $keyword . '%');
                            })
                            ->orWhereHas('type', function ($typeQuery) use ($keyword) {
                                $typeQuery->where('name', 'like', '%' . $keyword . '%');
                            });
                    });
                }
            });
        }

        $services = $query->latest()->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Search active services',
            'data' => ServiceResource::collection($services),
        ]);
    }

    public function myServices(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
        }

        $owner = Owner::where('user_id', Auth::id())->firstOrFail();

        $query = Service::with(['category', 'type', 'owner','packages'])
            ->withCount('packages')// ✅ count service packages
            ->where('owner_id', $owner->id);

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

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('type_id')) {
            $query->where('type_id', $request->type_id);
        }

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

    public function fineByowner($ownerId)
    {
        $services = Service::with(['category', 'type', 'owner','packages'])
            ->where('owner_id', $ownerId)
            ->latest()
            ->paginate(10);
    
        return response()->json([
            'success' => true,
            'message' => 'Owner services',
            'data' => ServiceResource::collection($services->items()),
            'meta' => [
                'current_page' => $services->currentPage(),
                'last_page' => $services->lastPage(),
                'total' => $services->total(),
            ],
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

    public function activeServices(Request $request)
    {
        $query = Service::with(['category', 'type', 'owner', 'packages'])
            ->where('status', 'active');
    
        if ($request->filled('type_id')) {
            $query->where('type_id', $request->type_id);
        }
    
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
    
        $services = $query->latest()->paginate(10);
    
        return response()->json([
            'success' => true,
            'message' => 'Active services',
            'data' => ServiceResource::collection($services),
        ]);
    }

    public function store(ServiceStoreRequest $request)
    {
        $data = $request->validated();
        $data['status'] = $data['status'] ?? 'active';

        if (Auth::user()->role === 'admin') {
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
            $images = $request->file('images');

            if (count($images) > 10) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can upload up to 10 images only.',
                ], 422);
            }

            $data['images'] = ImageUploadService::uploadMultiple($images, 'services');
        }

        $service = Service::create($data);
        $service->load(['owner', 'category', 'type']);

        return response()->json([
            'success' => true,
            'message' => 'Service created successfully',
            'data' => new ServiceResource($service)
        ], 201);
    }

    public function show($id)
    {
        $service = Service::with([
            'owner',
            'category',
            'type',
    
            'packages' => function ($query) {
                $query->orderBy('id');
            },
    
            'packages.taskGroups' => function ($query) {
                $query->orderByPivot('sort_order');
            },
    
            'packages.taskGroups.taskItems',
    
            'packages.includedItems' => function ($query) {
                $query->orderByPivot('sort_order');
            },
    
            'taskGroups.taskItems',
            'includedItems',
        ])->find($id);
    
        if (!$service) {
            return response()->json([
                'success' => false,
                'message' => 'Service not found'
            ], 404);
        }
    
        return response()->json([
            'success' => true,
            'message' => 'Service details',
            'data' => new ServiceResource($service)
        ]);
    }

    public function update(ServiceUpdateRequest $request, Service $service)
    {
        $data = $request->validated();

        if ($request->hasFile('images')) {
            $oldImages = $service->images ?? [];
            $newFiles = $request->file('images');

            if ((count($oldImages) + count($newFiles)) > 10) {
                return response()->json([
                    'success' => false,
                    'message' => 'This service can have up to 10 images only.',
                    'current_images' => count($oldImages),
                    'new_images' => count($newFiles),
                    'max_images' => 10,
                ], 422);
            }

            $newImages = ImageUploadService::uploadMultiple($newFiles, 'services');

            $data['images'] = array_values(array_merge($oldImages, $newImages));
        }

        $service->update($data);
        $service->refresh()->load(['owner', 'category', 'type']);

        return response()->json([
            'success' => true,
            'message' => 'Service updated successfully',
            'data' => new ServiceResource($service)
        ]);
    }

    public function destroy(Service $service)
    {
        $service->delete();

        return response()->json([
            'success' => true,
            'message' => 'Service deleted successfully'
        ]);
    }

    public function destroyMany(Request $request)
    {
        $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['exists:services,id'],
        ]);

        $services = Service::whereIn('id', $request->ids)->get();

        foreach ($services as $service) {
            $service->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Services deleted successfully'
        ]);
    }

    public function updateManyStatus(Request $request)
    {
        $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['exists:services,id'],
            'status' => ['required', 'in:active,paused,draft'],
        ]);

        $services = Service::whereIn('id', $request->ids)->get();

        foreach ($services as $service) {
            $service->update([
                'status' => $request->status
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Services status updated'
        ]);
    }

    public function deleteImage(Request $request, Service $service)
    {
        $request->validate([
            'image' => ['required', 'string']
        ]);

        $image = $request->image;
        $images = $service->images ?? [];

        if (!in_array($image, $images)) {
            return response()->json([
                'success' => false,
                'message' => 'Image not found in service'
            ], 404);
        }

        ImageUploadService::delete($image);

        $images = array_values(array_filter($images, function ($img) use ($image) {
            return $img !== $image;
        }));

        $service->update([
            'images' => $images
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Image deleted successfully',
            'data' => $images
        ]);
    }
}
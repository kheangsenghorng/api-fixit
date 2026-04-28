<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreServiceBookingProviderRequest;
use App\Http\Requests\UpdateServiceBookingProviderRequest;
use App\Http\Resources\ServiceBookingProviderResource;
use App\Models\ServiceBooking;
use App\Models\ServiceBookingProvider;
use Illuminate\Http\JsonResponse;

class ServiceBookingProviderController extends Controller
{
    public function index()
    {
        $items = ServiceBookingProvider::with([
            'serviceBooking.user',
            'serviceBooking.service',
            'provider',
            'assignedByOwner',
        ])
            ->latest()
            ->paginate(10);

        return ServiceBookingProviderResource::collection($items);
    }

    public function store(StoreServiceBookingProviderRequest $request): JsonResponse
    {
        $data = $request->validated();
    
        $exists = ServiceBookingProvider::where('service_booking_id', $data['service_booking_id'])
            ->where('provider_id', $data['provider_id'])
            ->exists();
    
        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'This provider is already assigned to this booking',
            ], 409);
        }
    
        if (empty($data['assigned_at'])) {
            $data['assigned_at'] = now();
        }
    
        $item = ServiceBookingProvider::create($data);
    
        $item->load([
            'serviceBooking.user',
            'serviceBooking.service',
            'provider',
            'assignedByOwner',
        ]);
    
        return response()->json([
            'success' => true,
            'message' => 'Provider assigned to booking successfully',
            'data' => new ServiceBookingProviderResource($item),
        ], 201);
    }

    public function show(ServiceBookingProvider $serviceBookingProvider): ServiceBookingProviderResource
    {
        $serviceBookingProvider->load([
            'serviceBooking.user',
            'serviceBooking.service',
            'provider',
            'assignedByOwner',
        ]);

        return new ServiceBookingProviderResource($serviceBookingProvider);
    }

    public function update(
        UpdateServiceBookingProviderRequest $request,
        ServiceBookingProvider $serviceBookingProvider
    ): ServiceBookingProviderResource {
        $data = $request->validated();
    
        $serviceBookingProvider->update($data);
    
        $serviceBookingProvider->load([
            'serviceBooking.user',
            'serviceBooking.service',
            'provider',
            'assignedByOwner',
        ]);
    
        return new ServiceBookingProviderResource($serviceBookingProvider);
    }

    public function destroy(ServiceBookingProvider $serviceBookingProvider): JsonResponse
    {
        $serviceBookingProvider->delete();

        return response()->json([
            'success' => true,
            'message' => 'Assigned provider removed successfully',
        ]);
    }

    public function showByBookingId(int $bookingId): JsonResponse
    {
        $items = ServiceBookingProvider::with([
            'provider',
            'assignedByOwner',
            'provider.user',
        ])
            ->where('service_booking_id', $bookingId)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Booking providers retrieved successfully',
            'data' => ServiceBookingProviderResource::collection($items),
        ]);
    }

    public function showByProviderId(int $providerId): JsonResponse
    {
        $items = ServiceBookingProvider::with([
            'serviceBooking.user',
            'serviceBooking.service',
            'provider',
            'assignedByOwner',
        ])
            ->where('provider_id', $providerId)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Provider bookings retrieved successfully',
            'data' => ServiceBookingProviderResource::collection($items),
        ]);
    }
}
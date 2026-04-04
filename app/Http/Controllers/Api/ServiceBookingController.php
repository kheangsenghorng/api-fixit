<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreServiceBookingRequest;
use App\Http\Requests\UpdateServiceBookingRequest;
use App\Http\Resources\ServiceBookingResource;
use App\Models\ServiceBooking;
use Illuminate\Http\JsonResponse;

class ServiceBookingController extends Controller
{
    public function index()
    {
        $bookings = ServiceBooking::with([
            'user',
            'service.category',
            'service.type',
        ])
            ->latest()
            ->paginate(10);

        return ServiceBookingResource::collection($bookings);
    }

    public function store(StoreServiceBookingRequest $request): JsonResponse
    {
        $booking = ServiceBooking::create([
            'user_id' => $request->user_id,
            'service_id' => $request->service_id,
            'street_number' => $request->street_number,
            'house_number' => $request->house_number,
            'booking_date' => $request->booking_date,
            'booking_hours' => $request->booking_hours,
            'address' => $request->address,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'map_url' => $request->map_url,
            'quantity' => $request->quantity ?? 1,
            'notes' => $request->notes,
            'booking_status' => $request->booking_status ?? 'pending',
            'customer_status' => $request->customer_status ?? 'pending',
            'customer_completed_at' => $request->customer_completed_at,
            'auto_complete_at' => $request->auto_complete_at,
        ]);

        $booking->load(['user', 'service.category', 'service.type']);

        return response()->json([
            'success' => true,
            'message' => 'Service booking created successfully',
            'data' => new ServiceBookingResource($booking)
        ], 201);
    }

    public function show(ServiceBooking $serviceBooking): ServiceBookingResource
    {
        $serviceBooking->load([
            'user',
            'service.category',
            'service.type',
        ]);

        return new ServiceBookingResource($serviceBooking);
    }

    public function showByUserId(int $userId): JsonResponse
    {
        $bookings = ServiceBooking::with([
            'user',
            'service.category',
            'service.type',
        ])
            ->where('user_id', $userId)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'User service bookings retrieved successfully',
            'data' => ServiceBookingResource::collection($bookings)
        ]);
    }

    public function update(UpdateServiceBookingRequest $request, ServiceBooking $serviceBooking): ServiceBookingResource
    {
        $serviceBooking->update($request->validated());

        $serviceBooking->load([
            'user',
            'service.category',
            'service.type',
        ]);

        return new ServiceBookingResource($serviceBooking);
    }

    public function destroy(ServiceBooking $serviceBooking): JsonResponse
    {
        $serviceBooking->delete();

        return response()->json([
            'message' => 'Service booking deleted successfully.'
        ], 200);
    }
}
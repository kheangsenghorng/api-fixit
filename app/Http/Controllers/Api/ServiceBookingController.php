<?php

namespace App\Http\Controllers\Api;

use App\Events\OwnerNotificationEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreServiceBookingRequest;
use App\Http\Requests\UpdateServiceBookingRequest;
use App\Http\Resources\ServiceBookingResource;
use App\Models\Payment;
use App\Models\Service;
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
            'payment'
        ])
            ->latest()
            ->paginate(10);

        return ServiceBookingResource::collection($bookings);
    }

    public function store(StoreServiceBookingRequest $request): JsonResponse
    {
        // $service = Service::findOrFail($request->service_id);

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

        // broadcast(new OwnerNotificationEvent(
        //     ownerId: $service->owner_id,
        //     type: 'service-booking.created',
        //     data: [
        //         'booking' => $booking,
        //         'message' => 'New service booking created',
        //     ]
        // ))->toOthers();

        return response()->json([
            'success' => true,
            'message' => 'Service booking created successfully',
            'data' => new ServiceBookingResource($booking)
        ], 201);
    }

    public function show(ServiceBooking $serviceBooking): JsonResponse
    {
        $serviceBooking->load([
            'user',
            'service.category',
            'service.type',
            'payment',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Service booking retrieved successfully',
            'data' => new ServiceBookingResource($serviceBooking),
        ]);
    }

    public function showByUserId(int $userId): JsonResponse
    {
        $bookings = ServiceBooking::with([
            'user',
            'service.category',
            'service.type',
            'payment',
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


   
// find by owner id
public function showByOwnerId(int $ownerId): JsonResponse
{
    $bookings = ServiceBooking::with([
        'user',
        'service.category',
        'service.type',
        'payment',
    ])
        ->whereHas('service', function ($query) use ($ownerId) {
            $query->where('owner_id', $ownerId);
        })
        ->whereNotIn('booking_status', ['completed', 'cancelled'])
        ->where('customer_status', 'pending')
        ->latest()
        ->paginate(10);

    return response()->json([
        'success' => true,
        'message' => 'Owner pending service bookings retrieved successfully',
        'data' => ServiceBookingResource::collection($bookings),
        'pagination' => [
            'current_page' => $bookings->currentPage(),
            'last_page' => $bookings->lastPage(),
            'per_page' => $bookings->perPage(),
            'total' => $bookings->total(),
            'from' => $bookings->firstItem(),
            'to' => $bookings->lastItem(),
        ],
    ]);
}
public function showHistoryByOwnerId(int $ownerId): JsonResponse
{
    $bookings = ServiceBooking::with([
        'user',
        'service.category',
        'service.type',
        'payment',
    ])
        ->whereHas('service', function ($query) use ($ownerId) {
            $query->where('owner_id', $ownerId);
        })
        ->where(function ($query) {
            $query
                ->where('booking_status', 'completed')
                ->orWhere('customer_status', 'completed');
        })
        ->latest()
        ->get();

    return response()->json([
        'success' => true,
        'message' => 'Owner completed service booking history retrieved successfully',
        'data' => ServiceBookingResource::collection($bookings),
    ]);
}

// booking stats by owner id    
public function bookingStatsByOwnerId(int $ownerId): JsonResponse
{
    $baseQuery = ServiceBooking::whereHas('service', function ($query) use ($ownerId) {
        $query->where('owner_id', $ownerId);
    });

    $monthlyRevenue = Payment::whereHas('serviceBooking.service', function ($query) use ($ownerId) {
            $query->where('owner_id', $ownerId);
        })
        ->where('status', 'paid')
        ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, SUM(final_amount) as revenue')
        ->groupByRaw('YEAR(created_at), MONTH(created_at)')
        ->orderByRaw('YEAR(created_at), MONTH(created_at)')
        ->get()
        ->map(function ($item) {
            return [
                'year' => (int) $item->year,
                'month' => (int) $item->month,
                'revenue' => (float) $item->revenue,
            ];
        });

    return response()->json([
        'success' => true,
        'message' => 'Owner booking stats retrieved successfully',
        'data' => [
            'total_bookings' => (clone $baseQuery)->count(),

            'pending_customer_bookings' => (clone $baseQuery)
                ->whereNotIn('booking_status', ['completed', 'cancelled'])
                ->where('customer_status', 'pending')
                ->count(),

            'completed_bookings' => (clone $baseQuery)
                ->where('booking_status', 'completed')
                ->count(),

            'cancelled_bookings' => (clone $baseQuery)
                ->where('booking_status', 'cancelled')
                ->count(),

            'paid_bookings' => (clone $baseQuery)
                ->whereHas('payment', function ($query) {
                    $query->where('status', 'paid');
                })
                ->count(),

            'pending_payments' => (clone $baseQuery)
                ->whereHas('payment', function ($query) {
                    $query->where('status', 'pending');
                })
                ->count(),

            'failed_payments' => (clone $baseQuery)
                ->whereHas('payment', function ($query) {
                    $query->where('status', 'failed');
                })
                ->count(),

            'unpaid_bookings' => (clone $baseQuery)
                ->where(function ($query) {
                    $query->whereDoesntHave('payment')
                        ->orWhereHas('payment', function ($paymentQuery) {
                            $paymentQuery->where('status', '!=', 'paid');
                        });
                })
                ->count(),

            'total_paid_amount' => Payment::whereHas('serviceBooking.service', function ($query) use ($ownerId) {
                    $query->where('owner_id', $ownerId);
                })
                ->where('status', 'paid')
                ->sum('final_amount'),

            'monthly_revenue' => $monthlyRevenue,
        ],
    ]);
}
}

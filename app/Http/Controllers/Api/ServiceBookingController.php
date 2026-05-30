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
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\ServicePackage;
use App\Services\TelegramNotificationService;

class ServiceBookingController extends Controller
{
    protected TelegramNotificationService $telegram;

    public function __construct(
        TelegramNotificationService $telegram
    ) {
        $this->telegram = $telegram;
    }
    public function index()
    {
        $bookings = ServiceBooking::with([
            'user',
            'address',
            'service.category',
            'service.type',
            'payments'
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
            'package_id' => $request->package_id,
            'address_id' => $request->address_id,
            'booking_date' => $request->booking_date,
            'booking_hours' => $request->booking_hours,
            'quantity' => $request->quantity ?? 1,
            'notes' => $request->notes,
            'booking_status' => $request->booking_status ?? 'pending',
            'customer_status' => $request->customer_status ?? 'pending',
            'customer_completed_at' => $request->customer_completed_at,
            'auto_complete_at' => $request->auto_complete_at,
        ]);

        $booking->load([
            'user',
            'service.owner',
            'package',
            'service.category',
            'service.type',
        ]);
        try {
            $this->telegram->sendBookingToCompany($booking);
        } catch (\Exception $e) {
            \Log::error('Telegram notification failed', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);
        }

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
            'address',
            'package.taskGroups.taskItems',
            'package.includedItems',
            'service.category',
            'service.type',
            'payments',
            'walletTransactions.wallet',
            'walletTransactions.user',
            'walletTransactions.payment',
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
            'address',
            'package',
            'service.category',
            'service.type',
            'payments',
        ])
            ->where('user_id', $userId)
            ->latest()
            ->get();
    
        return response()->json([
            'success' => true,
            'message' => 'User service bookings retrieved successfully',
            'data' => ServiceBookingResource::collection($bookings),
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
        'payments',
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
        'payments',
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
                ->whereHas('payments', function ($query) {
                    $query->where('status', 'paid');
                })
                ->count(),

            'pending_payments' => (clone $baseQuery)
                ->whereHas('payments', function ($query) {
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
                    $query->whereDoesntHave('payments')
                        ->orWhereHas('payments', function ($paymentQuery) {
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
    public function ownerCancelAndRefund(Request $request, int $bookingId): JsonResponse
    {
        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $booking = DB::transaction(function () use ($bookingId, $data) {
                $booking = ServiceBooking::with([
                        'payments.split.ownerPayout',
                        'user',
                        'address',
                        'package',
                        'service',
                    ])
                    ->lockForUpdate()
                    ->findOrFail($bookingId);

                if ($booking->booking_status === 'cancelled') {
                    abort(422, 'This booking is already cancelled.');
                }

                if ($booking->booking_status === 'completed') {
                    abort(422, 'Completed booking cannot be cancelled.');
                }

                $payment = $booking->payments()
                    ->whereIn('status', ['paid', 'pending'])
                    ->latest()
                    ->lockForUpdate()
                    ->first();

                if (!$payment) {
                    abort(422, 'No refundable payment found for this booking.');
                }

                $payment->load('split.ownerPayout');

                $split = $payment->split;
                $ownerPayout = $split?->ownerPayout;

                if ($ownerPayout && $ownerPayout->status === 'paid') {
                    abort(422, 'This booking cannot be refunded because owner payout is already paid.');
                }

                $refundAmount = (float) $payment->final_amount;

                if ($refundAmount <= 0) {
                    abort(422, 'Refund amount must be greater than zero.');
                }

                $wallet = Wallet::where('user_id', $booking->user_id)
                    ->lockForUpdate()
                    ->first();

                if (!$wallet) {
                    $wallet = Wallet::create([
                        'user_id' => $booking->user_id,
                        'balance' => 0,
                        'currency' => 'USD',
                        'status' => 'active',
                        'is_active' => true,
                    ]);
                }

                if ($wallet->status !== 'active' || !$wallet->is_active) {
                    abort(422, 'User wallet is not active.');
                }

                $balanceBefore = (float) $wallet->balance;
                $balanceAfter = $balanceBefore + $refundAmount;

                $wallet->update([
                    'balance' => $balanceAfter,
                ]);

                WalletTransaction::create([
                    'wallet_id' => $wallet->wallet_id,
                    'user_id' => $booking->user_id,
                    'payment_id' => $payment->id,
                    'service_booking_id' => $booking->id,
                    'type' => 'credit',
                    'method' => 'wallet',
                    'transaction_ref' => 'REFUND-' . $booking->id . '-' . now()->format('YmdHis'),
                    'amount' => $refundAmount,
                    'balance_before' => $balanceBefore,
                    'balance_after' => $balanceAfter,
                    'description' => !empty($data['reason'])
                        ? 'Refund: ' . $data['reason']
                        : 'Refund for cancelled service booking #' . $booking->id,
                ]);

                $payment->update([
                    'status' => 'refunded',
                ]);

                if ($ownerPayout && $ownerPayout->status === 'pending') {
                    if ($ownerPayout && $ownerPayout->status === 'pending') {
                        $ownerPayout->update([
                            'status' => 'cancelled',
                            'transaction_reference' => 'CANCELLED-BOOKING-' . $booking->id,
                            'paid_at' => null,
                        ]);
                    }
                }

                $booking->update([
                    'booking_status' => 'cancelled',
                    'customer_status' => 'refunded',
                ]);

                return $booking->fresh([
                    'user',
                    'address',
                    'package',
                    'service',
                    'payments.split.ownerPayout',
                    'walletTransactions',
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Booking cancelled and refund added to customer wallet successfully.',
                'data' => $booking,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
    
    public function refundedCancelledBookings(Request $request): JsonResponse
    {
        $ownerId = $request->query('owner_id');
    
        $bookings = ServiceBooking::with([
            'user',
            'address',
            'package',
            'service.category',
            'service.type',
            'payments.split.ownerPayout',
            'walletTransactions',
        ])
            ->where('booking_status', 'cancelled')
            ->where('customer_status', 'refunded')
            ->when($ownerId, function ($query) use ($ownerId) {
                $query->whereHas('payments', function ($paymentQuery) use ($ownerId) {
                    $paymentQuery->where('owner_id', $ownerId);
                });
            })
            ->latest()
            ->paginate(10);
    
        return response()->json([
            'success' => true,
            'message' => 'Cancelled and refunded bookings retrieved successfully.',
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

    public function refundedCancelledBookingsByOwnerId(int $ownerId): JsonResponse
{
    $bookings = ServiceBooking::with([
        'user',
        'address',
        'package',
        'service.category',
        'service.type',
        'payments.split.ownerPayout',
        'walletTransactions',
    ])
        ->whereHas('service', function ($query) use ($ownerId) {
            $query->where('owner_id', $ownerId);
        })
        ->where('booking_status', 'cancelled')
        ->where('customer_status', 'refunded')
        ->latest()
        ->paginate(10);

    return response()->json([
        'success' => true,
        'message' => 'Owner cancelled and refunded bookings retrieved successfully.',
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
}

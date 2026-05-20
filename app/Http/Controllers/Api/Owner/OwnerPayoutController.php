<?php

namespace App\Http\Controllers\Api\Owner;

use App\Http\Controllers\Controller;
use App\Models\Owner;
use App\Models\OwnerPayout;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OwnerPayoutController extends Controller
{
    /**
     * Common relationships for payout response
     */
    private array $relations = [
        'owner',
        'split.payment',
        'split.payment.serviceBooking',
        'split.payment.serviceBooking.service',
    ];

    /**
     * Get logged-in user's owner profile
     */
    private function getAuthenticatedOwner(): ?Owner
    {
        return Owner::where('user_id', auth()->id())->first();
    }

    /**
     * Owner not found response
     */
    private function ownerNotFoundResponse(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Owner profile not found for this user.',
            'data' => null,
        ], 404);
    }

    /**
     * Get month date range.
     * Default: current month.
     * Optional request:
     * ?month=2026-05
     */
    private function getMonthRange(?string $month = null): array
    {
        $date = $month
            ? Carbon::createFromFormat('Y-m', $month)
            : Carbon::now();

        return [
            'month' => $date->format('Y-m'),
            'start' => $date->copy()->startOfMonth(),
            'end' => $date->copy()->endOfMonth(),
        ];
    }

    /**
     * Base payout query by owner id
     */
    private function payoutQueryByOwnerId(int $ownerId): Builder
    {
        return OwnerPayout::with($this->relations)
            ->where('owner_id', $ownerId);
    }

    /**
     * Base payout query by owner id and month
     */
    private function monthlyPayoutQueryByOwnerId(int $ownerId, ?string $month = null): Builder
    {
        $range = $this->getMonthRange($month);

        return OwnerPayout::with($this->relations)
            ->where('owner_id', $ownerId)
            ->whereBetween('created_at', [
                $range['start'],
                $range['end'],
            ]);
    }

    /**
     * Monthly payout stats response data
     */
    private function getMonthlyStatsData(int $ownerId, ?string $month = null): array
    {
        $range = $this->getMonthRange($month);

        $baseQuery = OwnerPayout::where('owner_id', $ownerId)
            ->whereBetween('created_at', [
                $range['start'],
                $range['end'],
            ]);

        return [
            'total_payouts' => (clone $baseQuery)->count(),
            'total_amount' => round((float) (clone $baseQuery)->sum('amount'), 2),

            'pending_count' => (clone $baseQuery)->where('status', 'pending')->count(),
            'paid_count' => (clone $baseQuery)->where('status', 'paid')->count(),
            'failed_count' => (clone $baseQuery)->where('status', 'failed')->count(),

            'pending_amount' => round((float) (clone $baseQuery)->where('status', 'pending')->sum('amount'), 2),
            'paid_amount' => round((float) (clone $baseQuery)->where('status', 'paid')->sum('amount'), 2),
            'failed_amount' => round((float) (clone $baseQuery)->where('status', 'failed')->sum('amount'), 2),
        ];
    }

    /**
     * Get monthly payouts for logged-in owner
     *
     * Example:
     * GET /api/owner/payouts
     * GET /api/owner/payouts?status=pending
     * GET /api/owner/payouts?month=2026-05
     */
    public function index(Request $request): JsonResponse
    {
        $owner = $this->getAuthenticatedOwner();

        if (!$owner) {
            return $this->ownerNotFoundResponse();
        }

        $request->validate([
            'status' => ['nullable', 'in:pending,paid,failed'],
            'month' => ['nullable', 'date_format:Y-m'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $range = $this->getMonthRange($request->month);

        $query = $this->monthlyPayoutQueryByOwnerId(
            $owner->id,
            $request->month
        );

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $payouts = $query
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return response()->json([
            'success' => true,
            'message' => 'Your monthly payouts fetched successfully',
            'owner_id' => $owner->id,
            'user_id' => auth()->id(),
            'month' => $range['month'],
            'data' => $payouts,
        ]);
    }

    /**
     * Get monthly payout stats for logged-in owner
     *
     * Example:
     * GET /api/owner/payouts/stats
     * GET /api/owner/payouts/stats?month=2026-05
     */
    public function stats(Request $request): JsonResponse
    {
        $owner = $this->getAuthenticatedOwner();

        if (!$owner) {
            return $this->ownerNotFoundResponse();
        }

        $request->validate([
            'month' => ['nullable', 'date_format:Y-m'],
        ]);

        $range = $this->getMonthRange($request->month);

        return response()->json([
            'success' => true,
            'message' => 'Your monthly payout stats fetched successfully',
            'owner_id' => $owner->id,
            'user_id' => auth()->id(),
            'month' => $range['month'],
            'data' => $this->getMonthlyStatsData($owner->id, $request->month),
        ]);
    }

    /**
     * Show one payout for logged-in owner
     *
     * Example:
     * GET /api/owner/payouts/10
     */
    public function show(int $id): JsonResponse
    {
        $owner = $this->getAuthenticatedOwner();

        if (!$owner) {
            return $this->ownerNotFoundResponse();
        }

        $payout = $this->payoutQueryByOwnerId($owner->id)
            ->where('id', $id)
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'message' => 'Your payout fetched successfully',
            'owner_id' => $owner->id,
            'user_id' => auth()->id(),
            'data' => $payout,
        ]);
    }

    /**
     * Show monthly payouts by owner id
     *
     * Example:
     * GET /api/owner/payouts/owner/5
     * GET /api/owner/payouts/owner/5?status=pending
     * GET /api/owner/payouts/owner/5?month=2026-05
     */
    public function showByOwnerId(Request $request, int $ownerId): JsonResponse
    {
        $request->validate([
            'status' => ['nullable', 'in:pending,paid,failed'],
            'month' => ['nullable', 'date_format:Y-m'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $range = $this->getMonthRange($request->month);

        $query = $this->monthlyPayoutQueryByOwnerId(
            $ownerId,
            $request->month
        );

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $payouts = $query
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return response()->json([
            'success' => true,
            'message' => 'Owner monthly payouts fetched successfully',
            'owner_id' => $ownerId,
            'month' => $range['month'],
            'data' => $payouts,
        ]);
    }

    /**
     * Show monthly payout stats by owner id
     *
     * Example:
     * GET /api/owner/payouts/owner/5/stats
     * GET /api/owner/payouts/owner/5/stats?month=2026-05
     */
    public function statsByOwnerId(Request $request, int $ownerId): JsonResponse
    {
        $request->validate([
            'month' => ['nullable', 'date_format:Y-m'],
        ]);

        $range = $this->getMonthRange($request->month);

        return response()->json([
            'success' => true,
            'message' => 'Owner monthly payout stats fetched successfully',
            'owner_id' => $ownerId,
            'month' => $range['month'],
            'data' => $this->getMonthlyStatsData($ownerId, $request->month),
        ]);
    }
}
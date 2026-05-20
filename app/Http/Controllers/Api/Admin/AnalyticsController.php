<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Owner;
use App\Models\Payment;
use App\Models\Service;
use App\Models\ServiceBooking;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function overview(Request $request): JsonResponse
    {
        $days = (int) $request->get('days', 30);

        $startDate = now()->subDays($days)->startOfDay();
        $endDate = now()->endOfDay();

        $previousStartDate = now()->subDays($days * 2)->startOfDay();
        $previousEndDate = now()->subDays($days)->endOfDay();

        /**
         * ===============================
         * TOTAL REVENUE
         * ===============================
         */
        $totalRevenue = Payment::where('status', 'paid')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('final_amount');

        $previousRevenue = Payment::where('status', 'paid')
            ->whereBetween('created_at', [$previousStartDate, $previousEndDate])
            ->sum('final_amount');

        /**
         * ===============================
         * ACTIVE USERS
         * Users who made booking/payment in period
         * ===============================
         */
        $activeUsers = Payment::whereBetween('created_at', [$startDate, $endDate])
            ->distinct('user_id')
            ->count('user_id');

        $previousActiveUsers = Payment::whereBetween('created_at', [$previousStartDate, $previousEndDate])
            ->distinct('user_id')
            ->count('user_id');

        /**
         * ===============================
         * SERVICES COMPLETED
         * ===============================
         */
        $servicesCompleted = ServiceBooking::whereBetween('created_at', [$startDate, $endDate])
            ->where(function ($query) {
                $query->where('booking_status', 'completed')
                    ->orWhere('customer_status', 'completed');
            })
            ->count();

        $previousServicesCompleted = ServiceBooking::whereBetween('created_at', [$previousStartDate, $previousEndDate])
            ->where(function ($query) {
                $query->where('booking_status', 'completed')
                    ->orWhere('customer_status', 'completed');
            })
            ->count();

        /**
         * ===============================
         * PROVIDER GROWTH
         * New owners/providers in period
         * ===============================
         */
        $providerGrowth = Owner::whereBetween('created_at', [$startDate, $endDate])
            ->count();

        $previousProviderGrowth = Owner::whereBetween('created_at', [$previousStartDate, $previousEndDate])
            ->count();

        /**
         * ===============================
         * MONTHLY REVENUE CHART
         * Jan - Dec
         * ===============================
         */
        $monthlyRevenue = Payment::select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(final_amount) as total')
            )
            ->where('status', 'paid')
            ->whereYear('created_at', now()->year)
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->orderBy(DB::raw('MONTH(created_at)'))
            ->get();

        $revenueChart = collect(range(1, 12))->map(function ($month) use ($monthlyRevenue) {
            $found = $monthlyRevenue->firstWhere('month', $month);

            return [
                'month' => Carbon::create()->month($month)->format('M'),
                'revenue' => $found ? (float) $found->total : 0,
            ];
        });

        /**
         * ===============================
         * LAST MONTH REVENUE CHART
         * Optional compare
         * ===============================
         */
        $lastYearRevenue = Payment::select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(final_amount) as total')
            )
            ->where('status', 'paid')
            ->whereYear('created_at', now()->subYear()->year)
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->orderBy(DB::raw('MONTH(created_at)'))
            ->get();

        $lastYearChart = collect(range(1, 12))->map(function ($month) use ($lastYearRevenue) {
            $found = $lastYearRevenue->firstWhere('month', $month);

            return [
                'month' => Carbon::create()->month($month)->format('M'),
                'revenue' => $found ? (float) $found->total : 0,
            ];
        });

        /**
         * ===============================
         * TOP SERVICES
         * ===============================
         */
        $topServices = ServiceBooking::query()
            ->join('services', 'service_bookings.service_id', '=', 'services.id')
            ->select(
                'services.id',
                'services.title',
                DB::raw('COUNT(service_bookings.id) as orders')
            )
            ->whereBetween('service_bookings.created_at', [$startDate, $endDate])
            ->groupBy('services.id', 'services.title')
            ->orderByDesc('orders')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'title' => $item->title,
                    'orders' => (int) $item->orders,
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Analytics overview fetched successfully',
            'data' => [
                'period' => [
                    'days' => $days,
                    'start_date' => $startDate->toDateString(),
                    'end_date' => $endDate->toDateString(),
                ],

                'cards' => [
                    'total_revenue' => [
                        'label' => 'Total Revenue',
                        'value' => round((float) $totalRevenue, 2),
                        'formatted' => '$' . number_format((float) $totalRevenue, 2),
                        'change_percent' => $this->calculatePercentageChange($previousRevenue, $totalRevenue),
                    ],

                    'active_users' => [
                        'label' => 'Active Users',
                        'value' => $activeUsers,
                        'change_percent' => $this->calculatePercentageChange($previousActiveUsers, $activeUsers),
                    ],

                    'services_completed' => [
                        'label' => 'Services Completed',
                        'value' => $servicesCompleted,
                        'change_percent' => $this->calculatePercentageChange($previousServicesCompleted, $servicesCompleted),
                    ],

                    'provider_growth' => [
                        'label' => 'Provider Growth',
                        'value' => $providerGrowth,
                        'change_percent' => $this->calculatePercentageChange($previousProviderGrowth, $providerGrowth),
                    ],
                ],

                'revenue_forecast' => [
                    'this_year' => $revenueChart,
                    'last_year' => $lastYearChart,
                ],

                'top_services' => $topServices,
            ],
        ]);
    }

    private function calculatePercentageChange($oldValue, $newValue): float
    {
        $oldValue = (float) $oldValue;
        $newValue = (float) $newValue;

        if ($oldValue == 0 && $newValue == 0) {
            return 0;
        }

        if ($oldValue == 0) {
            return 100;
        }

        return round((($newValue - $oldValue) / $oldValue) * 100, 1);
    }
}
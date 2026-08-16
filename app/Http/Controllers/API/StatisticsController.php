<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class StatisticsController extends Controller
{
    public function rentals(): JsonResponse
    {
        $totalReservations = DB::table('reservations')->count();

        $completedRentals = DB::table('reservations')
            ->where('status', 'completed')
            ->count();

        $totalRevenue = DB::table('reservations')
            ->where('status', 'completed')
            ->sum('total_price');

        $reservationsByStatus = DB::table('reservations')
            ->select(
                'status',
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('status')
            ->orderByDesc('total')
            ->get();

        $mostRentedVehicles = DB::table('reservations')
            ->join(
                'vehicles',
                'reservations.vehicle_id',
                '=',
                'vehicles.id'
            )
            ->join(
                'categories',
                'vehicles.category_id',
                '=',
                'categories.id'
            )
            ->join(
                'users',
                'reservations.user_id',
                '=',
                'users.id'
            )
            ->whereIn(
                'reservations.status',
                ['approved', 'completed']
            )
            ->select(
                'vehicles.id',
                'vehicles.brand',
                'vehicles.model',
                'categories.name as category',
                DB::raw('COUNT(reservations.id) as rental_count'),
                DB::raw(
                    'COUNT(DISTINCT users.id) as unique_customers'
                )
            )
            ->groupBy(
                'vehicles.id',
                'vehicles.brand',
                'vehicles.model',
                'categories.name'
            )
            ->orderByDesc('rental_count')
            ->limit(5)
            ->get();

        return response()->json([
            'total_reservations' => $totalReservations,
            'completed_rentals' => $completedRentals,
            'total_revenue' => round((float) $totalRevenue, 2),
            'reservations_by_status' => $reservationsByStatus,
            'most_rented_vehicles' => $mostRentedVehicles,
        ]);
    }
}
<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\User;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ReservationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'status' => [
                'nullable',
                Rule::in([
                    'pending',
                    'approved',
                    'cancelled',
                    'completed',
                ]),
            ],
            'per_page' => [
                'nullable',
                'integer',
                'between:1,50',
            ],
        ]);

        $query = Reservation::query()
            ->with([
                'user:id,name,email,role',
                'vehicle.category',
            ])
            ->latest();

        // Customer može da vidi samo svoje rezervacije.
        if ($request->user()->hasRole(User::ROLE_CUSTOMER)) {
            $query->where('user_id', $request->user()->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $validated['status']);
        }

        $perPage = $validated['per_page'] ?? 10;

        return response()->json(
            $query->paginate($perPage)->withQueryString()
        );
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'vehicle_id' => [
                'required',
                'integer',
                'exists:vehicles,id',
            ],
            'start_date' => [
                'required',
                'date',
                'after_or_equal:today',
            ],
            'end_date' => [
                'required',
                'date',
                'after_or_equal:start_date',
            ],
        ]);

        $vehicle = Vehicle::findOrFail($validated['vehicle_id']);

        if ($vehicle->status !== 'available') {
            return response()->json([
                'message' => 'Vehicle is currently not available.',
            ], 409);
        }

        $hasOverlap = Reservation::query()
            ->where('vehicle_id', $vehicle->id)
            ->whereIn('status', ['pending', 'approved'])
            ->whereDate('start_date', '<=', $validated['end_date'])
            ->whereDate('end_date', '>=', $validated['start_date'])
            ->exists();

        if ($hasOverlap) {
            return response()->json([
                'message' => 'Vehicle is already reserved for the selected period.',
            ], 409);
        }

        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);

        $rentalDays = (int) $startDate->diffInDays($endDate) + 1;
        $totalPrice = $rentalDays * (float) $vehicle->daily_price;

        $reservation = Reservation::create([
            'user_id' => $request->user()->id,
            'vehicle_id' => $vehicle->id,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'total_price' => $totalPrice,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Reservation created successfully.',
            'rental_days' => $rentalDays,
            'data' => $reservation->load([
                'user:id,name,email,role',
                'vehicle.category',
            ]),
        ], 201);
    }

    public function show(
        Request $request,
        Reservation $reservation
    ): JsonResponse {
        if (
            $request->user()->hasRole(User::ROLE_CUSTOMER) &&
            $reservation->user_id !== $request->user()->id
        ) {
            return response()->json([
                'message' => 'Forbidden. This reservation does not belong to you.',
            ], 403);
        }

        return response()->json([
            'data' => $reservation->load([
                'user:id,name,email,role',
                'vehicle.category',
            ]),
        ]);
    }

    public function update(
        Request $request,
        Reservation $reservation
    ): JsonResponse {
        if (
            $request->user()->hasRole(User::ROLE_CUSTOMER) &&
            $reservation->user_id !== $request->user()->id
        ) {
            return response()->json([
                'message' => 'Forbidden. This reservation does not belong to you.',
            ], 403);
        }

        if ($reservation->status !== 'pending') {
            return response()->json([
                'message' => 'Only pending reservations can be modified.',
            ], 422);
        }

        $validated = $request->validate([
            'vehicle_id' => [
                'required',
                'integer',
                'exists:vehicles,id',
            ],
            'start_date' => [
                'required',
                'date',
                'after_or_equal:today',
            ],
            'end_date' => [
                'required',
                'date',
                'after_or_equal:start_date',
            ],
        ]);

        $result = DB::transaction(function () use (
            $validated,
            $reservation
        ) {
            $vehicle = Vehicle::query()
                ->lockForUpdate()
                ->findOrFail($validated['vehicle_id']);

            if ($vehicle->status !== 'available') {
                return [
                    'error' => true,
                    'status' => 409,
                    'message' => 'Vehicle is currently not available.',
                ];
            }

            $hasOverlap = Reservation::query()
                ->where('vehicle_id', $vehicle->id)
                ->where('id', '!=', $reservation->id)
                ->whereIn('status', ['pending', 'approved'])
                ->whereDate('start_date', '<=', $validated['end_date'])
                ->whereDate('end_date', '>=', $validated['start_date'])
                ->exists();

            if ($hasOverlap) {
                return [
                    'error' => true,
                    'status' => 409,
                    'message' => 'Vehicle is already reserved for the selected period.',
                ];
            }

            $startDate = Carbon::parse($validated['start_date']);
            $endDate = Carbon::parse($validated['end_date']);

            $rentalDays =
                (int) $startDate->diffInDays($endDate) + 1;

            $totalPrice =
                $rentalDays * (float) $vehicle->daily_price;

            $reservation->update([
                'vehicle_id' => $vehicle->id,
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'total_price' => $totalPrice,
            ]);

            return [
                'error' => false,
                'rental_days' => $rentalDays,
            ];
        });

        if ($result['error']) {
            return response()->json([
                'message' => $result['message'],
            ], $result['status']);
        }

        return response()->json([
            'message' => 'Reservation updated successfully.',
            'rental_days' => $result['rental_days'],
            'data' => $reservation->fresh()->load([
                'user:id,name,email,role',
                'vehicle.category',
            ]),
        ]);
    }

    public function updateStatus(
        Request $request,
        Reservation $reservation
    ): JsonResponse {
        $validated = $request->validate([
            'status' => [
                'required',
                Rule::in([
                    'approved',
                    'cancelled',
                    'completed',
                ]),
            ],
        ]);

        $allowedTransitions = [
            'pending' => ['approved', 'cancelled'],
            'approved' => ['completed', 'cancelled'],
            'cancelled' => [],
            'completed' => [],
        ];

        if (
            !in_array(
                $validated['status'],
                $allowedTransitions[$reservation->status],
                true
            )
        ) {
            return response()->json([
                'message' => 'The requested status change is not allowed.',
            ], 422);
        }

        $reservation->update([
            'status' => $validated['status'],
        ]);

        return response()->json([
            'message' => 'Reservation status updated successfully.',
            'data' => $reservation->fresh()->load([
                'user:id,name,email,role',
                'vehicle.category',
            ]),
        ]);
    }

    public function cancel(
        Request $request,
        Reservation $reservation
    ): JsonResponse {
        if (
            $request->user()->hasRole(User::ROLE_CUSTOMER) &&
            $reservation->user_id !== $request->user()->id
        ) {
            return response()->json([
                'message' => 'Forbidden. This reservation does not belong to you.',
            ], 403);
        }

        if (
            !in_array(
                $reservation->status,
                ['pending', 'approved'],
                true
            )
        ) {
            return response()->json([
                'message' => 'This reservation cannot be cancelled.',
            ], 422);
        }

        $reservation->update([
            'status' => 'cancelled',
        ]);

        return response()->json([
            'message' => 'Reservation cancelled successfully.',
            'data' => $reservation->fresh(),
        ]);
    }

    public function history(
        Request $request,
        User $user
    ): JsonResponse {
        if (
            $request->user()->hasRole(User::ROLE_CUSTOMER) &&
            $request->user()->id !== $user->id
        ) {
            return response()->json([
                'message' => 'Forbidden. You can only view your own reservation history.',
            ], 403);
        }

        $reservations = $user->reservations()
            ->with('vehicle.category')
            ->where(function ($query) {
                $query->whereIn('status', ['completed', 'cancelled'])
                    ->orWhereDate('end_date', '<', now()->toDateString());
            })
            ->orderByDesc('end_date')
            ->paginate(10);

        return response()->json($reservations);
    }
    public function vehicleReservations(
        Vehicle $vehicle
    ): JsonResponse {
        $reservations = Reservation::query()
            ->where('vehicle_id', $vehicle->id)
            ->with([
             'user:id,name,email,role',
             'vehicle.category',
         ])
         ->orderByDesc('start_date')
         ->paginate(10);

        return response()->json($reservations);
}
}
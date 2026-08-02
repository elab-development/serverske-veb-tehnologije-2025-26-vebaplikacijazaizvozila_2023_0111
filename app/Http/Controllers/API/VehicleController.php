<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VehicleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'brand' => ['nullable', 'string', 'max:100'],
            'model' => ['nullable', 'string', 'max:100'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'status' => [
                'nullable',
                Rule::in(['available', 'rented', 'service']),
            ],
            'transmission' => [
                'nullable',
                Rule::in(['manual', 'automatic']),
            ],
            'fuel_type' => [
                'nullable',
                Rule::in([
                    'petrol',
                    'diesel',
                    'electric',
                    'hybrid',
                ]),
            ],
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'max_price' => ['nullable', 'numeric', 'min:0'],
            'per_page' => ['nullable', 'integer', 'between:1,50'],
            'sort_by' => [
                'nullable',
                Rule::in([
                    'id',
                    'brand',
                    'model',
                    'production_year',
                    'daily_price',
                    'created_at',
                ]),
            ],
            'sort_direction' => [
                'nullable',
                Rule::in(['asc', 'desc']),
            ],
        ]);

        $query = Vehicle::query()
            ->with(['category', 'images']);

        if ($request->filled('brand')) {
            $query->where(
                'brand',
                'like',
                '%' . $validated['brand'] . '%'
            );
        }

        if ($request->filled('model')) {
            $query->where(
                'model',
                'like',
                '%' . $validated['model'] . '%'
            );
        }

        if ($request->filled('category_id')) {
            $query->where(
                'category_id',
                $validated['category_id']
            );
        }

        if ($request->filled('status')) {
            $query->where('status', $validated['status']);
        }

        if ($request->filled('transmission')) {
            $query->where(
                'transmission',
                $validated['transmission']
            );
        }

        if ($request->filled('fuel_type')) {
            $query->where(
                'fuel_type',
                $validated['fuel_type']
            );
        }

        if ($request->filled('min_price')) {
            $query->where(
                'daily_price',
                '>=',
                $validated['min_price']
            );
        }

        if ($request->filled('max_price')) {
            $query->where(
                'daily_price',
                '<=',
                $validated['max_price']
            );
        }

        $sortBy = $validated['sort_by'] ?? 'id';
        $sortDirection = $validated['sort_direction'] ?? 'desc';
        $perPage = $validated['per_page'] ?? 10;

        $vehicles = $query
            ->orderBy($sortBy, $sortDirection)
            ->paginate($perPage)
            ->withQueryString();

        return response()->json($vehicles);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category_id' => [
                'required',
                'integer',
                'exists:categories,id',
            ],
            'brand' => ['required', 'string', 'max:100'],
            'model' => ['required', 'string', 'max:100'],
            'registration_number' => [
                'required',
                'string',
                'max:50',
                'unique:vehicles,registration_number',
            ],
            'production_year' => [
                'required',
                'integer',
                'between:1950,' . (date('Y') + 1),
            ],
            'mileage' => [
                'sometimes',
                'integer',
                'min:0',
            ], 
            'color' => [
                'nullable',
                'string',
                'max:50',
            ],
            'daily_price' => [
                'required',
                'numeric',
                'min:0.01',
            ],
            'transmission' => [
                'required',
                Rule::in(['manual', 'automatic']),
            ],
            'fuel_type' => [
                'required',
                Rule::in([
                    'petrol',
                    'diesel',
                    'electric',
                    'hybrid',
                ]),
            ],
            'seats' => ['required', 'integer', 'between:1,9'],
            'status' => [
                'sometimes',
                Rule::in(['available', 'rented', 'service']),
            ],
            'description' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ]);

        $vehicle = Vehicle::create($validated);

        return response()->json([
            'message' => 'Vehicle created successfully.',
            'data' => $vehicle->load(['category', 'images']),
        ], 201);
    }

    public function show(Vehicle $vehicle): JsonResponse
    {
        return response()->json([
            'data' => $vehicle->load([
                'category',
                'images',
            ]),
        ]);
    }

    public function update(
        Request $request,
        Vehicle $vehicle
    ): JsonResponse {
        $validated = $request->validate([
            'category_id' => [
                'sometimes',
                'required',
                'integer',
                'exists:categories,id',
            ],
            'brand' => [
                'sometimes',
                'required',
                'string',
                'max:100',
            ],
            'model' => [
                'sometimes',
                'required',
                'string',
                'max:100',
            ],
            'registration_number' => [
                'sometimes',
                'required',
                'string',
                'max:50',
                Rule::unique(
                    'vehicles',
                    'registration_number'
                )->ignore($vehicle->id),
            ],
            'production_year' => [
                'sometimes',
                'required',
                'integer',
                'between:1950,' . (date('Y') + 1),
            ],
            'mileage' => [
                'sometimes',
                'required',
                'integer',
                'min:0',
            ],
            'color' => [
                'sometimes',
                'nullable',
                'string',
                'max:50',
            ],
            'daily_price' => [
                'sometimes',
                'required',
                'numeric',
                'min:0.01',
            ],
            'transmission' => [
                'sometimes',
                'required',
                Rule::in(['manual', 'automatic']),
            ],
            'fuel_type' => [
                'sometimes',
                'required',
                Rule::in([
                    'petrol',
                    'diesel',
                    'electric',
                    'hybrid',
                ]),
            ],
            'seats' => [
                'sometimes',
                'required',
                'integer',
                'between:1,9',
            ],
            'status' => [
                'sometimes',
                'required',
                Rule::in(['available', 'rented', 'service']),
            ],
            'description' => [
                'sometimes',
                'nullable',
                'string',
                'max:2000',
            ],
        ]);

        $vehicle->update($validated);

        return response()->json([
            'message' => 'Vehicle updated successfully.',
            'data' => $vehicle
                ->fresh()
                ->load(['category', 'images']),
        ]);
    }

    public function destroy(Vehicle $vehicle): JsonResponse
    {
        if ($vehicle->reservations()->exists()) {
            return response()->json([
                'message' => 'Vehicle cannot be deleted because it has reservations.',
            ], 409);
        }

        $vehicle->delete();

        return response()->json([
            'message' => 'Vehicle deleted successfully.',
        ]);
    }
}
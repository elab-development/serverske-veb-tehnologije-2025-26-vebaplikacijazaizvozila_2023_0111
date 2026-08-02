<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\VehicleImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VehicleImageController extends Controller
{
    public function store(
        Request $request,
        Vehicle $vehicle
    ): JsonResponse {
        $validated = $request->validate([
            'image' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
            'is_primary' => [
                'sometimes',
                'boolean',
            ],
        ]);

        $isPrimary = $request->boolean('is_primary');

        if ($isPrimary) {
            $vehicle->images()->update([
                'is_primary' => false,
            ]);
        }

        $path = $request->file('image')->store(
            'vehicle-images',
            'public'
        );

        $vehicleImage = VehicleImage::create([
            'vehicle_id' => $vehicle->id,
            'path' => $path,
            'is_primary' => $isPrimary,
        ]);

        return response()->json([
            'message' => 'Vehicle image uploaded successfully.',
            'data' => $vehicleImage,
            'url' => Storage::disk('public')->url($path),
        ], 201);
    }

    public function destroy(
        VehicleImage $vehicleImage
    ): JsonResponse {
        Storage::disk('public')->delete(
            $vehicleImage->path
        );

        $vehicleImage->delete();

        return response()->json([
            'message' => 'Vehicle image deleted successfully.',
        ]);
    }
}
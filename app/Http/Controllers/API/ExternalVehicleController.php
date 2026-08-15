<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;

class ExternalVehicleController extends Controller
{
    public function modelsByMake($make)
    {
        $response = Http::withoutVerifying()->get(
    "https://vpic.nhtsa.dot.gov/api/vehicles/GetModelsForMake/{$make}",
    [
        'format' => 'json'
    ]
);

        if ($response->failed()) {
            return response()->json([
                'message' => 'Unable to fetch vehicle models.'
            ], 502);
        }

        return response()->json($response->json());
    }
}
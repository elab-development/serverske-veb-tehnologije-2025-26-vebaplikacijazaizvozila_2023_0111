<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ExternalVehicleApiTest extends TestCase
{
    public function test_vehicle_models_can_be_fetched_by_make(): void
    {
        Http::fake([
            'vpic.nhtsa.dot.gov/*' => Http::response([
                'Count' => 1,
                'Results' => [
                    [
                        'Make_Name' => 'BMW',
                        'Model_Name' => 'X5',
                    ],
                ],
            ], 200),
        ]);

        $response = $this->getJson(
            '/api/external/vehicles/models/BMW'
        );

        $response
            ->assertStatus(200)
            ->assertJson([
                'Count' => 1,
                'Results' => [
                    [
                        'Make_Name' => 'BMW',
                        'Model_Name' => 'X5',
                    ],
                ],
            ]);
    }
    public function test_exchange_rate_can_be_fetched(): void
    {
     Http::fake([
        'api.frankfurter.dev/*' => Http::response([
            'amount' => 1,
            'base' => 'EUR',
            'rates' => [
                'USD' => 1.1567,
            ],
          ], 200),
        ]);

    $response = $this->getJson(
        '/api/external/exchange/EUR/USD'
    );

    $response
        ->assertStatus(200)
        ->assertJson([
            'amount' => 1,
            'base' => 'EUR',
            'rates' => [
                'USD' => 1.1567,
            ],
        ]);
}
}
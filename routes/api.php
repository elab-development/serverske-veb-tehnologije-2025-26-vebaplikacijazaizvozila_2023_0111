<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CategoryController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\VehicleController;
use App\Http\Controllers\API\ReservationController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::apiResource('categories', CategoryController::class)
        ->only(['index', 'show']);

    Route::apiResource('categories', CategoryController::class)
        ->only(['store', 'update', 'destroy'])
        ->middleware('role:admin,employee');

    Route::apiResource('vehicles', VehicleController::class)
    ->only(['index', 'show']);

    Route::apiResource('vehicles', VehicleController::class)
    ->only(['store', 'update', 'destroy'])
    ->middleware('role:admin,employee');
    Route::get(
    '/reservations',
    [ReservationController::class, 'index']
);

Route::post(
    '/reservations',
    [ReservationController::class, 'store']
);

Route::get(
    '/reservations/{reservation}',
    [ReservationController::class, 'show']
);

Route::post(
    '/reservations/{reservation}/cancel',
    [ReservationController::class, 'cancel']
);

Route::patch(
    '/reservations/{reservation}/status',
    [ReservationController::class, 'updateStatus']
)->middleware('role:admin,employee');    
});
<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookingApiController;
use App\Http\Controllers\Api\MidtransWebhookController;
use App\Http\Controllers\Api\VenueApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes for Mobile Booking App
|--------------------------------------------------------------------------
*/

// 1. Authentication (Public)
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    // Protected Auth
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});

// 2. Venues & Courts Catalog (Public)
Route::prefix('venues')->group(function () {
    Route::get('/', [VenueApiController::class, 'index']);
    Route::get('/{venue}', [VenueApiController::class, 'show']);
    Route::get('/{venue}/courts', [VenueApiController::class, 'courts']);
});

// 3. Real-time Slot Availability (Public)
Route::get('/courts/{court}/availability', [VenueApiController::class, 'availability']);

// 4. Payment Gateway Webhooks (Public)
Route::post('/webhooks/midtrans', [MidtransWebhookController::class, 'handle']);

// 5. Booking Details & E-Ticket (Public lookup by booking code)
Route::get('/bookings/{code}', [BookingApiController::class, 'show']);

// 6. Protected Customer Actions (Requires Sanctum Token)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/bookings', [BookingApiController::class, 'store']);
    Route::get('/my-bookings', [BookingApiController::class, 'myBookings']);
});

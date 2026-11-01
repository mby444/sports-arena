<?php

use App\Http\Controllers\Mobile\MobileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Mobile Web Routes (Customer Facing Interface)
|--------------------------------------------------------------------------
*/

// Home & Catalog
Route::get('/', [MobileController::class, 'home'])->name('mobile.home');
Route::get('/court/{court}', [MobileController::class, 'showCourt'])->name('mobile.court');
Route::post('/court/{court}/reserve', [MobileController::class, 'reserve'])->name('mobile.court.reserve');

// Checkout & Confirmation
Route::get('/checkout/{booking}', [MobileController::class, 'checkout'])->name('mobile.checkout');
Route::post('/checkout/{booking}/mock-pay', [MobileController::class, 'mockPay'])->name('mobile.checkout.mock');
Route::get('/booking/{booking}/success', [MobileController::class, 'success'])->name('mobile.success');

// Customer Auth
Route::get('/login', [MobileController::class, 'showLogin'])->name('mobile.login');
Route::post('/login', [MobileController::class, 'login'])->name('mobile.login.submit');
Route::get('/register', [MobileController::class, 'showRegister'])->name('mobile.register');
Route::post('/register', [MobileController::class, 'register'])->name('mobile.register.submit');
Route::post('/logout', [MobileController::class, 'logout'])->name('mobile.logout');

// Protected Customer Screens
Route::middleware('auth')->group(function () {
    Route::get('/my-bookings', [MobileController::class, 'myBookings'])->name('mobile.my-bookings');
    Route::get('/profile', [MobileController::class, 'profile'])->name('mobile.profile');
});

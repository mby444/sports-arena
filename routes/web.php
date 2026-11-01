<?php

use App\Http\Controllers\Admin\AdminBookingController;
use App\Http\Controllers\Admin\AdminCourtController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Mobile\MobileController;
use App\Http\Middleware\AdminMiddleware;
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

/*
|--------------------------------------------------------------------------
| Web Admin Dashboard Routes (Venue Management Portal)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->middleware(['auth', AdminMiddleware::class])->group(function () {
    Route::get('/', fn () => redirect()->route('admin.dashboard'));
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');

    // Courts Management
    Route::get('/courts', [AdminCourtController::class, 'index'])->name('admin.courts');
    Route::post('/courts', [AdminCourtController::class, 'store'])->name('admin.courts.store');
    Route::put('/courts/{court}', [AdminCourtController::class, 'update'])->name('admin.courts.update');
    Route::post('/courts/{court}/toggle', [AdminCourtController::class, 'toggle'])->name('admin.courts.toggle');

    // Bookings & Check-in Control
    Route::get('/bookings', [AdminBookingController::class, 'index'])->name('admin.bookings');
    Route::post('/bookings/{booking}/check-in', [AdminBookingController::class, 'checkIn'])->name('admin.bookings.checkin');
    Route::post('/bookings/{booking}/cancel', [AdminBookingController::class, 'cancel'])->name('admin.bookings.cancel');
});

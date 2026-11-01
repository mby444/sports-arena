<?php

use App\Models\Court;
use App\Models\User;
use App\Models\Venue;
use App\Services\MidtransService;
use App\Services\SlotManagerService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->venue = Venue::create([
        'name' => 'Arena Midtrans Test',
        'slug' => 'arena-midtrans-test',
        'address' => 'Jl. Bayar No. 8',
        'city' => 'Jakarta',
        'is_active' => true,
    ]);

    $this->court = Court::create([
        'venue_id' => $this->venue->id,
        'name' => 'Futsal 1',
        'sport_type' => 'futsal',
        'hourly_rate' => 150000.00,
        'open_time' => '08:00:00',
        'close_time' => '22:00:00',
        'is_active' => true,
    ]);

    $this->user = User::create([
        'name' => 'Customer Pay',
        'email' => 'pay@example.com',
        'phone' => '08777777777',
        'role' => 'customer',
        'password' => bcrypt('password'),
    ]);

    $this->slotManager = app(SlotManagerService::class);
    $this->midtransService = app(MidtransService::class);

    $date = now()->addDays(4)->format('Y-m-d');
    $this->booking = $this->slotManager->reserveSlots($this->user, $this->court, $date, ['19:00', '20:00']);
});

test('generates mock snap token and updates booking in mock mode', function () {
    $result = $this->midtransService->createSnapToken($this->booking);

    expect($result)->toHaveKeys(['snap_token', 'redirect_url'])
        ->and($result['snap_token'])->toStartWith('MOCK-SNAP-')
        ->and($this->booking->fresh()->snap_token)->toBe($result['snap_token']);
});

test('handles settlement notification and marks booking as paid', function () {
    $payload = [
        'order_id' => $this->booking->booking_code,
        'status_code' => '200',
        'gross_amount' => '300000.00',
        'transaction_status' => 'settlement',
        'payment_type' => 'qris',
    ];

    $updatedBooking = $this->midtransService->handleNotification($payload);

    expect($updatedBooking->status)->toBe('paid')
        ->and($updatedBooking->isPaid())->toBeTrue()
        ->and($updatedBooking->payment_method)->toBe('qris')
        ->and($updatedBooking->paid_at)->not->toBeNull()
        ->and($updatedBooking->lock_expires_at)->toBeNull();
});

test('handles capture notification with accepted fraud status', function () {
    $payload = [
        'order_id' => $this->booking->booking_code,
        'status_code' => '200',
        'gross_amount' => '300000.00',
        'transaction_status' => 'capture',
        'fraud_status' => 'accept',
        'payment_type' => 'credit_card',
    ];

    $updatedBooking = $this->midtransService->handleNotification($payload);

    expect($updatedBooking->status)->toBe('paid')
        ->and($updatedBooking->payment_method)->toBe('credit_card');
});

test('handles expire or cancel notification by cancelling the booking', function () {
    $payload = [
        'order_id' => $this->booking->booking_code,
        'status_code' => '202',
        'gross_amount' => '300000.00',
        'transaction_status' => 'expire',
        'payment_type' => 'gopay',
    ];

    $updatedBooking = $this->midtransService->handleNotification($payload);

    expect($updatedBooking->status)->toBe('cancelled')
        ->and($updatedBooking->isCancelled())->toBeTrue();
});

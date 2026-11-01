<?php

use App\Models\Booking;
use App\Models\Court;
use App\Models\User;
use App\Models\Venue;
use App\Services\SlotManagerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->venue = Venue::create([
        'name' => 'Arena Lock Test',
        'slug' => 'arena-lock-test',
        'address' => 'Jl. Lock No. 1',
        'city' => 'Jakarta',
        'is_active' => true,
    ]);

    $this->court = Court::create([
        'venue_id' => $this->venue->id,
        'name' => 'Court A',
        'sport_type' => 'badminton',
        'hourly_rate' => 60000.00,
        'open_time' => '08:00:00',
        'close_time' => '22:00:00',
        'is_active' => true,
    ]);

    $this->user1 = User::create([
        'name' => 'Alice',
        'email' => 'alice@example.com',
        'phone' => '08111111111',
        'role' => 'customer',
        'password' => bcrypt('password'),
    ]);

    $this->user2 = User::create([
        'name' => 'Bob',
        'email' => 'bob@example.com',
        'phone' => '08222222222',
        'role' => 'customer',
        'password' => bcrypt('password'),
    ]);

    $this->slotManager = app(SlotManagerService::class);
});

test('successfully reserves slots with a 15-minute temporary lock', function () {
    $date = now()->addDays(3)->format('Y-m-d');
    $booking = $this->slotManager->reserveSlots($this->user1, $this->court, $date, ['09:00', '10:00']);

    expect($booking)->toBeInstanceOf(Booking::class)
        ->and($booking->status)->toBe('pending')
        ->and($booking->total_price)->toBe('120000.00')
        ->and($booking->lock_expires_at)->not->toBeNull()
        ->and($booking->lock_expires_at->isFuture())->toBeTrue()
        ->and($booking->slots)->toHaveCount(2);
});

test('prevents double-booking when competing user tries to lock same slot', function () {
    $date = now()->addDays(3)->format('Y-m-d');

    // Alice locks 09:00 - 10:00
    $this->slotManager->reserveSlots($this->user1, $this->court, $date, ['09:00']);

    // Bob tries to lock 09:00 - 10:00 immediately
    expect(function () use ($date) {
        $this->slotManager->reserveSlots($this->user2, $this->court, $date, ['09:00']);
    })->toThrow(InvalidArgumentException::class, 'One or more selected slots are no longer available.');
});

test('allows booking a slot if previous pending lock has expired', function () {
    $date = now()->addDays(3)->format('Y-m-d');

    // Alice reserved earlier, but lock expired
    $booking = $this->slotManager->reserveSlots($this->user1, $this->court, $date, ['10:00']);
    $booking->update([
        'lock_expires_at' => now()->subMinutes(1),
    ]);

    // Bob tries to reserve the same slot now
    $bobBooking = $this->slotManager->reserveSlots($this->user2, $this->court, $date, ['10:00']);

    expect($bobBooking)->toBeInstanceOf(Booking::class)
        ->and($bobBooking->user_id)->toBe($this->user2->id)
        ->and($bobBooking->isLockActive())->toBeTrue();
});

test('releases expired bookings via artisan command', function () {
    $date = now()->addDays(3)->format('Y-m-d');

    $booking = $this->slotManager->reserveSlots($this->user1, $this->court, $date, ['11:00']);
    $booking->update([
        'lock_expires_at' => now()->subMinutes(2),
    ]);

    Artisan::call('bookings:release-expired');

    expect($booking->fresh()->status)->toBe('cancelled');
});

test('rejects booking past dates or slots outside operating hours', function () {
    $pastDate = now()->subDays(2)->format('Y-m-d');
    expect(function () use ($pastDate) {
        $this->slotManager->reserveSlots($this->user1, $this->court, $pastDate, ['09:00']);
    })->toThrow(InvalidArgumentException::class, 'Cannot book a slot for a past date.');

    $validDate = now()->addDays(1)->format('Y-m-d');
    expect(function () use ($validDate) {
        $this->slotManager->reserveSlots($this->user1, $this->court, $validDate, ['23:00']);
    })->toThrow(InvalidArgumentException::class, 'outside court operating hours.');
});

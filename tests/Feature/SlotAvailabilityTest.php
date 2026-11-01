<?php

use App\Models\Booking;
use App\Models\BookingSlot;
use App\Models\Court;
use App\Models\User;
use App\Models\Venue;
use App\Services\SlotManagerService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->venue = Venue::create([
        'name' => 'Arena Test',
        'slug' => 'arena-test',
        'address' => 'Jl. Test No. 1',
        'city' => 'Jakarta',
        'is_active' => true,
    ]);

    $this->court = Court::create([
        'venue_id' => $this->venue->id,
        'name' => 'Court 1',
        'sport_type' => 'badminton',
        'hourly_rate' => 60000.00,
        'open_time' => '08:00:00',
        'close_time' => '22:00:00',
        'is_active' => true,
    ]);

    $this->user = User::create([
        'name' => 'Tester',
        'email' => 'tester@example.com',
        'phone' => '08123456789',
        'role' => 'customer',
        'password' => bcrypt('password'),
    ]);

    $this->slotManager = app(SlotManagerService::class);
});

test('generates correct 1-hour intervals for court operating hours', function () {
    $intervals = $this->slotManager->generateHourlyIntervals('08:00:00', '12:00:00');

    expect($intervals)->toHaveCount(4)
        ->and($intervals[0])->toBe(['start' => '08:00', 'end' => '09:00'])
        ->and($intervals[3])->toBe(['start' => '11:00', 'end' => '12:00']);
});

test('returns all slots as available when no bookings exist', function () {
    $date = now()->addDays(2)->format('Y-m-d');
    $slots = $this->slotManager->getSlotsForCourtAndDate($this->court, $date);

    expect($slots)->toHaveCount(14); // 08:00 to 22:00 = 14 hours
    foreach ($slots as $slot) {
        expect($slot['status'])->toBe('available')
            ->and($slot['price'])->toBe(60000.0);
    }
});

test('identifies booked slots correctly for confirmed bookings', function () {
    $date = now()->addDays(2)->format('Y-m-d');

    $booking = Booking::create([
        'booking_code' => 'BK-TEST-0001',
        'user_id' => $this->user->id,
        'court_id' => $this->court->id,
        'booking_date' => $date,
        'total_price' => 60000.00,
        'status' => 'paid',
        'paid_at' => now(),
    ]);

    BookingSlot::create([
        'booking_id' => $booking->id,
        'court_id' => $this->court->id,
        'booking_date' => $date,
        'start_time' => '09:00:00',
        'end_time' => '10:00:00',
        'price' => 60000.00,
    ]);

    $slots = $this->slotManager->getSlotsForCourtAndDate($this->court, $date);
    $slot09 = collect($slots)->firstWhere('start_time', '09:00');
    $slot10 = collect($slots)->firstWhere('start_time', '10:00');

    expect($slot09['status'])->toBe('booked')
        ->and($slot10['status'])->toBe('available');
});

test('identifies locked slots for pending unexpired bookings', function () {
    $date = now()->addDays(2)->format('Y-m-d');

    $booking = Booking::create([
        'booking_code' => 'BK-TEST-0002',
        'user_id' => $this->user->id,
        'court_id' => $this->court->id,
        'booking_date' => $date,
        'total_price' => 60000.00,
        'status' => 'pending',
        'lock_expires_at' => now()->addMinutes(10),
    ]);

    BookingSlot::create([
        'booking_id' => $booking->id,
        'court_id' => $this->court->id,
        'booking_date' => $date,
        'start_time' => '14:00:00',
        'end_time' => '15:00:00',
        'price' => 60000.00,
    ]);

    $slots = $this->slotManager->getSlotsForCourtAndDate($this->court, $date);
    $slot14 = collect($slots)->firstWhere('start_time', '14:00');

    expect($slot14['status'])->toBe('locked');
});

test('treats expired pending booking slots as available', function () {
    $date = now()->addDays(2)->format('Y-m-d');

    $booking = Booking::create([
        'booking_code' => 'BK-TEST-0003',
        'user_id' => $this->user->id,
        'court_id' => $this->court->id,
        'booking_date' => $date,
        'total_price' => 60000.00,
        'status' => 'pending',
        'lock_expires_at' => now()->subMinutes(5), // expired
    ]);

    BookingSlot::create([
        'booking_id' => $booking->id,
        'court_id' => $this->court->id,
        'booking_date' => $date,
        'start_time' => '16:00:00',
        'end_time' => '17:00:00',
        'price' => 60000.00,
    ]);

    $slots = $this->slotManager->getSlotsForCourtAndDate($this->court, $date);
    $slot16 = collect($slots)->firstWhere('start_time', '16:00');

    expect($slot16['status'])->toBe('available');
});

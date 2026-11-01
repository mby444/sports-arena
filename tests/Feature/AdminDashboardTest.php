<?php

use App\Models\Court;
use App\Models\User;
use App\Models\Venue;
use App\Services\SlotManagerService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->venue = Venue::create([
        'name' => 'Admin Arena Test',
        'slug' => 'admin-arena-test',
        'address' => 'Jl. Admin No. 99',
        'city' => 'Jakarta Timur',
        'is_active' => true,
    ]);

    $this->court = Court::create([
        'venue_id' => $this->venue->id,
        'name' => 'Badminton Court 1',
        'sport_type' => 'badminton',
        'hourly_rate' => 60000.00,
        'open_time' => '08:00:00',
        'close_time' => '22:00:00',
        'is_active' => true,
    ]);

    $this->admin = User::create([
        'name' => 'Admin User',
        'email' => 'admin@arena.com',
        'phone' => '081111111111',
        'role' => 'admin',
        'password' => bcrypt('password'),
    ]);

    $this->customer = User::create([
        'name' => 'Regular Customer',
        'email' => 'regular@example.com',
        'phone' => '082222222222',
        'role' => 'customer',
        'password' => bcrypt('password'),
    ]);
});

test('blocks regular customer from accessing admin dashboard', function () {
    $response = $this->actingAs($this->customer)
        ->get(route('admin.dashboard'));

    $response->assertForbidden();
});

test('redirects unauthenticated user from admin dashboard to login', function () {
    $response = $this->get(route('admin.dashboard'));

    $response->assertRedirect(route('mobile.login'));
});

test('admin can access dashboard and view occupancy metrics', function () {
    $response = $this->actingAs($this->admin)
        ->get(route('admin.dashboard'));

    $response->assertOk()
        ->assertSee('Overview &amp; Master Court Matrix', false)
        ->assertSee('Master Court Schedule Matrix')
        ->assertSee('Badminton Court 1');
});

test('admin can add a new court', function () {
    $response = $this->actingAs($this->admin)
        ->post(route('admin.courts.store'), [
            'venue_id' => $this->venue->id,
            'name' => 'Futsal Vinyl Pro 3',
            'sport_type' => 'futsal',
            'hourly_rate' => 160000.00,
            'open_time' => '08:00',
            'close_time' => '22:00',
            'is_active' => 1,
        ]);

    $response->assertRedirect(route('admin.courts'));
    $this->assertDatabaseHas('courts', [
        'name' => 'Futsal Vinyl Pro 3',
        'sport_type' => 'futsal',
        'hourly_rate' => 160000.00,
    ]);
});

test('admin can toggle court maintenance status', function () {
    expect($this->court->is_active)->toBeTrue();

    $response = $this->actingAs($this->admin)
        ->post(route('admin.courts.toggle', $this->court));

    $response->assertRedirect(route('admin.courts'));
    expect($this->court->fresh()->is_active)->toBeFalse();
});

test('admin can view reservations and check-in a paid customer', function () {
    $date = now()->addDays(2)->format('Y-m-d');
    $booking = app(SlotManagerService::class)->reserveSlots(
        $this->customer,
        $this->court,
        $date,
        ['10:00']
    );
    $booking->update(['status' => 'paid']);

    // Admin views bookings list
    $listResponse = $this->actingAs($this->admin)
        ->get(route('admin.bookings'));
    $listResponse->assertOk()
        ->assertSee($booking->booking_code)
        ->assertSee('PAID');

    // Admin checks in the customer
    $checkInResponse = $this->actingAs($this->admin)
        ->post(route('admin.bookings.checkin', $booking));

    $checkInResponse->assertRedirect();
    expect($booking->fresh()->status)->toBe('completed');
});

test('admin can cancel an active booking', function () {
    $date = now()->addDays(2)->format('Y-m-d');
    $booking = app(SlotManagerService::class)->reserveSlots(
        $this->customer,
        $this->court,
        $date,
        ['11:00']
    );

    $cancelResponse = $this->actingAs($this->admin)
        ->post(route('admin.bookings.cancel', $booking));

    $cancelResponse->assertRedirect();
    expect($booking->fresh()->status)->toBe('cancelled');
});

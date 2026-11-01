<?php

use App\Models\Court;
use App\Models\User;
use App\Models\Venue;
use App\Services\SlotManagerService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->venue = Venue::create([
        'name' => 'Champion Arena Web',
        'slug' => 'champion-arena-web',
        'address' => 'Jl. Web No. 1',
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

    $this->customer = User::create([
        'name' => 'Customer Web',
        'email' => 'customer.web@example.com',
        'phone' => '081234567890',
        'role' => 'customer',
        'password' => bcrypt('password123'),
    ]);
});

test('loads mobile home explore screen with courts and venues', function () {
    $response = $this->get(route('mobile.home'));

    $response->assertOk()
        ->assertSee('Champion Arena Web')
        ->assertSee('Badminton Court 1')
        ->assertSee('Rp 60.000');
});

test('loads court booking screen with date carousel and slots', function () {
    $response = $this->get(route('mobile.court', $this->court));

    $response->assertOk()
        ->assertSee($this->court->name)
        ->assertSee('Choose Hourly Slots')
        ->assertSee('08:00');
});

test('redirects unauthenticated user to login when attempting to reserve', function () {
    $date = now()->addDays(2)->format('Y-m-d');

    $response = $this->post(route('mobile.court.reserve', $this->court), [
        'booking_date' => $date,
        'slots' => ['09:00'],
    ]);

    $response->assertRedirect(route('mobile.login'));
});

test('authenticated customer can reserve slots and proceed to checkout', function () {
    $date = now()->addDays(2)->format('Y-m-d');

    $response = $this->actingAs($this->customer)
        ->post(route('mobile.court.reserve', $this->court), [
            'booking_date' => $date,
            'slots' => ['09:00', '10:00'],
        ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('bookings', [
        'user_id' => $this->customer->id,
        'court_id' => $this->court->id,
        'total_price' => 120000.00,
        'status' => 'pending',
    ]);
});

test('processes mock payment and redirects to success E-ticket', function () {
    $date = now()->addDays(2)->format('Y-m-d');

    $booking = app(SlotManagerService::class)->reserveSlots(
        $this->customer,
        $this->court,
        $date,
        ['14:00']
    );

    $response = $this->actingAs($this->customer)
        ->post(route('mobile.checkout.mock', $booking), [
            'payment_method' => 'QRIS',
        ]);

    $response->assertRedirect(route('mobile.success', $booking));

    expect($booking->fresh()->status)->toBe('paid')
        ->and($booking->fresh()->payment_method)->toBe('QRIS');

    // Verify success ticket screen renders
    $ticketResponse = $this->actingAs($this->customer)
        ->get(route('mobile.success', $booking));

    $ticketResponse->assertOk()
        ->assertSee($booking->booking_code)
        ->assertSee('Booking Confirmed!')
        ->assertSee('E-Ticket Pass');
});

test('renders my bookings tabs for customer', function () {
    $response = $this->actingAs($this->customer)
        ->get(route('mobile.my-bookings'));

    $response->assertOk()
        ->assertSee('My Tickets')
        ->assertSee('Upcoming');
});

test('renders customer profile', function () {
    $response = $this->actingAs($this->customer)
        ->get(route('mobile.profile'));

    $response->assertOk()
        ->assertSee($this->customer->name)
        ->assertSee($this->customer->email);
});

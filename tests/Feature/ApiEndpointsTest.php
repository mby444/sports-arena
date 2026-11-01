<?php

use App\Models\Court;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->venue = Venue::create([
        'name' => 'Champion Arena API',
        'slug' => 'champion-arena-api',
        'address' => 'Jl. Kebon Jeruk 10',
        'city' => 'Jakarta Barat',
        'is_active' => true,
    ]);

    $this->courtBadminton = Court::create([
        'venue_id' => $this->venue->id,
        'name' => 'Badminton Court 1',
        'sport_type' => 'badminton',
        'hourly_rate' => 60000.00,
        'open_time' => '08:00:00',
        'close_time' => '22:00:00',
        'is_active' => true,
    ]);

    $this->courtFutsal = Court::create([
        'venue_id' => $this->venue->id,
        'name' => 'Futsal Pitch 1',
        'sport_type' => 'futsal',
        'hourly_rate' => 150000.00,
        'open_time' => '08:00:00',
        'close_time' => '22:00:00',
        'is_active' => true,
    ]);

    $this->user = User::create([
        'name' => 'API Customer',
        'email' => 'api.customer@example.com',
        'phone' => '081234567890',
        'role' => 'customer',
        'password' => bcrypt('secret123'),
    ]);
});

test('registers a new customer and returns sanctum token', function () {
    $response = $this->postJson('/api/auth/register', [
        'name' => 'New Player',
        'email' => 'newplayer@example.com',
        'phone' => '08999999999',
        'password' => 'password123',
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'message',
            'token',
            'user' => ['id', 'name', 'email', 'phone', 'role'],
        ]);

    $this->assertDatabaseHas('users', [
        'email' => 'newplayer@example.com',
        'role' => 'customer',
    ]);
});

test('logs in an existing user and returns a token', function () {
    $response = $this->postJson('/api/auth/login', [
        'email' => 'api.customer@example.com',
        'password' => 'secret123',
    ]);

    $response->assertOk()
        ->assertJsonStructure(['token', 'user']);
});

test('rejects login with invalid credentials', function () {
    $response = $this->postJson('/api/auth/login', [
        'email' => 'api.customer@example.com',
        'password' => 'wrongpassword',
    ]);

    $response->assertStatus(401);
});

test('retrieves authenticated user profile via Sanctum', function () {
    $token = $this->user->createToken('test_token')->plainTextToken;

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->getJson('/api/auth/me');

    $response->assertOk()
        ->assertJsonPath('user.email', 'api.customer@example.com');
});

test('logs out and revokes access token', function () {
    $token = $this->user->createToken('test_token')->plainTextToken;

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->postJson('/api/auth/logout');

    $response->assertOk();

    // Verify token record was deleted from personal_access_tokens table
    expect($this->user->tokens()->count())->toBe(0);
});

test('lists venues and filters by sport type', function () {
    $response = $this->getJson('/api/venues');
    $response->assertOk()
        ->assertJsonCount(1, 'data');

    $badmintonResponse = $this->getJson('/api/venues?sport=badminton');
    $badmintonResponse->assertOk()
        ->assertJsonCount(1, 'data');
});

test('returns real-time court availability slots', function () {
    $date = now()->addDays(5)->format('Y-m-d');

    $response = $this->getJson("/api/courts/{$this->courtBadminton->id}/availability?date={$date}");

    $response->assertOk()
        ->assertJsonStructure([
            'court' => ['id', 'name', 'sport_type', 'hourly_rate', 'open_time', 'close_time'],
            'booking_date',
            'slots' => [
                '*' => ['start_time', 'end_time', 'status', 'price', 'court_id', 'booking_date'],
            ],
        ])
        ->assertJsonCount(14, 'slots');
});

test('creates booking with 15-minute lock and snap token', function () {
    $token = $this->user->createToken('test_token')->plainTextToken;
    $date = now()->addDays(5)->format('Y-m-d');

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->postJson('/api/bookings', [
            'court_id' => $this->courtBadminton->id,
            'booking_date' => $date,
            'slots' => ['09:00', '10:00'],
        ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'message',
            'booking' => ['id', 'booking_code', 'status', 'total_price', 'lock_expires_at'],
            'payment' => ['snap_token', 'redirect_url', 'expires_at'],
        ]);

    $bookingCode = $response->json('booking.booking_code');

    // Confirm slot is now locked
    $availResponse = $this->getJson("/api/courts/{$this->courtBadminton->id}/availability?date={$date}");
    $slot09 = collect($availResponse->json('slots'))->firstWhere('start_time', '09:00');
    expect($slot09['status'])->toBe('locked');

    // Retrieve booking details by code
    $showResponse = $this->getJson("/api/bookings/{$bookingCode}");
    $showResponse->assertOk()
        ->assertJsonPath('booking.booking_code', $bookingCode);
});

test('blocks unauthenticated users from making bookings', function () {
    $date = now()->addDays(5)->format('Y-m-d');

    $response = $this->postJson('/api/bookings', [
        'court_id' => $this->courtBadminton->id,
        'booking_date' => $date,
        'slots' => ['14:00'],
    ]);

    $response->assertStatus(401);
});

test('retrieves user booking history', function () {
    $token = $this->user->createToken('test_token')->plainTextToken;
    $date = now()->addDays(5)->format('Y-m-d');

    // Make a booking
    $this->withHeader('Authorization', 'Bearer '.$token)
        ->postJson('/api/bookings', [
            'court_id' => $this->courtBadminton->id,
            'booking_date' => $date,
            'slots' => ['18:00'],
        ]);

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->getJson('/api/my-bookings');

    $response->assertOk()
        ->assertJsonCount(1, 'data');
});

test('processes midtrans webhook notification', function () {
    $token = $this->user->createToken('test_token')->plainTextToken;
    $date = now()->addDays(5)->format('Y-m-d');

    $createResponse = $this->withHeader('Authorization', 'Bearer '.$token)
        ->postJson('/api/bookings', [
            'court_id' => $this->courtBadminton->id,
            'booking_date' => $date,
            'slots' => ['20:00'],
        ]);

    $bookingCode = $createResponse->json('booking.booking_code');

    $webhookResponse = $this->postJson('/api/webhooks/midtrans', [
        'order_id' => $bookingCode,
        'status_code' => '200',
        'gross_amount' => '60000.00',
        'transaction_status' => 'settlement',
        'payment_type' => 'qris',
    ]);

    $webhookResponse->assertOk()
        ->assertJsonPath('booking_status', 'paid');

    $this->assertDatabaseHas('bookings', [
        'booking_code' => $bookingCode,
        'status' => 'paid',
        'payment_method' => 'qris',
    ]);
});

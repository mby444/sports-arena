<?php

namespace Database\Seeders;

use App\Models\Court;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class VenueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create Admin User
        User::firstOrCreate(
            ['email' => 'admin@arena.com'],
            [
                'name' => 'Admin Arena',
                'phone' => '081234567890',
                'role' => 'admin',
                'password' => Hash::make('password'),
            ]
        );

        // 2. Create Sample Customer User
        User::firstOrCreate(
            ['email' => 'customer@arena.com'],
            [
                'name' => 'Budi Santoso',
                'phone' => '089876543210',
                'role' => 'customer',
                'password' => Hash::make('password'),
            ]
        );

        // 3. Create Champion Sports Arena Venue
        $venue = Venue::firstOrCreate(
            ['slug' => 'champion-sports-arena'],
            [
                'name' => 'Champion Sports Arena',
                'address' => 'Jl. Pemuda No. 88, Rawamangun',
                'city' => 'Jakarta Timur',
                'phone' => '021-5557890',
                'description' => 'Premier indoor sports arena equipped with BWF-certified wooden-cushioned badminton courts and high-grade FIFA-standard vinyl and synthetic turf futsal pitches with LED tournament lighting.',
                'image_url' => 'https://images.unsplash.com/photo-1546519638-68e109498ffc?w=800&auto=format&fit=crop&q=80',
                'is_active' => true,
            ]
        );

        // 4. Create Badminton Courts
        $badmintonCourts = [
            [
                'name' => 'Badminton Court 1 (BWF Pro)',
                'sport_type' => 'badminton',
                'hourly_rate' => 60000.00,
                'open_time' => '08:00:00',
                'close_time' => '22:00:00',
                'is_active' => true,
            ],
            [
                'name' => 'Badminton Court 2 (BWF Pro)',
                'sport_type' => 'badminton',
                'hourly_rate' => 60000.00,
                'open_time' => '08:00:00',
                'close_time' => '22:00:00',
                'is_active' => true,
            ],
            [
                'name' => 'Badminton Court 3 (Standard Vinyl)',
                'sport_type' => 'badminton',
                'hourly_rate' => 50000.00,
                'open_time' => '08:00:00',
                'close_time' => '22:00:00',
                'is_active' => true,
            ],
        ];

        foreach ($badmintonCourts as $courtData) {
            Court::firstOrCreate(
                [
                    'venue_id' => $venue->id,
                    'name' => $courtData['name'],
                ],
                $courtData
            );
        }

        // 5. Create Futsal Courts
        $futsalCourts = [
            [
                'name' => 'Futsal Court 1 (Interlock Vinyl)',
                'sport_type' => 'futsal',
                'hourly_rate' => 150000.00,
                'open_time' => '08:00:00',
                'close_time' => '22:00:00',
                'is_active' => true,
            ],
            [
                'name' => 'Futsal Court 2 (Synthetic Turf)',
                'sport_type' => 'futsal',
                'hourly_rate' => 140000.00,
                'open_time' => '08:00:00',
                'close_time' => '22:00:00',
                'is_active' => true,
            ],
        ];

        foreach ($futsalCourts as $courtData) {
            Court::firstOrCreate(
                [
                    'venue_id' => $venue->id,
                    'name' => $courtData['name'],
                ],
                $courtData
            );
        }
    }
}

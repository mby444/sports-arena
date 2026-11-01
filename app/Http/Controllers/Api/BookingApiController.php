<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Booking\StoreBookingRequest;
use App\Models\Booking;
use App\Models\Court;
use App\Services\MidtransService;
use App\Services\SlotManagerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class BookingApiController extends Controller
{
    public function __construct(
        protected SlotManagerService $slotManager,
        protected MidtransService $midtransService
    ) {}

    /**
     * Create a new booking with a 15-minute slot lock and Midtrans Snap token.
     */
    public function store(StoreBookingRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $court = Court::findOrFail($validated['court_id']);

        try {
            // 1. Atomically reserve slots with 15-minute lock
            $booking = $this->slotManager->reserveSlots(
                $request->user(),
                $court,
                $validated['booking_date'],
                $validated['slots']
            );

            // 2. Generate Midtrans Snap Token
            $snap = $this->midtransService->createSnapToken($booking);

            return response()->json([
                'message' => 'Court slot(s) held for 15 minutes. Please complete payment.',
                'booking' => $booking->fresh(['court.venue', 'slots', 'user']),
                'payment' => [
                    'snap_token' => $snap['snap_token'],
                    'redirect_url' => $snap['redirect_url'],
                    'expires_at' => $booking->lock_expires_at?->toIso8601String(),
                ],
            ], 201);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get booking details and E-ticket by booking code.
     */
    public function show(string $code): JsonResponse
    {
        $booking = Booking::where('booking_code', $code)
            ->with(['court.venue', 'slots', 'user'])
            ->firstOrFail();

        return response()->json([
            'booking' => $booking,
        ]);
    }

    /**
     * List authenticated customer's booking history.
     */
    public function myBookings(Request $request): JsonResponse
    {
        $filter = $request->query('filter', 'all'); // 'upcoming', 'past', 'all'

        $query = $request->user()
            ->bookings()
            ->with(['court.venue', 'slots'])
            ->latest();

        if ($filter === 'upcoming') {
            $query->where(function ($q) {
                $q->where('status', 'paid')
                    ->orWhere(function ($sub) {
                        $sub->where('status', 'pending')
                            ->where('lock_expires_at', '>', now());
                    });
            });
        } elseif ($filter === 'past') {
            $query->whereIn('status', ['completed', 'cancelled']);
        }

        $bookings = $query->paginate(15);

        return response()->json($bookings);
    }
}

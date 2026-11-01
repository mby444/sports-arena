<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Court\CheckAvailabilityRequest;
use App\Models\Court;
use App\Models\Venue;
use App\Services\SlotManagerService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VenueApiController extends Controller
{
    public function __construct(
        protected SlotManagerService $slotManager
    ) {}

    /**
     * List active venues with courts count and optional sport filter.
     */
    public function index(Request $request): JsonResponse
    {
        $sport = $request->query('sport');

        $venues = Venue::active()
            ->withCount(['courts' => function ($query) use ($sport) {
                $query->where('is_active', true);
                if ($sport) {
                    $query->where('sport_type', strtolower($sport));
                }
            }])
            ->when($sport, function ($query, $sport) {
                $query->whereHas('courts', function ($q) use ($sport) {
                    $q->where('is_active', true)
                        ->where('sport_type', strtolower($sport));
                });
            })
            ->with(['courts' => function ($query) {
                $query->where('is_active', true);
            }])
            ->get();

        return response()->json([
            'data' => $venues,
        ]);
    }

    /**
     * Get specific venue details with all active courts.
     */
    public function show(Venue $venue): JsonResponse
    {
        $venue->load(['courts' => function ($query) {
            $query->where('is_active', true);
        }]);

        return response()->json([
            'data' => $venue,
        ]);
    }

    /**
     * List active courts for a specific venue.
     */
    public function courts(Venue $venue, Request $request): JsonResponse
    {
        $sport = $request->query('sport');

        $courts = $venue->courts()
            ->active()
            ->when($sport, fn ($q) => $q->sport($sport))
            ->get();

        return response()->json([
            'venue' => [
                'id' => $venue->id,
                'name' => $venue->name,
            ],
            'data' => $courts,
        ]);
    }

    /**
     * Get real-time 1-hour slot availability for a court on a given date.
     */
    public function availability(Court $court, CheckAvailabilityRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $date = $validated['date'] ?? now()->format('Y-m-d');

        $slots = $this->slotManager->getSlotsForCourtAndDate($court, $date);

        return response()->json([
            'court' => [
                'id' => $court->id,
                'name' => $court->name,
                'sport_type' => $court->sport_type,
                'hourly_rate' => (float) $court->hourly_rate,
                'open_time' => Carbon::createFromTimeString($court->open_time)->format('H:i'),
                'close_time' => Carbon::createFromTimeString($court->close_time)->format('H:i'),
                'venue' => $court->venue ? [
                    'id' => $court->venue->id,
                    'name' => $court->venue->name,
                    'address' => $court->venue->address,
                ] : null,
            ],
            'booking_date' => $date,
            'slots' => $slots,
        ]);
    }
}

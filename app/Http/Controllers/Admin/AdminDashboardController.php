<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Court;
use App\Services\SlotManagerService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function __construct(
        protected SlotManagerService $slotManager
    ) {}

    /**
     * Display the Admin Dashboard metrics and daily court occupancy matrix.
     */
    public function index(Request $request): View
    {
        $selectedDate = $request->query('date', now()->format('Y-m-d'));
        $date = Carbon::parse($selectedDate)->format('Y-m-d');

        // Metrics for today
        $today = now()->format('Y-m-d');
        $todayBookingsCount = Booking::whereDate('booking_date', $today)
            ->whereIn('status', ['paid', 'completed'])
            ->count();

        $todayRevenue = Booking::whereDate('booking_date', $today)
            ->whereIn('status', ['paid', 'completed'])
            ->sum('total_price');

        $activeCourtsCount = Court::where('is_active', true)->count();

        $pendingLocksCount = Booking::activeLock()->count();

        // Master Schedule Matrix: All active courts with their slots on $date
        $courts = Court::active()->with('venue')->get();

        $scheduleMatrix = [];
        $totalSlotsCount = 0;
        $occupiedSlotsCount = 0;

        foreach ($courts as $court) {
            $slots = $this->slotManager->getSlotsForCourtAndDate($court, $date);
            $totalSlotsCount += count($slots);
            $occupiedSlotsCount += collect($slots)->whereIn('status', ['booked', 'locked'])->count();

            $scheduleMatrix[] = [
                'court' => $court,
                'slots' => $slots,
            ];
        }

        $occupancyRate = $totalSlotsCount > 0
            ? round(($occupiedSlotsCount / $totalSlotsCount) * 100)
            : 0;

        return view('admin.dashboard', [
            'selectedDate' => $date,
            'todayBookingsCount' => $todayBookingsCount,
            'todayRevenue' => $todayRevenue,
            'activeCourtsCount' => $activeCourtsCount,
            'pendingLocksCount' => $pendingLocksCount,
            'occupancyRate' => $occupancyRate,
            'scheduleMatrix' => $scheduleMatrix,
        ]);
    }
}

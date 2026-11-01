<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminBookingController extends Controller
{
    /**
     * Display master reservations list with filters.
     */
    public function index(Request $request): View
    {
        $status = $request->query('status');
        $search = $request->query('search');
        $date = $request->query('date');

        $query = Booking::with(['court.venue', 'user', 'slots'])->latest();

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        if ($date) {
            $query->whereDate('booking_date', $date);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('booking_code', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($sub) use ($search) {
                        $sub->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        }

        $bookings = $query->paginate(20)->withQueryString();

        return view('admin.bookings', [
            'bookings' => $bookings,
            'currentStatus' => $status ?? 'all',
            'search' => $search,
            'selectedDate' => $date,
        ]);
    }

    /**
     * Manually check-in customer at the venue counter.
     */
    public function checkIn(Booking $booking): RedirectResponse
    {
        $booking->update([
            'status' => 'completed',
        ]);

        return back()->with('success', "Booking [{$booking->booking_code}] marked as Checked In & Completed.");
    }

    /**
     * Admin manual cancellation of a booking.
     */
    public function cancel(Booking $booking): RedirectResponse
    {
        $booking->update([
            'status' => 'cancelled',
        ]);

        return back()->with('info', "Booking [{$booking->booking_code}] has been cancelled.");
    }
}

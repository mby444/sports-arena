<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Booking\ReserveSlotWebRequest;
use App\Http\Requests\Court\CheckAvailabilityRequest;
use App\Models\Booking;
use App\Models\Court;
use App\Models\User;
use App\Models\Venue;
use App\Services\MidtransService;
use App\Services\SlotManagerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use InvalidArgumentException;

class MobileController extends Controller
{
    public function __construct(
        protected SlotManagerService $slotManager,
        protected MidtransService $midtransService
    ) {}

    /**
     * Mobile Home / Explore Screen.
     */
    public function home(Request $request): View
    {
        $sport = $request->query('sport');

        $venues = Venue::active()
            ->withCount(['courts' => function ($q) use ($sport) {
                $q->where('is_active', true);
                if ($sport) {
                    $q->where('sport_type', strtolower($sport));
                }
            }])
            ->get();

        $courts = Court::active()
            ->with('venue')
            ->when($sport, fn ($q) => $q->sport($sport))
            ->get();

        return view('mobile.home', [
            'venues' => $venues,
            'courts' => $courts,
            'currentSport' => $sport,
        ]);
    }

    /**
     * Court Detail & Interactive Slot Selection Screen.
     */
    public function showCourt(Court $court, CheckAvailabilityRequest $request): View
    {
        $validated = $request->validated();
        $selectedDate = $validated['date'] ?? now()->format('Y-m-d');

        // Generate next 14 days carousel
        $dates = [];
        $start = now();
        for ($i = 0; $i < 14; $i++) {
            $day = $start->copy()->addDays($i);
            $dates[] = [
                'date' => $day->format('Y-m-d'),
                'day_name' => $day->format('D'),
                'day_num' => $day->format('d'),
                'month' => $day->format('M'),
                'is_today' => $day->isToday(),
                'is_selected' => $day->format('Y-m-d') === $selectedDate,
            ];
        }

        $slots = $this->slotManager->getSlotsForCourtAndDate($court, $selectedDate);

        return view('mobile.court-booking', [
            'court' => $court->load('venue'),
            'selectedDate' => $selectedDate,
            'dates' => $dates,
            'slots' => $slots,
        ]);
    }

    /**
     * Reserve Slots and Proceed to Checkout.
     */
    public function reserve(Court $court, ReserveSlotWebRequest $request): RedirectResponse
    {
        if (! Auth::check()) {
            session()->put('url.intended', url()->previous());

            return redirect()->route('mobile.login')
                ->with('error', 'Please log in or create an account to reserve court slots.');
        }

        $validated = $request->validated();

        try {
            $booking = $this->slotManager->reserveSlots(
                Auth::user(),
                $court,
                $validated['booking_date'],
                $validated['slots']
            );

            return redirect()->route('mobile.checkout', $booking);
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Checkout Screen with 15-Minute Countdown.
     */
    public function checkout(Booking $booking): View|RedirectResponse
    {
        if (Auth::id() !== $booking->user_id && ! Auth::user()->isAdmin()) {
            abort(403);
        }

        if ($booking->isPaid()) {
            return redirect()->route('mobile.success', $booking);
        }

        if ($booking->isExpired()) {
            $booking->update(['status' => 'cancelled']);

            return redirect()->route('mobile.court', $booking->court_id)
                ->with('error', 'Your 15-minute reservation lock expired. Please select your slots again.');
        }

        $snap = $this->midtransService->createSnapToken($booking);

        $booking->load(['court.venue', 'slots', 'user']);

        return view('mobile.checkout', [
            'booking' => $booking,
            'snapToken' => $snap['snap_token'],
            'redirectUrl' => $snap['redirect_url'],
            'isMock' => $this->midtransService->isMock(),
        ]);
    }

    /**
     * Process Mock Midtrans Payment.
     */
    public function mockPay(Booking $booking, Request $request): RedirectResponse
    {
        if (Auth::id() !== $booking->user_id && ! Auth::user()->isAdmin()) {
            abort(403);
        }

        $method = $request->input('payment_method', 'QRIS');

        $booking->update([
            'status' => 'paid',
            'payment_method' => $method,
            'paid_at' => now(),
            'lock_expires_at' => null,
        ]);

        return redirect()->route('mobile.success', $booking)
            ->with('success', 'Payment successful! Your court booking is confirmed.');
    }

    /**
     * Booking Confirmation & E-Ticket Screen.
     */
    public function success(Booking $booking): View
    {
        if (Auth::id() !== $booking->user_id && ! Auth::user()->isAdmin()) {
            abort(403);
        }

        $booking->load(['court.venue', 'slots', 'user']);

        return view('mobile.booking-success', [
            'booking' => $booking,
        ]);
    }

    /**
     * My Bookings Screen with tabbed history.
     */
    public function myBookings(Request $request): View
    {
        $tab = $request->query('tab', 'upcoming');

        $user = Auth::user();

        $allBookings = $user->bookings()
            ->with(['court.venue', 'slots'])
            ->latest()
            ->get();

        $upcoming = $allBookings->filter(function (Booking $b) {
            return $b->isPaid() || $b->isLockActive();
        });

        $completed = $allBookings->filter(function (Booking $b) {
            return $b->status === 'completed';
        });

        $cancelled = $allBookings->filter(function (Booking $b) {
            return $b->isCancelled() || ($b->isPending() && $b->isExpired());
        });

        return view('mobile.my-bookings', [
            'tab' => $tab,
            'upcoming' => $upcoming,
            'completed' => $completed,
            'cancelled' => $cancelled,
        ]);
    }

    /**
     * Profile Screen.
     */
    public function profile(): View
    {
        $user = Auth::user();
        $totalBookings = $user->bookings()->count();
        $paidBookings = $user->bookings()->where('status', 'paid')->count();

        return view('mobile.profile', [
            'user' => $user,
            'totalBookings' => $totalBookings,
            'paidBookings' => $paidBookings,
        ]);
    }

    /**
     * Mobile Login View.
     */
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('mobile.home');
        }

        return view('mobile.auth.login');
    }

    /**
     * Handle Mobile Login submission.
     */
    public function login(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->validated();
        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            return redirect()->intended(route('mobile.home'))
                ->with('success', 'Welcome back, '.Auth::user()->name.'!');
        }

        return back()->withErrors([
            'email' => 'Invalid email address or password.',
        ])->onlyInput('email');
    }

    /**
     * Mobile Register View.
     */
    public function showRegister(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('mobile.home');
        }

        return view('mobile.auth.register');
    }

    /**
     * Handle Mobile Registration submission.
     */
    public function register(RegisterRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'role' => 'customer',
            'password' => Hash::make($validated['password']),
        ]);

        Auth::login($user);

        return redirect()->route('mobile.home')
            ->with('success', 'Account created! Welcome to Champion Sports Arena.');
    }

    /**
     * Logout.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('mobile.home')
            ->with('info', 'You have been logged out.');
    }
}

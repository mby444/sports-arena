<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingSlot;
use App\Models\Court;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class SlotManagerService
{
    /**
     * Duration of the temporary slot reservation lock in minutes.
     */
    public const int LOCK_DURATION_MINUTES = 15;

    /**
     * Generate discrete 1-hour intervals for a court given its operating hours.
     *
     * @return array<int, array{start: string, end: string}>
     */
    public function generateHourlyIntervals(string $openTime, string $closeTime): array
    {
        $start = Carbon::createFromTimeString($openTime);
        $end = Carbon::createFromTimeString($closeTime);

        $intervals = [];

        while ($start->copy()->addHour()->lte($end)) {
            $nextHour = $start->copy()->addHour();
            $intervals[] = [
                'start' => $start->format('H:i'),
                'end' => $nextHour->format('H:i'),
            ];
            $start->addHour();
        }

        return $intervals;
    }

    /**
     * Retrieve all 1-hour slots for a court on a date with their availability status.
     *
     * @return array<int, array{start_time: string, end_time: string, status: string, price: float, court_id: int, booking_date: string}>
     */
    public function getSlotsForCourtAndDate(Court $court, string $date): array
    {
        $intervals = $this->generateHourlyIntervals($court->open_time, $court->close_time);

        // Fetch all active booking slots on this court and date
        $activeSlots = BookingSlot::where('court_id', $court->id)
            ->whereDate('booking_date', $date)
            ->whereHas('booking', function ($query) {
                $query->where(function ($sub) {
                    $sub->whereIn('status', ['paid', 'completed'])
                        ->orWhere(function ($q) {
                            $q->where('status', 'pending')
                                ->where('lock_expires_at', '>', now());
                        });
                });
            })
            ->with('booking')
            ->get()
            ->keyBy(function (BookingSlot $slot) {
                return Carbon::createFromTimeString($slot->start_time)->format('H:i');
            });

        $result = [];

        foreach ($intervals as $interval) {
            $key = $interval['start'];
            $slotRecord = $activeSlots->get($key);

            $status = 'available';

            if ($slotRecord !== null) {
                $booking = $slotRecord->booking;
                if ($booking->isPaid() || $booking->status === 'completed') {
                    $status = 'booked';
                } elseif ($booking->isLockActive()) {
                    $status = 'locked';
                }
            }

            $result[] = [
                'start_time' => $interval['start'],
                'end_time' => $interval['end'],
                'status' => $status,
                'price' => (float) $court->hourly_rate,
                'court_id' => $court->id,
                'booking_date' => $date,
            ];
        }

        return $result;
    }

    /**
     * Atomically reserve slots for a user with a 15-minute temporary lock.
     *
     * @param  array<int, string>  $slotTimes  Array of start times e.g. ['08:00', '09:00']
     *
     * @throws InvalidArgumentException
     */
    public function reserveSlots(User $user, Court $court, string $date, array $slotTimes): Booking
    {
        if (empty($slotTimes)) {
            throw new InvalidArgumentException('Please select at least one time slot.');
        }

        if (! $court->is_active) {
            throw new InvalidArgumentException('This court is currently not active.');
        }

        // Validate booking date is not in the past
        $bookingDate = Carbon::parse($date)->startOfDay();
        if ($bookingDate->isPast() && ! $bookingDate->isToday()) {
            throw new InvalidArgumentException('Cannot book a slot for a past date.');
        }

        // Normalize and sort slot times (format: 'H:i')
        $normalizedTimes = array_map(function ($time) {
            return Carbon::createFromTimeString($time)->format('H:i');
        }, $slotTimes);
        sort($normalizedTimes);

        // Verify all requested slots are within operating hours
        $validIntervals = collect($this->generateHourlyIntervals($court->open_time, $court->close_time))
            ->keyBy('start');

        foreach ($normalizedTimes as $time) {
            if (! $validIntervals->has($time)) {
                throw new InvalidArgumentException("Time slot {$time} is outside court operating hours.");
            }
        }

        $formattedDate = $bookingDate->format('Y-m-d');
        $searchTimes = array_map(fn ($t) => Carbon::createFromTimeString($t)->format('H:i:s'), $normalizedTimes);

        return DB::transaction(function () use ($user, $court, $formattedDate, $normalizedTimes, $searchTimes, $validIntervals) {
            // Check for conflicts: Any paid booking or active lock for this court, date, and start time
            $conflicts = BookingSlot::where('court_id', $court->id)
                ->whereDate('booking_date', $formattedDate)
                ->whereIn('start_time', $searchTimes)
                ->whereHas('booking', function ($query) {
                    $query->where(function ($sub) {
                        $sub->whereIn('status', ['paid', 'completed'])
                            ->orWhere(function ($q) {
                                $q->where('status', 'pending')
                                    ->where('lock_expires_at', '>', now());
                            });
                    });
                })
                ->lockForUpdate()
                ->exists();

            if ($conflicts) {
                throw new InvalidArgumentException('One or more selected slots are no longer available. Please choose another time.');
            }

            $totalPrice = count($normalizedTimes) * (float) $court->hourly_rate;
            $bookingCode = 'BK-'.Carbon::parse($formattedDate)->format('Ymd').'-'.strtoupper(Str::random(4));

            $booking = Booking::create([
                'booking_code' => $bookingCode,
                'user_id' => $user->id,
                'court_id' => $court->id,
                'booking_date' => $formattedDate,
                'total_price' => $totalPrice,
                'status' => 'pending',
                'lock_expires_at' => now()->addMinutes(self::LOCK_DURATION_MINUTES),
            ]);

            foreach ($normalizedTimes as $time) {
                $interval = $validIntervals->get($time);
                BookingSlot::create([
                    'booking_id' => $booking->id,
                    'court_id' => $court->id,
                    'booking_date' => $formattedDate,
                    'start_time' => Carbon::createFromTimeString($interval['start'])->format('H:i:s'),
                    'end_time' => Carbon::createFromTimeString($interval['end'])->format('H:i:s'),
                    'price' => (float) $court->hourly_rate,
                ]);
            }

            return $booking->load(['court.venue', 'slots', 'user']);
        });
    }
}

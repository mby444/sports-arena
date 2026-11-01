<?php

namespace App\Console\Commands;

use App\Models\Booking;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('bookings:release-expired')]
#[Description('Release pending court bookings whose 15-minute reservation lock has expired.')]
class ReleaseExpiredBookingsCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $expiredBookings = Booking::where('status', 'pending')
            ->whereNotNull('lock_expires_at')
            ->where('lock_expires_at', '<', now())
            ->get();

        $count = $expiredBookings->count();

        if ($count === 0) {
            $this->info('No expired bookings to release.');

            return self::SUCCESS;
        }

        foreach ($expiredBookings as $booking) {
            $booking->update([
                'status' => 'cancelled',
            ]);
        }

        $this->info("Successfully released {$count} expired booking(s).");

        return self::SUCCESS;
    }
}

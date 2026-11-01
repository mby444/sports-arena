<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'booking_code',
    'user_id',
    'court_id',
    'booking_date',
    'total_price',
    'status',
    'lock_expires_at',
    'snap_token',
    'payment_method',
    'paid_at',
])]
class Booking extends Model
{
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'booking_date' => 'date',
            'total_price' => 'decimal:2',
            'lock_expires_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    /**
     * Get the user who made the booking.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the court being booked.
     *
     * @return BelongsTo<Court, $this>
     */
    public function court(): BelongsTo
    {
        return $this->belongsTo(Court::class);
    }

    /**
     * Get the reserved time slots for this booking.
     *
     * @return HasMany<BookingSlot, $this>
     */
    public function slots(): HasMany
    {
        return $this->hasMany(BookingSlot::class);
    }

    /**
     * Check if the booking is fully paid.
     */
    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    /**
     * Check if the booking is pending payment.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if the booking is cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Check if the 15-minute slot lock is still active.
     */
    public function isLockActive(): bool
    {
        return $this->isPending() && $this->lock_expires_at !== null && $this->lock_expires_at->isFuture();
    }

    /**
     * Check if the 15-minute slot lock has expired.
     */
    public function isExpired(): bool
    {
        return $this->isPending() && ($this->lock_expires_at === null || $this->lock_expires_at->isPast());
    }

    /**
     * Scope for bookings that currently hold active slot locks.
     *
     * @param  Builder<Booking>  $query
     * @return Builder<Booking>
     */
    public function scopeActiveLock(Builder $query): Builder
    {
        return $query->where('status', 'pending')
            ->where('lock_expires_at', '>', now());
    }

    /**
     * Scope for confirmed/paid bookings.
     *
     * @param  Builder<Booking>  $query
     * @return Builder<Booking>
     */
    public function scopePaid(Builder $query): Builder
    {
        return $query->where('status', 'paid');
    }
}

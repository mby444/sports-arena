<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'venue_id',
    'name',
    'sport_type',
    'hourly_rate',
    'open_time',
    'close_time',
    'is_active',
])]
class Court extends Model
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
            'hourly_rate' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Scope a query to only include active courts.
     *
     * @param  Builder<Court>  $query
     * @return Builder<Court>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to courts for a specific sport.
     *
     * @param  Builder<Court>  $query
     * @return Builder<Court>
     */
    public function scopeSport(Builder $query, string $sport): Builder
    {
        return $query->where('sport_type', strtolower($sport));
    }

    /**
     * Get the venue that owns the court.
     *
     * @return BelongsTo<Venue, $this>
     */
    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    /**
     * Get the bookings for the court.
     *
     * @return HasMany<Booking, $this>
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Get the booking slots for the court.
     *
     * @return HasMany<BookingSlot, $this>
     */
    public function bookingSlots(): HasMany
    {
        return $this->hasMany(BookingSlot::class);
    }
}

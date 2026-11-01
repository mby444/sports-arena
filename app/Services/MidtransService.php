<?php

namespace App\Services;

use App\Models\Booking;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use InvalidArgumentException;

class MidtransService
{
    protected string $serverKey;

    protected string $clientKey;

    protected bool $isProduction;

    protected bool $isMock;

    public function __construct()
    {
        $this->serverKey = (string) config('services.midtrans.server_key', '');
        $this->clientKey = (string) config('services.midtrans.client_key', '');
        $this->isProduction = (bool) config('services.midtrans.is_production', false);
        $this->isMock = (bool) config('services.midtrans.mock', true);
    }

    /**
     * Check if running in mock mode.
     */
    public function isMock(): bool
    {
        return $this->isMock;
    }

    /**
     * Get Midtrans Snap API endpoint URL.
     */
    protected function getSnapUrl(): string
    {
        return $this->isProduction
            ? 'https://app.midtrans.com/snap/v1/transactions'
            : 'https://app.sandbox.midtrans.com/snap/v1/transactions';
    }

    /**
     * Create Snap Token for a booking.
     *
     * @return array{snap_token: string, redirect_url: string}
     */
    public function createSnapToken(Booking $booking): array
    {
        // 1. If in mock mode, generate local mock snap token
        if ($this->isMock || empty($this->serverKey) || str_starts_with($this->serverKey, 'SB-Mid-server-mock')) {
            $mockToken = 'MOCK-SNAP-'.str_replace('-', '', $booking->booking_code).'-'.Str::upper(Str::random(8));
            $mockUrl = '/mobile/mock-pay/'.$booking->booking_code;

            $booking->update([
                'snap_token' => $mockToken,
            ]);

            return [
                'snap_token' => $mockToken,
                'redirect_url' => $mockUrl,
            ];
        }

        // 2. Real Midtrans Sandbox / Production API Call
        $params = [
            'transaction_details' => [
                'order_id' => $booking->booking_code,
                'gross_amount' => (int) round($booking->total_price),
            ],
            'customer_details' => [
                'first_name' => $booking->user->name,
                'email' => $booking->user->email,
                'phone' => $booking->user->phone ?? '08123456789',
            ],
            'item_details' => [
                [
                    'id' => 'COURT-'.$booking->court_id,
                    'price' => (int) round($booking->court->hourly_rate),
                    'quantity' => $booking->slots()->count(),
                    'name' => $booking->court->name.' ('.$booking->booking_date->format('d M Y').')',
                ],
            ],
            'expiry' => [
                'duration' => SlotManagerService::LOCK_DURATION_MINUTES,
                'unit' => 'minute',
            ],
        ];

        $response = Http::withBasicAuth($this->serverKey, '')
            ->acceptJson()
            ->post($this->getSnapUrl(), $params);

        if ($response->failed()) {
            Log::error('Midtrans Snap Error', [
                'booking_code' => $booking->booking_code,
                'response' => $response->body(),
            ]);

            throw new InvalidArgumentException('Failed to generate payment gateway transaction: '.$response->body());
        }

        $data = $response->json();
        $token = $data['token'] ?? '';
        $redirectUrl = $data['redirect_url'] ?? '';

        $booking->update([
            'snap_token' => $token,
        ]);

        return [
            'snap_token' => $token,
            'redirect_url' => $redirectUrl,
        ];
    }

    /**
     * Handle incoming notification webhook from Midtrans.
     *
     * @param  array<string, mixed>  $payload
     *
     * @throws InvalidArgumentException
     */
    public function handleNotification(array $payload): Booking
    {
        $orderId = $payload['order_id'] ?? null;
        $statusCode = $payload['status_code'] ?? null;
        $grossAmount = $payload['gross_amount'] ?? null;
        $signatureKey = $payload['signature_key'] ?? null;
        $transactionStatus = $payload['transaction_status'] ?? null;
        $fraudStatus = $payload['fraud_status'] ?? null;
        $paymentType = $payload['payment_type'] ?? 'midtrans';

        if (! $orderId || ! $transactionStatus) {
            throw new InvalidArgumentException('Invalid notification payload.');
        }

        // Verify signature if not in mock mode
        if (! $this->isMock && ! str_starts_with($this->serverKey, 'SB-Mid-server-mock')) {
            $expectedSignature = hash('sha512', $orderId.$statusCode.$grossAmount.$this->serverKey);
            if ($signatureKey !== $expectedSignature) {
                Log::warning('Midtrans Webhook Invalid Signature', [
                    'order_id' => $orderId,
                    'signature_received' => $signatureKey,
                ]);

                throw new InvalidArgumentException('Invalid signature key.');
            }
        }

        $booking = Booking::where('booking_code', $orderId)->firstOrFail();

        if ($transactionStatus === 'capture') {
            if ($fraudStatus === 'accept' || empty($fraudStatus)) {
                $booking->update([
                    'status' => 'paid',
                    'payment_method' => $paymentType,
                    'paid_at' => now(),
                    'lock_expires_at' => null,
                ]);
            }
        } elseif ($transactionStatus === 'settlement') {
            $booking->update([
                'status' => 'paid',
                'payment_method' => $paymentType,
                'paid_at' => now(),
                'lock_expires_at' => null,
            ]);
        } elseif (in_array($transactionStatus, ['cancel', 'deny', 'expire'])) {
            $booking->update([
                'status' => 'cancelled',
            ]);
        } elseif ($transactionStatus === 'pending') {
            // Keep pending until lock expiration
            if ($booking->isExpired()) {
                $booking->update([
                    'status' => 'cancelled',
                ]);
            }
        }

        return $booking->fresh(['court.venue', 'slots', 'user']);
    }
}

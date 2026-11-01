<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MidtransService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class MidtransWebhookController extends Controller
{
    public function __construct(
        protected MidtransService $midtransService
    ) {}

    /**
     * Handle incoming payment notification webhook from Midtrans.
     */
    public function handle(Request $request): JsonResponse
    {
        try {
            $booking = $this->midtransService->handleNotification($request->all());

            return response()->json([
                'status' => 'success',
                'message' => 'Notification processed successfully.',
                'order_id' => $booking->booking_code,
                'booking_status' => $booking->status,
            ]);
        } catch (ModelNotFoundException) {
            return response()->json([
                'status' => 'error',
                'message' => 'Booking order not found.',
            ], 404);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}

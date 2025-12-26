<?php

namespace Arkenstone\Core\ECommerce\Stock\Http\Controllers\API\V1;

use Arkenstone\Core\ECommerce\Contracts\StockReservationServiceInterface;
use Arkenstone\Core\ECommerce\Stock\Http\Requests\ReserveStockRequest;
use Arkenstone\Core\ECommerce\Stock\Http\Resources\StockReservationResource;
use Arkenstone\Core\Helpers\ResponseProtocol;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class StockReservationController extends Controller
{
    public function __construct(private StockReservationServiceInterface $reservationService)
    {
    }

    /**
     * Reserve stock for cart or order.
     */
    public function reserve(ReserveStockRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            $reservation = $this->reservationService->reserve(
                $validated['stock_id'],
                $validated['quantity'],
                [
                    'type' => $validated['reference_type'],
                    'id' => $validated['reference_id'],
                ]
            );

            return ResponseProtocol::success(
                new StockReservationResource($reservation),
                'Stock reserved successfully',
                201
            );
        } catch (\Exception $e) {
            return ResponseProtocol::failed(
                ['error' => $e->getMessage()],
                'Failed to reserve stock',
                422
            );
        }
    }

    /**
     * Get a single reservation.
     */
    public function show(int $id): JsonResponse
    {
        $reservation = $this->reservationService->getReservation($id);

        if (!$reservation) {
            return ResponseProtocol::failed(
                null,
                'Reservation not found',
                404
            );
        }

        return ResponseProtocol::success(
            new StockReservationResource($reservation),
            'Reservation retrieved successfully',
            200
        );
    }

    /**
     * Update reservation status.
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'status' => 'required|string|in:pending,checking_out,committed,fulfilled,cancelled,expired',
        ]);

        try {
            $updated = $this->reservationService->updateStatus($id, $request->input('status'));

            if (!$updated) {
                return ResponseProtocol::failed(
                    null,
                    'Failed to update status',
                    400
                );
            }

            $reservation = $this->reservationService->getReservation($id);

            return ResponseProtocol::success(
                new StockReservationResource($reservation),
                'Reservation status updated successfully',
                200
            );
        } catch (\Exception $e) {
            return ResponseProtocol::failed(
                ['error' => $e->getMessage()],
                'Failed to update status',
                500
            );
        }
    }

    /**
     * Extend reservation expiry.
     */
    public function extend(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'minutes' => 'required|integer|min:1|max:60',
        ]);

        try {
            $extended = $this->reservationService->extendExpiry($id, $request->input('minutes'));

            if (!$extended) {
                return ResponseProtocol::failed(
                    null,
                    'Failed to extend reservation',
                    400
                );
            }

            $reservation = $this->reservationService->getReservation($id);

            return ResponseProtocol::success(
                new StockReservationResource($reservation),
                'Reservation extended successfully',
                200
            );
        } catch (\Exception $e) {
            return ResponseProtocol::failed(
                ['error' => $e->getMessage()],
                'Failed to extend reservation',
                500
            );
        }
    }

    /**
     * Release a reservation.
     */
    public function release(int $id): JsonResponse
    {
        try {
            $released = $this->reservationService->release($id);

            if (!$released) {
                return ResponseProtocol::failed(
                    null,
                    'Failed to release reservation',
                    400
                );
            }

            return ResponseProtocol::success(
                null,
                'Reservation released successfully',
                200
            );
        } catch (\Exception $e) {
            return ResponseProtocol::failed(
                ['error' => $e->getMessage()],
                'Failed to release reservation',
                500
            );
        }
    }

    /**
     * Commit a reservation (order placement).
     */
    public function commit(int $id): JsonResponse
    {
        try {
            $committed = $this->reservationService->commit($id);

            if (!$committed) {
                return ResponseProtocol::failed(
                    null,
                    'Failed to commit reservation',
                    400
                );
            }

            $reservation = $this->reservationService->getReservation($id);

            return ResponseProtocol::success(
                new StockReservationResource($reservation),
                'Reservation committed successfully',
                200
            );
        } catch (\Exception $e) {
            return ResponseProtocol::failed(
                ['error' => $e->getMessage()],
                'Failed to commit reservation',
                400
            );
        }
    }

    /**
     * Fulfill a reservation (order shipment).
     */
    public function fulfill(int $id): JsonResponse
    {
        try {
            $fulfilled = $this->reservationService->fulfill($id);

            if (!$fulfilled) {
                return ResponseProtocol::failed(
                    null,
                    'Failed to fulfill reservation',
                    400
                );
            }

            return ResponseProtocol::success(
                null,
                'Reservation fulfilled successfully',
                200
            );
        } catch (\Exception $e) {
            return ResponseProtocol::failed(
                ['error' => $e->getMessage()],
                'Failed to fulfill reservation',
                400
            );
        }
    }

    /**
     * Get active reservations for a stock.
     */
    public function activeByStock(int $stockId): JsonResponse
    {
        $reservations = $this->reservationService->getActiveReservations($stockId);

        return ResponseProtocol::success(
            StockReservationResource::collection($reservations),
            'Active reservations retrieved successfully',
            200
        );
    }

    /**
     * Get reservations by reference (cart/order).
     */
    public function byReference(Request $request): JsonResponse
    {
        $request->validate([
            'reference_type' => 'required|string|in:cart,order',
            'reference_id' => 'required|integer',
        ]);

        $reservations = $this->reservationService->getReservationsByReference(
            $request->input('reference_type'),
            $request->input('reference_id')
        );

        return ResponseProtocol::success(
            StockReservationResource::collection($reservations),
            'Reservations retrieved successfully',
            200
        );
    }
}

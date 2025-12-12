<?php

namespace Arkenstone\Core\ECommerce\Stock\Services;

use Arkenstone\Core\ECommerce\Contracts\StockReservationServiceInterface;
use Arkenstone\Core\ECommerce\Stock\Models\Stock;
use Arkenstone\Core\ECommerce\Stock\Models\StockReservation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StockReservationService implements StockReservationServiceInterface
{
    /**
     * Reserve stock for cart or order.
     */
    public function reserve(int $stockId, int $quantity, array $reference): StockReservation
    {
        return DB::transaction(function () use ($stockId, $quantity, $reference) {
            $stock = Stock::lockForUpdate()->findOrFail($stockId);

            // Check availability
            if (!$stock->canReserve($quantity)) {
                throw new \Exception("Insufficient stock available. Requested: {$quantity}, Available: {$stock->quantity_available}");
            }

            // Create reservation using Stock model method
            $reservation = $stock->reserve($quantity, $reference);

            Log::info('Stock reserved', [
                'reservation_id' => $reservation->id,
                'stock_id' => $stockId,
                'quantity' => $quantity,
                'reference' => $reference,
            ]);

            // Check if stock is now low
            if ($stock->fresh()->isLowStock()) {
                Log::warning('Low stock alert', [
                    'stock_id' => $stockId,
                    'sku' => $stock->sku,
                    'available' => $stock->quantity_available,
                    'min_level' => $stock->min_stock_level,
                ]);
            }

            return $reservation;
        });
    }

    /**
     * Update reservation status.
     */
    public function updateStatus(int $reservationId, string $status): bool
    {
        $reservation = StockReservation::findOrFail($reservationId);
        $updated = $reservation->updateStatus($status);

        if ($updated) {
            Log::info('Reservation status updated', [
                'reservation_id' => $reservationId,
                'new_status' => $status,
            ]);
        }

        return $updated;
    }

    /**
     * Extend reservation expiry time.
     */
    public function extendExpiry(int $reservationId, int $minutes): bool
    {
        $reservation = StockReservation::findOrFail($reservationId);
        $extended = $reservation->extend($minutes);

        if ($extended) {
            Log::info('Reservation expiry extended', [
                'reservation_id' => $reservationId,
                'minutes' => $minutes,
                'new_expires_at' => $reservation->expires_at,
            ]);
        }

        return $extended;
    }

    /**
     * Release a reservation (cancel/expired).
     */
    public function release(int $reservationId): bool
    {
        return DB::transaction(function () use ($reservationId) {
            $reservation = StockReservation::with('stock')->findOrFail($reservationId);
            $released = $reservation->release();

            if ($released) {
                Log::info('Reservation released', [
                    'reservation_id' => $reservationId,
                    'stock_id' => $reservation->stock_id,
                    'quantity' => $reservation->quantity,
                ]);
            }

            return $released;
        });
    }

    /**
     * Release all expired reservations (background job).
     */
    public function releaseExpired(): int
    {
        $expiredReservations = StockReservation::expired()->get();
        $count = 0;

        foreach ($expiredReservations as $reservation) {
            try {
                DB::transaction(function () use ($reservation) {
                    $reservation->release();
                });
                $count++;
            } catch (\Exception $e) {
                Log::error('Failed to release expired reservation', [
                    'reservation_id' => $reservation->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if ($count > 0) {
            Log::info('Released expired reservations', ['count' => $count]);
        }

        return $count;
    }

    /**
     * Commit reservation (order placed, payment successful).
     */
    public function commit(int $reservationId): bool
    {
        return DB::transaction(function () use ($reservationId) {
            $reservation = StockReservation::with('stock')->findOrFail($reservationId);

            // Check if reservation is still valid
            if ($reservation->isExpired()) {
                throw new \Exception("Cannot commit expired reservation");
            }

            if ($reservation->status !== 'pending' && $reservation->status !== 'checking_out') {
                throw new \Exception("Cannot commit reservation with status: {$reservation->status}");
            }

            $committed = $reservation->commit();

            if ($committed) {
                Log::info('Reservation committed', [
                    'reservation_id' => $reservationId,
                    'stock_id' => $reservation->stock_id,
                    'quantity' => $reservation->quantity,
                ]);
            }

            return $committed;
        });
    }

    /**
     * Fulfill reservation (order shipped).
     */
    public function fulfill(int $reservationId): bool
    {
        return DB::transaction(function () use ($reservationId) {
            $reservation = StockReservation::with('stock')->findOrFail($reservationId);

            if ($reservation->status !== 'committed') {
                throw new \Exception("Can only fulfill committed reservations. Current status: {$reservation->status}");
            }

            $fulfilled = $reservation->fulfill();

            if ($fulfilled) {
                Log::info('Reservation fulfilled', [
                    'reservation_id' => $reservationId,
                    'stock_id' => $reservation->stock_id,
                    'quantity' => $reservation->quantity,
                ]);

                // Check if stock is now low after fulfillment
                $stock = $reservation->stock->fresh();
                if ($stock->isLowStock()) {
                    Log::warning('Low stock after fulfillment', [
                        'stock_id' => $stock->id,
                        'sku' => $stock->sku,
                        'quantity_on_hand' => $stock->quantity_on_hand,
                        'min_level' => $stock->min_stock_level,
                    ]);
                }
            }

            return $fulfilled;
        });
    }

    /**
     * Get a single reservation by ID.
     */
    public function getReservation(int $id): ?StockReservation
    {
        return StockReservation::with('stock')->find($id);
    }

    /**
     * Get all active reservations for a stock.
     */
    public function getActiveReservations(int $stockId): Collection
    {
        return StockReservation::where('stock_id', $stockId)
            ->active()
            ->with('stock')
            ->get();
    }

    /**
     * Get reservations by reference (cart_id or order_id).
     */
    public function getReservationsByReference(string $type, int $id): Collection
    {
        return StockReservation::byReference($type, $id)
            ->with('stock')
            ->get();
    }
}

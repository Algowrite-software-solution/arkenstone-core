<?php

namespace Arkenstone\Core\ECommerce\Contracts;

use Arkenstone\Core\ECommerce\Stock\Models\StockReservation;
use Illuminate\Database\Eloquent\Collection;

interface StockReservationServiceInterface
{
    /**
     * Reserve stock for cart or order.
     *
     * @param int $stockId
     * @param int $quantity
     * @param array $reference ['type' => 'cart|order', 'id' => int]
     * @return StockReservation
     */
    public function reserve(int $stockId, int $quantity, array $reference): StockReservation;

    /**
     * Update reservation status.
     *
     * @param int $reservationId
     * @param string $status
     * @return bool
     */
    public function updateStatus(int $reservationId, string $status): bool;

    /**
     * Extend reservation expiry time.
     *
     * @param int $reservationId
     * @param int $minutes
     * @return bool
     */
    public function extendExpiry(int $reservationId, int $minutes): bool;

    /**
     * Release a reservation (cancel/expired).
     *
     * @param int $reservationId
     * @return bool
     */
    public function release(int $reservationId): bool;

    /**
     * Release all expired reservations (background job).
     *
     * @return int Number of released reservations
     */
    public function releaseExpired(): int;

    /**
     * Commit reservation (order placed, payment successful).
     *
     * @param int $reservationId
     * @return bool
     */
    public function commit(int $reservationId): bool;

    /**
     * Fulfill reservation (order shipped).
     *
     * @param int $reservationId
     * @return bool
     */
    public function fulfill(int $reservationId): bool;

    /**
     * Get a single reservation by ID.
     *
     * @param int $id
     * @return StockReservation|null
     */
    public function getReservation(int $id): ?StockReservation;

    /**
     * Get all active reservations for a stock.
     *
     * @param int $stockId
     * @return Collection
     */
    public function getActiveReservations(int $stockId): Collection;

    /**
     * Get reservations by reference (cart_id or order_id).
     *
     * @param string $type
     * @param int $id
     * @return Collection
     */
    public function getReservationsByReference(string $type, int $id): Collection;
}

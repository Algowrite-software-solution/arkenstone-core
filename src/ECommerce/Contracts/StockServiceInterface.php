<?php

namespace Arkenstone\Core\ECommerce\Contracts;

use Arkenstone\Core\ECommerce\Stock\Models\Stock;
use Illuminate\Database\Eloquent\Collection;

interface StockServiceInterface
{
    /**
     * Create a new stock item.
     *
     * @param array $data
     * @return Stock
     */
    public function createStock(array $data): Stock;

    /**
     * Update an existing stock item.
     *
     * @param int $id
     * @param array $data
     * @return Stock
     */
    public function updateStock(int $id, array $data): Stock;

    /**
     * Delete a stock item.
     *
     * @param int $id
     * @return bool
     */
    public function deleteStock(int $id): bool;

    /**
     * Get a single stock item by ID.
     *
     * @param int $id
     * @return Stock|null
     */
    public function getStock(int $id): ?Stock;

    /**
     * Get stocks with filters.
     *
     * @param array $filters
     * @return Collection
     */
    public function getStocks(array $filters = []): Collection;

    /**
     * Search stocks by query string.
     *
     * @param string $query
     * @return Collection
     */
    public function searchStocks(string $query): Collection;

    /**
     * Check stock availability for a given quantity.
     *
     * @param int $stockId
     * @param int $quantity
     * @return array ['available' => bool, 'quantity_available' => int, 'stock' => Stock]
     */
    public function checkAvailability(int $stockId, int $quantity): array;

    /**
     * Adjust stock quantity (increase or decrease).
     *
     * @param int $stockId
     * @param int $quantity (positive to increase, negative to decrease)
     * @param string $reason
     * @return bool
     */
    public function adjustQuantity(int $stockId, int $quantity, string $reason = 'adjustment'): bool;

    /**
     * Get all low stock items.
     *
     * @return Collection
     */
    public function getLowStockItems(): Collection;

    /**
     * Get all out of stock items.
     *
     * @return Collection
     */
    public function getOutOfStockItems(): Collection;
}

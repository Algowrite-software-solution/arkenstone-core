<?php

namespace Arkenstone\Core\ECommerce\Stock\Services;

use Arkenstone\Core\ECommerce\Contracts\StockServiceInterface;
use Arkenstone\Core\ECommerce\Stock\Enum\StockReservationStatus;
use Arkenstone\Core\ECommerce\Stock\Models\Stock;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StockService implements StockServiceInterface
{
    /**
     * Allowed relations for eager loading.
     */
    protected array $allowedRelations = ['product', 'supplier', 'variationOptions', 'reservations', 'image'];

    /**
     * Create a new stock item.
     */
    public function createStock(array $data): Stock
    {
        return DB::transaction(function () use ($data) {
            // Extract variation options if present
            $variationOptions = $data['variation_option_ids'] ?? [];
            unset($data['variation_option_ids']);

            $stock = Stock::create($data);

            // Attach variation options if provided
            if (!empty($variationOptions)) {
                $stock->variationOptions()->attach($variationOptions);
            }

            Log::info(
                'Stock created',
                ['stock_id' => $stock->id, 'sku' => $stock->sku]
            );

            return $stock->load($this->allowedRelations);
        });
    }

    /**
     * Update an existing stock item.
     */
    public function updateStock(int $id, array $data): Stock
    {
        return DB::transaction(function () use ($id, $data) {
            $stock = Stock::findOrFail($id);

            // Extract variation options if present
            $variationOptions = $data['variation_option_ids'] ?? null;
            unset($data['variation_option_ids']);

            $stock->update($data);

            // Sync variation options if provided
            if ($variationOptions !== null) {
                $stock->variationOptions()->sync($variationOptions);
            }

            Log::info(
                'Stock updated',
                ['stock_id' => $stock->id, 'sku' => $stock->sku]
            );

            return $stock->load($this->allowedRelations);
        });
    }

    /**
     * Delete a stock item.
     */
    public function deleteStock(int $id): bool
    {
        $stock = Stock::findOrFail($id);

        // Check if there are active reservations
        $activeReservations = $stock->reservations()
            ->whereIn('status', StockReservationStatus::activeStatuses())
            ->count();

        if ($activeReservations > 0) {
            throw new \Exception("Cannot delete stock with active reservations. Please release or fulfill them first.");
        }

        $deleted = $stock->delete();

        if ($deleted) {
            Log::info('Stock deleted', ['stock_id' => $id]);
        }

        return $deleted;
    }

    /**
     * Get a single stock item by ID.
     */
    public function getStock(int $id): ?Stock
    {
        return Stock::with($this->allowedRelations)->find($id);
    }

    /**
     * Get stocks with filters.
     */
    public function getStocks(array $filters = []): Collection
    {
        $query = Stock::query()->with($this->allowedRelations);

        // Filter by product
        if (isset($filters['product_id'])) {
            $query->byProduct($filters['product_id']);
        }

        // Filter by supplier
        if (isset($filters['supplier_id'])) {
            $query->bySupplier($filters['supplier_id']);
        }

        // Filter by status
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Filter active only
        if (isset($filters['active']) && $filters['active']) {
            $query->active();
        }

        // Filter low stock
        if (isset($filters['low_stock']) && $filters['low_stock']) {
            $query->lowStock();
        }

        // Filter out of stock
        if (isset($filters['out_of_stock']) && $filters['out_of_stock']) {
            $query->outOfStock();
        }

        // Filter in stock
        if (isset($filters['in_stock']) && $filters['in_stock']) {
            $query->inStock();
        }

        return $query->get();
    }

    /**
     * Search stocks by query string.
     */
    public function searchStocks(string $query): Collection
    {
        return Stock::with($this->allowedRelations)
            ->search($query)
            ->get();
    }

    /**
     * Check stock availability for a given quantity.
     */
    public function checkAvailability(int $stockId, int $quantity): array
    {
        $stock = Stock::findOrFail($stockId);

        // Check if stock is active and not soft deleted
        if ($stock->status !== 'active' || $stock->trashed()) {
            return [
                'available' => false,
                'quantity_available' => 0,
                'stock' => $stock,
                'reason' => $stock->trashed() ? 'Stock is deleted' : 'Stock is inactive',
            ];
        }

        // Calculate available quantity
        $availableQuantity = $stock->getAvailableQuantity();

        return [
            'available' => $availableQuantity >= $quantity,
            'quantity_available' => $availableQuantity,
            'stock' => $stock,
            'reason' => $availableQuantity >= $quantity ? null : 'Insufficient quantity',
        ];
    }

    /**
     * Adjust stock quantity (increase or decrease).
     */
    public function adjustQuantity(int $stockId, int $quantity, string $reason = 'adjustment'): bool
    {
        $stock = Stock::findOrFail($stockId);

        $oldQuantity = $stock->quantity_on_hand;
        $adjusted = $stock->adjustQuantity($quantity, $reason);

        if ($adjusted) {
            Log::info('Stock quantity adjusted', [
                'stock_id' => $stockId,
                'old_quantity' => $oldQuantity,
                'new_quantity' => $stock->quantity_on_hand,
                'adjustment' => $quantity,
                'reason' => $reason,
            ]);
        }

        return $adjusted;
    }

    /**
     * Get all low stock items.
     */
    public function getLowStockItems(): Collection
    {
        return Stock::with($this->allowedRelations)
            ->active()
            ->lowStock()
            ->get();
    }

    /**
     * Get all out of stock items.
     */
    public function getOutOfStockItems(): Collection
    {
        return Stock::with($this->allowedRelations)
            ->active()
            ->outOfStock()
            ->get();
    }
}

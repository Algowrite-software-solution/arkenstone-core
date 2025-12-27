<?php

namespace Arkenstone\Core\ECommerce\Stock\Services;

use Arkenstone\Core\ECommerce\Contracts\SupplierServiceInterface;
use Arkenstone\Core\ECommerce\Stock\Models\Supplier;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class SupplierService implements SupplierServiceInterface
{
    /**
     * Create a new supplier.
     */
    public function createSupplier(array $data): Supplier
    {
        // Generate supplier code if not provided
        if (empty($data['supplier_code'])) {
            $data['supplier_code'] = 'SUP-' . strtoupper(uniqid());
        }

        $supplier = Supplier::create($data);

        Log::info('Supplier created', [
            'supplier_id' => $supplier->id,
            'supplier_code' => $supplier->supplier_code,
        ]);

        return $supplier;
    }

    /**
     * Update an existing supplier.
     */
    public function updateSupplier(int $id, array $data): Supplier
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->update($data);

        Log::info('Supplier updated', [
            'supplier_id' => $supplier->id,
            'supplier_code' => $supplier->supplier_code,
        ]);

        return $supplier;
    }

    /**
     * Delete a supplier.
     */
    public function deleteSupplier(int $id): bool
    {
        $supplier = Supplier::findOrFail($id);

        // Check if supplier has stocks
        $stockCount = $supplier->stocks()->count();
        if ($stockCount > 0) {
            throw new \Exception("Cannot delete supplier with existing stocks. Please reassign or delete {$stockCount} stock items first.");
        }

        $deleted = $supplier->delete();

        if ($deleted) {
            Log::info('Supplier deleted', ['supplier_id' => $id]);
        }

        return $deleted;
    }

    /**
     * Get a single supplier by ID.
     */
    public function getSupplier(int $id): ?Supplier
    {
        return Supplier::with('stocks')->find($id);
    }

    /**
     * Get suppliers with filters.
     */
    public function getSuppliers(array $filters = []): Collection
    {
        $query = Supplier::query();

        // Filter by status
        if (isset($filters['status'])) {
            $query->byStatus($filters['status']);
        }

        // Filter active only
        if (isset($filters['active']) && $filters['active']) {
            $query->active();
        }

        return $query->get();
    }

    /**
     * Search suppliers by query string.
     */
    public function searchSuppliers(string $query): Collection
    {
        return Supplier::search($query)->get();
    }
}

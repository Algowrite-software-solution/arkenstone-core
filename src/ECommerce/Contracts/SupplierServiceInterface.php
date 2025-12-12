<?php

namespace Arkenstone\Core\ECommerce\Contracts;

use Arkenstone\Core\ECommerce\Stock\Models\Supplier;
use Illuminate\Database\Eloquent\Collection;

interface SupplierServiceInterface
{
    /**
     * Create a new supplier.
     *
     * @param array $data
     * @return Supplier
     */
    public function createSupplier(array $data): Supplier;

    /**
     * Update an existing supplier.
     *
     * @param int $id
     * @param array $data
     * @return Supplier
     */
    public function updateSupplier(int $id, array $data): Supplier;

    /**
     * Delete a supplier.
     *
     * @param int $id
     * @return bool
     */
    public function deleteSupplier(int $id): bool;

    /**
     * Get a single supplier by ID.
     *
     * @param int $id
     * @return Supplier|null
     */
    public function getSupplier(int $id): ?Supplier;

    /**
     * Get suppliers with filters.
     *
     * @param array $filters
     * @return Collection
     */
    public function getSuppliers(array $filters = []): Collection;

    /**
     * Search suppliers by query string.
     *
     * @param string $query
     * @return Collection
     */
    public function searchSuppliers(string $query): Collection;
}

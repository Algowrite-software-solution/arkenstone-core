<?php

namespace Arkenstone\Core\ECommerce\Contracts;

use Arkenstone\Core\ECommerce\Stock\Models\Variant;
use Illuminate\Database\Eloquent\Collection;

interface VariantServiceInterface
{
    /**
     * Create a new variant.
     *
     * @param array $data
     * @return Variant
     */
    public function createVariant(array $data): Variant;

    /**
     * Update an existing variant.
     *
     * @param int $id
     * @param array $data
     * @return Variant
     */
    public function updateVariant(int $id, array $data): Variant;

    /**
     * Delete a variant.
     *
     * @param int $id
     * @return bool
     */
    public function deleteVariant(int $id): bool;

    /**
     * Get a single variant by ID.
     *
     * @param int $id
     * @return Variant|null
     */
    public function getVariant(int $id): ?Variant;

    /**
     * Get all variants.
     *
     * @return Collection
     */
    public function getVariants(): Collection;
}

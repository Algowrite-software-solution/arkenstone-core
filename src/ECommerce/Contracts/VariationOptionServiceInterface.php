<?php

namespace Arkenstone\Core\ECommerce\Contracts;

use Arkenstone\Core\ECommerce\Stock\Models\VariationOption;
use Illuminate\Database\Eloquent\Collection;

interface VariationOptionServiceInterface
{
    /**
     * Create a new variation option.
     *
     * @param array $data
     * @return VariationOption
     */
    public function createOption(array $data): VariationOption;

    /**
     * Update an existing variation option.
     *
     * @param int $id
     * @param array $data
     * @return VariationOption
     */
    public function updateOption(int $id, array $data): VariationOption;

    /**
     * Delete a variation option.
     *
     * @param int $id
     * @return bool
     */
    public function deleteOption(int $id): bool;

    /**
     * Get a single variation option by ID.
     *
     * @param int $id
     * @return VariationOption|null
     */
    public function getOption(int $id): ?VariationOption;

    /**
     * Get all variation options for a specific variant.
     *
     * @param int $variantId
     * @return Collection
     */
    public function getOptionsByVariant(int $variantId): Collection;

    /**
     * Get all variation options.
     *
     * @return Collection
     */
    public function getAllOptions(): Collection;
}

<?php

namespace Arkenstone\Core\ECommerce\Stock\Services;

use Arkenstone\Core\ECommerce\Contracts\VariationOptionServiceInterface;
use Arkenstone\Core\ECommerce\Stock\Models\VariationOption;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class VariationOptionService implements VariationOptionServiceInterface
{
    /**
     * Create a new variation option.
     */
    public function createOption(array $data): VariationOption
    {
        $option = VariationOption::create($data);

        Log::info('Variation option created', [
            'option_id' => $option->id,
            'variant_id' => $option->variant_id,
            'name' => $option->name,
        ]);

        return $option->load('variant');
    }
 
    /**
     * Update an existing variation option.
     */
    public function updateOption(int $id, array $data): VariationOption
    {
        $option = VariationOption::findOrFail($id);
        $option->update($data);

        Log::info('Variation option updated', [
            'option_id' => $option->id,
            'name' => $option->name,
        ]);

        return $option->load('variant');
    }

    /**
     * Delete a variation option.
     */
    public function deleteOption(int $id): bool
    {
        $option = VariationOption::findOrFail($id);

        // Check if option is used in stocks
        $stockCount = $option->stocks()->count();
        if ($stockCount > 0) {
            throw new \Exception("Cannot delete variation option used in {$stockCount} stock items.");
        }

        $deleted = $option->delete();

        if ($deleted) {
            Log::info('Variation option deleted', ['option_id' => $id]);
        }

        return $deleted;
    }

    /**
     * Get a single variation option by ID.
     */
    public function getOption(int $id): ?VariationOption
    {
        return VariationOption::with(['variant', 'stocks'])->find($id);
    }

    /**
     * Get all variation options for a specific variant.
     */
    public function getOptionsByVariant(int $variantId): Collection
    {
        return VariationOption::byVariant($variantId)
            ->with('variant')
            ->get();
    }

    /**
     * Get all variation options.
     */
    public function getAllOptions(): Collection
    {
        return VariationOption::with('variant')->get();
    }
}

<?php

namespace Arkenstone\Core\ECommerce\Stock\Services;

use Arkenstone\Core\ECommerce\Contracts\VariantServiceInterface;
use Arkenstone\Core\ECommerce\Enum\APIDefaults;
use Arkenstone\Core\ECommerce\Stock\Models\Variant;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class VariantService implements VariantServiceInterface
{
    protected int $PER_PAGE;
    protected string $ORDER;

    public function __construct()
    {
        $this->PER_PAGE = config('arkenstone.api_defaults.per_page', 100000000);
        $this->ORDER = config('arkenstone.api_defaults.order', 'desc');
    }

    /**
     * Create a new variant.
     */
    public function createVariant(array $data): Variant
    {
        $variant = Variant::create($data);

        Log::info('Variant created', [
            'variant_id' => $variant->id,
            'name' => $variant->name,
        ]);

        return $variant;
    }

    /**
     * Update an existing variant.
     */
    public function updateVariant(int $id, array $data): Variant
    {
        $variant = Variant::findOrFail($id);
        $variant->update($data);

        Log::info('Variant updated', [
            'variant_id' => $variant->id,
            'name' => $variant->name,
        ]);

        return $variant;
    }

    /**
     * Delete a variant.
     */
    public function deleteVariant(int $id): bool
    {
        $variant = Variant::findOrFail($id);

        // Check if variant has options
        $optionCount = $variant->variationOptions()->count();
        if ($optionCount > 0) {
            throw new \Exception("Cannot delete variant with existing options. Please delete {$optionCount} options first.");
        }

        $deleted = $variant->delete();

        if ($deleted) {
            Log::info('Variant deleted', ['variant_id' => $id]);
        }

        return $deleted;
    }

    /**
     * Get a single variant by ID.
     */
    public function getVariant(int $id): ?Variant
    {
        return Variant::with('variationOptions')->find($id);
    }

    /**
     * Get all variants.
     */
    public function getVariants(): Collection
    {
        return Variant::with('variationOptions')->orderBy('created_at', $filters['order'] ?? $this->ORDER)->get();
    }
}

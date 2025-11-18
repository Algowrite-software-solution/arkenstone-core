<?php

namespace Arkenstone\Core\ECommerce\Product\Http\Controllers\API\V1;

use Arkenstone\Core\ECommerce\Product\Http\Requests\AttachTaxonomiesToProductRequest;
use Arkenstone\Core\ECommerce\Product\Http\Requests\DetachTaxonomiesRequest;
use Arkenstone\Core\ECommerce\Product\Http\Requests\SyncTaxonomiesToProductRequest;
use Arkenstone\Core\ECommerce\Product\Http\Resources\ProductTaxonomyResource;
use Arkenstone\Core\ECommerce\Product\Http\Resources\TaxonomyResource;
use Arkenstone\Core\ECommerce\Product\Services\ProductTaxonomyService;
use Arkenstone\Core\Helpers\ResponseProtocol;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class ProductTaxonomyController extends Controller
{
    protected ProductTaxonomyService $productTaxonomyService;

    public function __construct(ProductTaxonomyService $productTaxonomyService)
    {
        $this->productTaxonomyService = $productTaxonomyService;
    }

    /**
     * Get all taxonomies for a specific product.
     */
    public function getProductTaxonomies(int $productId): JsonResponse
    {
        $taxonomies = $this->productTaxonomyService->getTaxonomiesByProduct($productId);

        return ResponseProtocol::success(
            TaxonomyResource::collection($taxonomies),
            'Product taxonomies retrieved successfully'
        );
    }

    /**
     * Get all products for a specific taxonomy.
     */
    public function getTaxonomyProducts(int $taxonomyId): JsonResponse
    {
        $products = $this->productTaxonomyService->getProductsByTaxonomy($taxonomyId);

        return ResponseProtocol::success(
            $products,
            'Taxonomy products retrieved successfully'
        );
    }

    /**
     * Attach one or more taxonomies to a product.
     */
    public function attach(AttachTaxonomiesToProductRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $productId = $validated['product_id'];
        $taxonomyIds = $validated['taxonomy_ids'];

        $product = \Arkenstone\Core\ECommerce\Product\Models\Product::find($productId);

        if (!$product) {
            return ResponseProtocol::error(null, 'Product not found', 404);
        }

        $currentTaxonomyIds = $product->taxonomies()->pluck('taxonomies.id')->toArray();

        $attached = [];
        $alreadyAttached = [];
        $failed = [];

        foreach ($taxonomyIds as $taxonomyId) {
            if (in_array($taxonomyId, $currentTaxonomyIds)) {
                $alreadyAttached[] = $taxonomyId;
                continue;
            }

            $success = $this->productTaxonomyService->attachTaxonomy($productId, $taxonomyId);

            if ($success) {
                $attached[] = $taxonomyId;
            } else {
                $failed[] = $taxonomyId;
            }
        }

        $message = count($attached) === count($taxonomyIds)
            ? 'All taxonomies attached successfully'
            : sprintf('%d newly attached, %d already attached, %d failed', count($attached), count($alreadyAttached), count($failed));

        return ResponseProtocol::success(
            [
                'product_id' => $productId,
                'attached' => $attached,
                'already_attached' => $alreadyAttached,
                'failed' => $failed,
                'total_attached' => count($attached),
                'total_already_attached' => count($alreadyAttached),
                'total_failed' => count($failed),
            ],
            $message
        );
    }

    /**
     * Sync taxonomies for a product (replaces all existing).
     */
    public function sync(SyncTaxonomiesToProductRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $productId = $validated['product_id'];
        $taxonomyIds = $validated['taxonomy_ids'] ?? [];

        $success = $this->productTaxonomyService->syncTaxonomies($productId, $taxonomyIds);

        if (!$success) {
            return ResponseProtocol::error(
                null,
                'Failed to sync taxonomies. Product not found.',
                404
            );
        }

        $taxonomies = $this->productTaxonomyService->getTaxonomiesByProduct($productId);

        return ResponseProtocol::success(
            [
                'product_id' => $productId,
                'synced_taxonomy_ids' => $taxonomyIds,
                'total_synced' => count($taxonomyIds),
                'taxonomies' => TaxonomyResource::collection($taxonomies),
            ],
            'Taxonomies synced successfully'
        );
    }

    /**
     * Detach one or more taxonomies from a product.
     */
    public function detach(DetachTaxonomiesRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $productId = $validated['product_id'];
        $taxonomyIds = $validated['taxonomy_ids'];

        $detached = [];
        $notFound = [];

        foreach ($taxonomyIds as $taxonomyId) {
            $success = $this->productTaxonomyService->detachTaxonomy($productId, $taxonomyId);

            if ($success) {
                $detached[] = $taxonomyId;
            } else {
                $notFound[] = $taxonomyId;
            }
        }

        $message = count($detached) === count($taxonomyIds)
            ? 'All taxonomies detached successfully'
            : sprintf('%d taxonomies detached, %d not found', count($detached), count($notFound));

        return ResponseProtocol::success(
            [
                'product_id' => $productId,
                'detached' => $detached,
                'not_found' => $notFound,
                'total_detached' => count($detached),
                'total_not_found' => count($notFound),
            ],
            $message
        );
    }
}

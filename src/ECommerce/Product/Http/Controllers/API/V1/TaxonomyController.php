<?php

namespace Arkenstone\Core\ECommerce\Product\Http\Controllers\API\V1;

use Arkenstone\Core\ECommerce\Product\Http\Requests\StoreTaxonomyRequest;
use Arkenstone\Core\ECommerce\Product\Http\Requests\UpdateTaxonomyRequest;
use Arkenstone\Core\ECommerce\Product\Http\Resources\TaxonomyResource;
use Arkenstone\Core\ECommerce\Product\Http\Resources\Collection\TaxonomyCollection;
use Arkenstone\Core\ECommerce\Product\Models\Taxonomy;
use Arkenstone\Core\ECommerce\Contracts\TaxonomyServiceInterface;
use Arkenstone\Core\Helpers\ResponseProtocol;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;

class TaxonomyController extends Controller
{

    public function __construct(private TaxonomyServiceInterface $taxonomyService)
    {
    }

    /**
     * Display a listing of taxonomies.
     */
    public function index(Request $request): JsonResponse
    {
        $taxonomies = $this->taxonomyService->listTaxonomies(request()->all());
        return ResponseProtocol::success(
            new TaxonomyCollection($taxonomies),
            'Taxonomies retrieved successfully'
        );
    }

    /**
     * Store a newly created taxonomy.
     */
    public function store(StoreTaxonomyRequest $request): JsonResponse
    {
        $taxonomy = $this->taxonomyService->createTaxonomy($request->validated());
        return ResponseProtocol::success(
            new TaxonomyResource($taxonomy),
            "Taxonomy created successfully.",
            201
        );
    }

    /**
     * Display the specified taxonomy.
     */
    public function show(Taxonomy $taxonomy): JsonResponse
    {
        $taxonomy->load(['type', 'parent', 'children']);
        return ResponseProtocol::success(
            new TaxonomyResource($taxonomy),
            "Taxonomy retrieved successfully."
        );
    }

    /**
     * Update the specified taxonomy.
     */
    public function update(UpdateTaxonomyRequest $request, Taxonomy $taxonomy): JsonResponse
    {
        try {
            $updated = $this->taxonomyService->updateTaxonomy($taxonomy, $request->validated());
            return ResponseProtocol::success(new TaxonomyResource($updated), "Taxonomy updated successfully.");
        } catch (Exception $e) {
            return ResponseProtocol::failed($e->getMessage(), 400);
        }
    }

    /**
     * Remove the specified taxonomy.
     */
    public function destroy(Taxonomy $taxonomy): JsonResponse
    {
        try {
            $returnOfService = $this->taxonomyService->deleteTaxonomy($taxonomy);
            if ($returnOfService) {
                return ResponseProtocol::success(null, "Taxonomy and its children deleted successfully.");
            } else {
                return ResponseProtocol::failed("Failed to delete taxonomy.", 500);
            }
        } catch (Exception $e) {
            return ResponseProtocol::failed($e->getMessage(), 400);
        }
    }

    /**
     * GET the active taxonomies.
     */
    public function active(): JsonResponse
    {
        $taxonomies = $this->taxonomyService->getActiveTaxonomies();
        return ResponseProtocol::success(
            TaxonomyResource::collection($taxonomies),
            "Active taxonomies retrieved successfully."
        );
    }

    /**
     * GET taxonomies filtered by type.
     */
    public function byType(int $typeId): JsonResponse
    {
        $taxonomies = $this->taxonomyService->listTaxonomies(['taxonomy_type_id' => $typeId]);
        return ResponseProtocol::success(
            new TaxonomyCollection($taxonomies),
            "Taxonomies filtered by type retrieved successfully."
        );
    }

    /**
     * Get children of a specific taxonomy.
     */
    public function children(int $id): JsonResponse
    {
        $children = $this->taxonomyService->getTaxonomyChildren($id);

        return ResponseProtocol::success(
            TaxonomyResource::collection($children),
            'Taxonomy children retrieved successfully'
        );
    }

    /**
     * Get root taxonomies (no parent).
     */
    public function roots(): JsonResponse
    {
        $rootTaxonomies = $this->taxonomyService->getRootTaxonomies();

        return ResponseProtocol::success(
            TaxonomyResource::collection($rootTaxonomies),
            'Root taxonomies retrieved successfully'
        );
    }
}

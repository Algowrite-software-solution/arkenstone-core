<?php

namespace Arkenstone\Core\ECommerce\Product\Http\Controllers\API\V1;

use Arkenstone\Core\ECommerce\Product\Http\Requests\StoreTaxonomyRequest;
use Arkenstone\Core\ECommerce\Product\Http\Requests\UpdateTaxonomyRequest;
use Arkenstone\Core\ECommerce\Product\Http\Resources\TaxonomyResource;
use Arkenstone\Core\ECommerce\Product\Http\Resources\Collection\TaxonomyCollection;
use Arkenstone\Core\ECommerce\Product\Models\Taxonomy;
use Arkenstone\Core\ECommerce\Contracts\TaxonomyServiceInterface;
use Arkenstone\Core\Helpers\ResponseProtocol;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class TaxonomyController extends Controller
{
    protected TaxonomyServiceInterface $taxonomyService;

    public function __construct(TaxonomyServiceInterface $taxonomyService)
    {
        $this->taxonomyService = $taxonomyService;
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
        $updated = $this->taxonomyService->updateTaxonomy($taxonomy, $request->validated());
        return ResponseProtocol::success(new TaxonomyResource($updated), "Taxonomy updated successfully.");
    }

    /**
     * Remove the specified taxonomy.
     */
    public function destroy(Taxonomy $taxonomy): JsonResponse
    {
        $returnOfService = $this->taxonomyService->deleteTaxonomy($taxonomy);
        if ($returnOfService) {
            return ResponseProtocol::success(null, "Taxonomy and its children deleted successfully.");
        } else {
            return ResponseProtocol::failed("Failed to delete taxonomy.", 500);
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
}

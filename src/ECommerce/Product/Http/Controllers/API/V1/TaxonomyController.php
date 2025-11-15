<?php

namespace Arkenstone\Core\ECommerce\Product\Http\Controllers\API\V1;

use Arkenstone\Core\ECommerce\Product\Http\Requests\StoreTaxonomyRequest;
use Arkenstone\Core\ECommerce\Product\Http\Requests\UpdateTaxonomyRequest;
use Arkenstone\Core\ECommerce\Product\Http\Resources\TaxonomyResource;
use Arkenstone\Core\ECommerce\Product\Http\Resources\Collection\TaxonomyCollection;
use Arkenstone\Core\ECommerce\Product\Models\Taxonomy;
use Arkenstone\Core\ECommerce\Product\Services\TaxonomyService;
use Arkenstone\Core\Helpers\ResponseProtocol;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class TaxonomyController extends Controller
{
    protected TaxonomyService $taxonomyService;

    public function __construct(TaxonomyService $taxonomyService)
    {
        $this->taxonomyService = $taxonomyService;
    }

    /**
     * Display a listing of taxonomies.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 15);
        $isActive = $request->input('is_active');
        $typeId = $request->input('type_id');

        $query = Taxonomy::query()->with(['taxonomyType', 'parent', 'children']);

        if ($isActive !== null) {
            $query->where('is_active', filter_var($isActive, FILTER_VALIDATE_BOOLEAN));
        }

        if ($typeId !== null) {
            $query->where('taxonomy_type_id', $typeId);
        }

        $taxonomies = $query->paginate($perPage);

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
        $validated = $request->validated();

        $taxonomy = $this->taxonomyService->createTaxonomy($validated);

        $taxonomy->load(['taxonomyType', 'parent', 'children']);

        return ResponseProtocol::success(
            new TaxonomyResource($taxonomy),
            'Taxonomy created successfully',
            201
        );
    }

    /**
     * Display the specified taxonomy.
     */
    public function show(int $id): JsonResponse
    {
        $taxonomy = Taxonomy::with(['taxonomyType', 'parent', 'children'])->find($id);

        if (!$taxonomy) {
            return ResponseProtocol::error(
                null,
                'Taxonomy not found',
                404
            );
        }

        return ResponseProtocol::success(
            new TaxonomyResource($taxonomy),
            'Taxonomy retrieved successfully'
        );
    }

    /**
     * Update the specified taxonomy.
     */
    public function update(UpdateTaxonomyRequest $request, int $id): JsonResponse
    {
        $validated = $request->validated();

        $success = $this->taxonomyService->updateTaxonomy($id, $validated);

        if (!$success) {
            return ResponseProtocol::error(
                null,
                'Taxonomy not found',
                404
            );
        }

        $taxonomy = Taxonomy::with(['taxonomyType', 'parent', 'children'])->find($id);

        return ResponseProtocol::success(
            new TaxonomyResource($taxonomy),
            'Taxonomy updated successfully'
        );
    }

    /**
     * Remove the specified taxonomy.
     */
    public function destroy(int $id): JsonResponse
    {
        $success = $this->taxonomyService->deleteTaxonomy($id);

        if (!$success) {
            return ResponseProtocol::error(
                null,
                'Taxonomy not found',
                404
            );
        }

        return ResponseProtocol::success(
            null,
            'Taxonomy deleted successfully'
        );
    }

    /**
     * Get taxonomies by type.
     */
    public function byType(int $typeId): JsonResponse
    {
        $taxonomies = $this->taxonomyService->getTaxonomiesByType($typeId);

        return ResponseProtocol::success(
            TaxonomyResource::collection($taxonomies),
            'Taxonomies retrieved successfully'
        );
    }
}

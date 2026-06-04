<?php
namespace Arkenstone\Core\ECommerce\Product\Http\Controllers\API\V1;

use Arkenstone\Core\ECommerce\Product\Http\Requests\StoreTaxonomyTypeRequest;
use Arkenstone\Core\ECommerce\Product\Http\Requests\UpdateTaxonomyTypeRequest;
use Arkenstone\Core\ECommerce\Product\Http\Resources\TaxonomyTypeResource;
use Arkenstone\Core\ECommerce\Product\Http\Resources\Collection\TaxonomyTypeCollection;
use Arkenstone\Core\ECommerce\Product\Models\TaxonomyType;
use Arkenstone\Core\ECommerce\Contracts\TaxonomyTypeServiceInterface;
use Arkenstone\Core\Helpers\ResponseProtocol;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * -------------------------------------------------------------------------------------
 * @group Taxonomy Types
 * APIs for managing taxonomy types (e.g., categories, tags)
 * 
 * A Taxonomy Type represents a classification type for products, such as categories or tags.
 * This controller provides endpoints to create, update, delete, and list taxonomy types.
 * 
 * @authenticated
 * 
 * @header Authorization Bearer {token}
 * 
 * @apiResourceCollection Modules\Product\Http\Resources\TaxonomyTypeCollection
 * @apiResourceModel Modules\Product\Models\TaxonomyType
 * -------------------------------------------------------------------------------------
 */
class TaxonomyTypeController extends Controller
{


    public function __construct(private TaxonomyTypeServiceInterface $taxonomyTypeService)
    {

    }

    // GET /taxonomy-types
    public function index(Request $request)
    {
        $types = $this->taxonomyTypeService->listTypes($request->all());
        return ResponseProtocol::success(new TaxonomyTypeCollection($types), "Taxonomy types retrieved successfully.");
    }

    // POST /taxonomy-types
    public function store(StoreTaxonomyTypeRequest $request)
    {
        $type = $this->taxonomyTypeService->createType($request->validated());
        return ResponseProtocol::success(new TaxonomyTypeResource($type), "Taxonomy type created successfully.");
    }

    // PUT/PATCH /taxonomy-types/{taxonomyType}
    public function update(UpdateTaxonomyTypeRequest $request, TaxonomyType $taxonomyType)
    {
        try {
            $updated = $this->taxonomyTypeService->updateType($taxonomyType, $request->validated());
            return ResponseProtocol::success(new TaxonomyTypeResource($updated), "Taxonomy type updated successfully.");
        } catch (Exception $e) {
            return ResponseProtocol::failed($e->getMessage(), 400);
        }
    }

    // GET /taxonomy-types/{taxonomyType}
    public function show(TaxonomyType $taxonomyType)
    {
        $taxonomyType->load('taxonomies');
        return ResponseProtocol::success(new TaxonomyTypeResource($taxonomyType), "Taxonomy type retrieved successfully.");
    }

    // DELETE /taxonomy-types/{taxonomyType}
    public function destroy(TaxonomyType $taxonomyType)
    {
        try {
            $this->taxonomyTypeService->deleteType($taxonomyType);
            return ResponseProtocol::success(null, "Taxonomy type deleted successfully.");
        } catch (Exception $e) {
            return ResponseProtocol::failed($e->getMessage(), 400);
        }
    }
}
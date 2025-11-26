<?php

namespace Arkenstone\Core\ECommerce\Product\Http\Controllers\API\V1;

use Arkenstone\Core\ECommerce\Contracts\ProductTaxonomyServiceInterface;
use Arkenstone\Core\ECommerce\Product\Http\Requests\AttachTaxonomiesToProductRequest;
use Arkenstone\Core\ECommerce\Product\Http\Requests\DetachTaxonomiesRequest;
use Arkenstone\Core\ECommerce\Product\Http\Requests\SyncTaxonomiesToProductRequest;
use Arkenstone\Core\ECommerce\Product\Http\Resources\ProductResource;
use Arkenstone\Core\ECommerce\Product\Http\Resources\TaxonomyResource;
use Arkenstone\Core\ECommerce\Product\Models\Product;
use Arkenstone\Core\ECommerce\Product\Models\Taxonomy;
use Arkenstone\Core\ECommerce\Product\Services\ProductTaxonomyService;
use Arkenstone\Core\Helpers\ResponseProtocol;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

class ProductTaxonomyController extends Controller
{


    public function __construct(private ProductTaxonomyServiceInterface $productTaxonomyService)
    {

    }

    // GET /products/{product}/taxonomies
    public function index(Request $request, Product $product)
    {
        $typeId = $request->query('type_id');
        $taxonomies = $this->productTaxonomyService->getProductTaxonomies($product, $typeId); // expect Collection
        return ResponseProtocol::success(
            TaxonomyResource::collection($taxonomies),
            'Product taxonomies retrieved successfully.'
        );
    }

    // The attach method handles bulk attach from request body
    public function attach(AttachTaxonomiesToProductRequest $request)
    {
        $product = Product::findOrFail($request->validated()['product_id']);
        $taxonomyIds = $request->validated()['taxonomy_ids'];

        $attached = [];
        $alreadyAttached = [];

        foreach ($taxonomyIds as $taxonomyId) {
            if ($product->taxonomies->contains($taxonomyId)) {
                $alreadyAttached[] = $taxonomyId;
            } else {
                $attached[] = $taxonomyId;
            }
        }

        if (!empty($attached)) {
            $this->productTaxonomyService->attachToProduct($product, $attached);
        }

        return ResponseProtocol::success(
            [
                'attached' => $attached,
                'already_attached' => $alreadyAttached
            ],
            'Taxonomies attached to product successfully.'
        );
    }

    // The sync method handles bulk sync from request body
    public function sync(SyncTaxonomiesToProductRequest $request)
    {
        $product = Product::findOrFail($request->validated()['product_id']);
        $taxonomyIds = $request->validated()['taxonomy_ids'];

        $this->productTaxonomyService->syncForProduct($product, $taxonomyIds);

        return ResponseProtocol::success(
            null,
            'Product taxonomies synchronized successfully.'
        );
    }

    // The detach method handles bulk detach from request body
    public function detach(DetachTaxonomiesRequest $request)
    {
        $product = Product::findOrFail($request->validated()['product_id']);
        $taxonomyIds = $request->validated()['taxonomy_ids'];

        $detached = [];
        $notFound = [];

        foreach ($taxonomyIds as $taxonomyId) {
            if ($product->taxonomies->contains($taxonomyId)) {
                $taxonomy = Taxonomy::find($taxonomyId);
                if ($taxonomy) {
                    $this->productTaxonomyService->detachFromProduct($product, $taxonomy);
                    $detached[] = $taxonomyId;
                }
            } else {
                $notFound[] = $taxonomyId;
            }
        }

        return ResponseProtocol::success(
            [
                'detached' => $detached,
                'not_found' => $notFound
            ],
            'Taxonomies detached from product successfully.'
        );
    }

    /**
     * |-------------------------------------------------------------------------------------|
     *      @route GET /taxonomies/{taxonomy}/products
     *      This endpoint retrieves all products associated with a specific taxonomy.
     *      @Note This endpoint requires the user to be authenticated.
     *      @Note This method not implemented yet.
     * |-------------------------------------------------------------------------------------|
     */
    public function products(Request $request, Taxonomy $taxonomy)
    {
        $with = (array) $request->query('with', []);
        $products = $this->productTaxonomyService->getProductsByTaxonomy($taxonomy, $with);
        return ResponseProtocol::success(
            ProductResource::collection($products),
            "Products retrieved successfully."
        );
    }
}

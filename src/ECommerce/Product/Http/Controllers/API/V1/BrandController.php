<?php

namespace Arkenstone\Core\ECommerce\Product\Http\Controllers\API\V1;

use Arkenstone\Core\ECommerce\Contracts\BrandServiceInterface;
use Arkenstone\Core\ECommerce\Product\Http\Requests\StoreBrandRequest;
use Arkenstone\Core\ECommerce\Product\Http\Requests\UpdateBrandRequest;
use Arkenstone\Core\ECommerce\Product\Http\Resources\BrandResource;
use Arkenstone\Core\ECommerce\Product\Http\Resources\Collection\BrandCollection;
use Arkenstone\Core\Helpers\ResponseProtocol;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class BrandController extends Controller
{

    public int $PER_PAGE;
    public string $ORDER;

    public function __construct(private BrandServiceInterface $brandService)
    {
        $this->PER_PAGE = config('arkenstone.api_defaults.per_page', 100000000);
        $this->ORDER = config('arkenstone.api_defaults.order', 'desc');
    }

    /**
     * Display a listing of brands.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = [
            'limit' => $request->input('per_page', $this->PER_PAGE),
            'with_inactive' => $request->input('with_inactive', false),
        ];

        $brands = $this->brandService->queryBrands($filters);

        return ResponseProtocol::success(
            new BrandCollection($brands),
            'Brands retrieved successfully',
            200
        );
    }

    /**
     * Store a newly created brand.
     */
    public function store(StoreBrandRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $brand = $this->brandService->createBrand($validated);

        return ResponseProtocol::success(
            new BrandResource($brand),
            'Brand created successfully',
            201
        );
    }

    /**
     * Display the specified brand.
     */
    public function show(int $id): JsonResponse
    {
        $brand = $this->brandService->getBrandById($id);

        if (!$brand) {
            return ResponseProtocol::failed(
                null,
                'Brand not found',
                404
            );
        }

        return ResponseProtocol::success(
            new BrandResource($brand),
            'Brand retrieved successfully',
            200
        );
    }

    /**
     * Update the specified brand.
     */
    public function update(UpdateBrandRequest $request, int $id): JsonResponse
    {
        $validated = $request->validated();

        $success = $this->brandService->updateBrand($id, $validated);

        if (!$success) {
            return ResponseProtocol::failed(
                null,
                'Brand not found',
                404
            );
        }

        $brand = $this->brandService->getBrandById($id);

        return ResponseProtocol::success(
            new BrandResource($brand),
            'Brand updated successfully',
            200
        );
    }

    /**
     * Remove the specified brand.
     */
    public function destroy(int $id): JsonResponse
    {
        $success = $this->brandService->deleteBrand($id);

        if (!$success) {
            return ResponseProtocol::failed(
                null,
                'Brand not found',
                404
            );
        }

        return ResponseProtocol::success(
            null,
            'Brand deleted successfully',
            200
        );
    }
}

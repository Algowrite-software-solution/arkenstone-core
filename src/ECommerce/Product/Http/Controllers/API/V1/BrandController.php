<?php

namespace Arkenstone\Core\ECommerce\Product\Http\Controllers\API\V1;

use Arkenstone\Core\ECommerce\Product\Http\Requests\StoreBrandRequest;
use Arkenstone\Core\ECommerce\Product\Http\Requests\UpdateBrandRequest;
use Arkenstone\Core\ECommerce\Product\Http\Resources\BrandResource;
use Arkenstone\Core\ECommerce\Product\Http\Resources\Collection\BrandCollection;
use Arkenstone\Core\ECommerce\Product\Models\Brand;
use Arkenstone\Core\ECommerce\Product\Services\BrandService;
use Arkenstone\Core\Helpers\ResponseProtocol;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class BrandController extends Controller
{
    protected BrandService $brandService;

    public function __construct(BrandService $brandService)
    {
        $this->brandService = $brandService;
    }

    /**
     * Display a listing of brands.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 15);
        $isActive = $request->input('is_active');

        $query = Brand::query();

        if ($isActive !== null) {
            $query->where('is_active', filter_var($isActive, FILTER_VALIDATE_BOOLEAN));
        }

        $brands = $query->paginate($perPage);

        return ResponseProtocol::success(
            new BrandCollection($brands),
            'Brands retrieved successfully'
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
            return ResponseProtocol::error(
                null,
                'Brand not found',
                404
            );
        }

        return ResponseProtocol::success(
            new BrandResource($brand),
            'Brand retrieved successfully'
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
            return ResponseProtocol::error(
                null,
                'Brand not found',
                404
            );
        }

        $brand = $this->brandService->getBrandById($id);

        return ResponseProtocol::success(
            new BrandResource($brand),
            'Brand updated successfully'
        );
    }

    /**
     * Remove the specified brand.
     */
    public function destroy(int $id): JsonResponse
    {
        $success = $this->brandService->deleteBrand($id);

        if (!$success) {
            return ResponseProtocol::error(
                null,
                'Brand not found',
                404
            );
        }

        return ResponseProtocol::success(
            null,
            'Brand deleted successfully'
        );
    }
}

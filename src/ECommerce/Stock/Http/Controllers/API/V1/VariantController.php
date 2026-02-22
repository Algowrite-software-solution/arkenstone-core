<?php

namespace Arkenstone\Core\ECommerce\Stock\Http\Controllers\API\V1;

use Arkenstone\Core\ECommerce\Contracts\VariantServiceInterface;
use Arkenstone\Core\ECommerce\Stock\Http\Requests\StoreVariantRequest;
use Arkenstone\Core\ECommerce\Stock\Http\Resources\VariantResource;
use Arkenstone\Core\Helpers\ResponseProtocol;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class VariantController extends Controller
{
    public int $PER_PAGE;
    public string $ORDER;

    public function __construct(private VariantServiceInterface $variantService)
    {
        $this->PER_PAGE = config('arkenstone.api_defaults.per_page', 100000000);
        $this->ORDER = config('arkenstone.api_defaults.order', 'desc');
    }

    /**
     * Display a listing of variants.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', $this->PER_PAGE);
        $search = $request->input('search');

        $query = \Arkenstone\Core\ECommerce\Stock\Models\Variant::query()
            ->with('variationOptions');

        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        $variants = $query->paginate($perPage);

        return ResponseProtocol::success(
            VariantResource::collection($variants),
            'Variants retrieved successfully',
            200
        );
    }

    /**
     * Store a newly created variant.
     */
    public function store(StoreVariantRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $variant = $this->variantService->createVariant($validated);

            return ResponseProtocol::success(
                new VariantResource($variant),
                'Variant created successfully',
                201
            );
        } catch (\Exception $e) {
            return ResponseProtocol::failed(
                ['error' => $e->getMessage()],
                'Failed to create variant',
                500
            );
        }
    }

    /**
     * Display the specified variant.
     */
    public function show(int $id): JsonResponse
    {
        $variant = $this->variantService->getVariant($id);

        if (!$variant) {
            return ResponseProtocol::failed(
                null,
                'Variant not found',
                404
            );
        }

        return ResponseProtocol::success(
            new VariantResource($variant),
            'Variant retrieved successfully',
            200
        );
    }

    /**
     * Update the specified variant.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        try {
            $variant = $this->variantService->updateVariant($id, $request->only('name'));

            return ResponseProtocol::success(
                new VariantResource($variant),
                'Variant updated successfully',
                200
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ResponseProtocol::failed(
                null,
                'Variant not found',
                404
            );
        } catch (\Exception $e) {
            return ResponseProtocol::failed(
                ['error' => $e->getMessage()],
                'Failed to update variant',
                500
            );
        }
    }

    /**
     * Remove the specified variant.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $deleted = $this->variantService->deleteVariant($id);

            return ResponseProtocol::success(
                null,
                'Variant deleted successfully',
                200
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ResponseProtocol::failed(
                null,
                'Variant not found',
                404
            );
        } catch (\Exception $e) {
            return ResponseProtocol::failed(
                ['error' => $e->getMessage()],
                'Failed to delete variant',
                500
            );
        }
    }
}

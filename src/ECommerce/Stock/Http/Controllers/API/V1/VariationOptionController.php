<?php

namespace Arkenstone\Core\ECommerce\Stock\Http\Controllers\API\V1;

use Arkenstone\Core\ECommerce\Contracts\VariationOptionServiceInterface;
use Arkenstone\Core\ECommerce\Stock\Http\Requests\StoreVariationOptionRequest;
use Arkenstone\Core\ECommerce\Stock\Http\Resources\VariationOptionResource;
use Arkenstone\Core\Helpers\ResponseProtocol;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class VariationOptionController extends Controller
{
    public function __construct(private VariationOptionServiceInterface $optionService)
    {
    }

    /**
     * Get all options for a specific variant.
     */
    public function byVariant(int $variantId): JsonResponse
    {
        // Check if variant exists
        $variant = \Arkenstone\Core\ECommerce\Stock\Models\Variant::find($variantId);
        
        if (!$variant) {
            return ResponseProtocol::failed(
                null,
                'Variant not found',
                404
            );
        }

        $options = $this->optionService->getOptionsByVariant($variantId);

        return ResponseProtocol::success(
            VariationOptionResource::collection($options),
            'Variation options retrieved successfully',
            200
        );
    }

    /**
     * Store a newly created variation option.
     */
    public function store(StoreVariationOptionRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $option = $this->optionService->createOption($validated);

            return ResponseProtocol::success(
                new VariationOptionResource($option),
                'Variation option created successfully',
                201
            );
        } catch (\Exception $e) {
            return ResponseProtocol::failed(
                ['error' => $e->getMessage()],
                'Failed to create variation option',
                500
            );
        }
    }

    /**
     * Display the specified variation option.
     */
    public function show(int $id): JsonResponse
    {
        $option = $this->optionService->getOption($id);

        if (!$option) {
            return ResponseProtocol::failed(
                null,
                'Variation option not found',
                404
            );
        }

        return ResponseProtocol::success(
            new VariationOptionResource($option),
            'Variation option retrieved successfully',
            200
        );
    }

    /**
     * Update the specified variation option.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'variant_id' => 'sometimes|integer|exists:variants,id',
            'name' => 'sometimes|string|max:255',
            'meta' => 'nullable|array',
        ]);

        try {
            $option = $this->optionService->updateOption($id, $request->only(['variant_id', 'name', 'meta']));

            return ResponseProtocol::success(
                new VariationOptionResource($option),
                'Variation option updated successfully',
                200
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ResponseProtocol::failed(
                null,
                'Variation option not found',
                404
            );
        } catch (\Exception $e) {
            return ResponseProtocol::failed(
                ['error' => $e->getMessage()],
                'Failed to update variation option',
                500
            );
        }
    }

    /**
     * Remove the specified variation option.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $deleted = $this->optionService->deleteOption($id);

            return ResponseProtocol::success(
                null,
                'Variation option deleted successfully',
                200
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ResponseProtocol::failed(
                null,
                'Variation option not found',
                404
            );
        } catch (\Exception $e) {
            return ResponseProtocol::failed(
                ['error' => $e->getMessage()],
                'Failed to delete variation option',
                500
            );
        }
    }
}

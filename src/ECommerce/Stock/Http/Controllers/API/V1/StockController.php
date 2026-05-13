<?php

namespace Arkenstone\Core\ECommerce\Stock\Http\Controllers\API\V1;

use Arkenstone\Core\ECommerce\Contracts\StockServiceInterface;
use Arkenstone\Core\ECommerce\Stock\Http\Requests\StoreStockRequest;
use Arkenstone\Core\ECommerce\Stock\Http\Requests\UpdateStockRequest;
use Arkenstone\Core\ECommerce\Stock\Http\Resources\StockResource;
use Arkenstone\Core\ECommerce\Stock\Http\Resources\Collection\StockCollection;
use Arkenstone\Core\Helpers\ResponseProtocol;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class StockController extends Controller
{
    public int $PER_PAGE;
    public string $ORDER;

    public function __construct(private StockServiceInterface $stockService)
    {
        $this->PER_PAGE = config('arkenstone.api_defaults.per_page', 100000000);
        $this->ORDER = config('arkenstone.api_defaults.order', 'desc');
    }

    /**
     * Display a listing of stocks.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', $this->PER_PAGE);
        $filters = $request->only([
            'ids',
            'product_id',
            'supplier_id',
            'status',
            'active',
            'low_stock',
            'out_of_stock',
            'in_stock',
            'with_inactive'
        ]);

        $query = \Arkenstone\Core\ECommerce\Stock\Models\Stock::query()
            ->with(['product', 'supplier', 'variationOptions', 'reservations', 'image', 'product.images']);

        // Apply filters
        if (isset($filters['ids'])) {
            $query->whereIn('id', $filters['ids']);
        }
        if (isset($filters['product_id'])) {
            $query->byProduct($filters['product_id']);
        }
        if (isset($filters['supplier_id'])) {
            $query->bySupplier($filters['supplier_id']);
        }
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (isset($filters['active']) && $filters['active']) {
            $query->active();
        }
        if (isset($filters['low_stock']) && $filters['low_stock']) {
            $query->lowStock();
        }
        if (isset($filters['out_of_stock']) && $filters['out_of_stock']) {
            $query->outOfStock();
        }
        if (isset($filters['in_stock']) && $filters['in_stock']) {
            $query->inStock();
        }

        

        $stocks = $query->paginate($perPage);

        return ResponseProtocol::success(
            StockResource::collection($stocks)->response()->getData(true),
            'Stocks retrieved successfully',
            200
        );
    }

    /**
     * Store a newly created stock.
     */
    public function store(StoreStockRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $stock = $this->stockService->createStock($validated);

            return ResponseProtocol::success(
                new StockResource($stock),
                'Stock created successfully',
                201
            );
        } catch (\Exception $e) {
            return ResponseProtocol::failed(
                ['error' => $e->getMessage()],
                'Failed to create stock',
                500
            );
        }
    }

    /**
     * Display the specified stock.
     */
    public function show(int $id): JsonResponse
    {
        $stock = $this->stockService->getStock($id);

        if (!$stock) {
            return ResponseProtocol::failed(
                null,
                'Stock not found',
                404
            );
        }

        return ResponseProtocol::success(
            new StockResource($stock),
            'Stock retrieved successfully',
            200
        );
    }

    /**
     * Update the specified stock.
     */
    public function update(UpdateStockRequest $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validated();
            $stock = $this->stockService->updateStock($id, $validated);

            return ResponseProtocol::success(
                new StockResource($stock),
                'Stock updated successfully',
                200
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ResponseProtocol::failed(
                null,
                'Stock not found',
                404
            );
        } catch (\Exception $e) {
            return ResponseProtocol::failed(
                ['error' => $e->getMessage()],
                'Failed to update stock',
                500
            );
        }
    }

    /**
     * Remove the specified stock.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $deleted = $this->stockService->deleteStock($id);

            return ResponseProtocol::success(
                null,
                'Stock deleted successfully',
                200
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ResponseProtocol::failed(
                null,
                'Stock not found',
                404
            );
        } catch (\Exception $e) {
            return ResponseProtocol::failed(
                ['error' => $e->getMessage()],
                'Failed to delete stock',
                500
            );
        }
    }

    /**
     * Check stock availability.
     */
    public function checkAvailability(Request $request): JsonResponse
    {
        $request->validate([
            'stock_id' => 'required|integer|exists:stocks,id',
            'quantity' => 'required|integer|min:1',
        ]);

        try {
            $result = $this->stockService->checkAvailability(
                $request->input('stock_id'),
                $request->input('quantity')
            );

            return ResponseProtocol::success(
                $result,
                $result['available'] ? 'Stock is available' : 'Stock is not available',
                200
            );
        } catch (\Exception $e) {
            return ResponseProtocol::failed(
                ['error' => $e->getMessage()],
                'Failed to check availability',
                500
            );
        }
    }

    /**
     * Search stocks by query.
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->input('search', '');
        $perPage = $request->input('per_page', $this->PER_PAGE);

        if (empty($query)) {
            return ResponseProtocol::failed(
                null,
                'Search query is required',
                400
            );
        }

        $stocks = \Arkenstone\Core\ECommerce\Stock\Models\Stock::query()
            ->with(['product', 'supplier', 'variationOptions', 'reservations', 'image'])
            ->search($query)
            ->paginate($perPage);

        return ResponseProtocol::success(
            StockResource::collection($stocks),
            'Search results retrieved successfully',
            200
        );
    }

    /**
     * Get low stock items.
     */
    public function lowStock(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', $this->PER_PAGE);

        $stocks = \Arkenstone\Core\ECommerce\Stock\Models\Stock::query()
            ->with(['product', 'supplier', 'variationOptions', 'reservations', 'image'])
            ->active()
            ->lowStock()
            ->paginate($perPage);

        return ResponseProtocol::success(
            StockResource::collection($stocks),
            'Low stock items retrieved successfully',
            200
        );
    }

    /**
     * Get out of stock items.
     */
    public function outOfStock(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', $this->PER_PAGE);

        $stocks = \Arkenstone\Core\ECommerce\Stock\Models\Stock::query()
            ->with(['product', 'supplier', 'variationOptions', 'reservations', 'image'])
            ->active()
            ->outOfStock()
            ->paginate($perPage);

        return ResponseProtocol::success(
            StockResource::collection($stocks),
            'Out of stock items retrieved successfully',
            200
        );
    }

    /**
     * Adjust stock quantity.
     */
    public function adjustQuantity(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'quantity' => 'required|integer',
            'reason' => 'nullable|string|max:255',
        ]);

        try {
            $adjusted = $this->stockService->adjustQuantity(
                $id,
                $request->input('quantity'),
                $request->input('reason', 'manual adjustment')
            );

            if (!$adjusted) {
                return ResponseProtocol::failed(
                    null,
                    'Failed to adjust quantity',
                    400
                );
            }

            $stock = $this->stockService->getStock($id);

            return ResponseProtocol::success(
                new StockResource($stock),
                'Stock quantity adjusted successfully',
                200
            );
        } catch (\Exception $e) {
            return ResponseProtocol::failed(
                ['error' => $e->getMessage()],
                'Failed to adjust quantity',
                500
            );
        }
    }
}

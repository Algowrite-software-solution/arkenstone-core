<?php

namespace Arkenstone\Core\ECommerce\Stock\Http\Controllers\API\V1;

use Arkenstone\Core\ECommerce\Contracts\SupplierServiceInterface;
use Arkenstone\Core\ECommerce\Stock\Http\Requests\StoreSupplierRequest;
use Arkenstone\Core\ECommerce\Stock\Http\Requests\UpdateSupplierRequest;
use Arkenstone\Core\ECommerce\Stock\Http\Resources\SupplierResource;
use Arkenstone\Core\Helpers\ResponseProtocol;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class SupplierController extends Controller
{
    public int $PER_PAGE;
    public string $ORDER;

    public function __construct(private SupplierServiceInterface $supplierService)
    {
        $this->PER_PAGE = config('arkenstone.api_defaults.per_page', 100000000);
        $this->ORDER = config('arkenstone.api_defaults.order', 'desc');
    }

    /**
     * Display a listing of suppliers.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', $this->PER_PAGE);
        $filters = $request->only(['status', 'active']);

        $query = \Arkenstone\Core\ECommerce\Stock\Models\Supplier::query();

        // Apply filters
        if (isset($filters['status'])) {
            $query->byStatus($filters['status']);
        }
        if (isset($filters['active']) && $filters['active']) {
            $query->active();
        }

        $suppliers = $query->paginate($perPage);

        return ResponseProtocol::success(
            SupplierResource::collection($suppliers),
            'Suppliers retrieved successfully',
            200
        );
    }

    /**
     * Store a newly created supplier.
     */
    public function store(StoreSupplierRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $supplier = $this->supplierService->createSupplier($validated);

            return ResponseProtocol::success(
                new SupplierResource($supplier),
                'Supplier created successfully',
                201
            );
        } catch (\Exception $e) {
            return ResponseProtocol::failed(
                ['error' => $e->getMessage()],
                'Failed to create supplier',
                500
            );
        }
    }

    /**
     * Display the specified supplier.
     */
    public function show(int $id): JsonResponse
    {
        $supplier = $this->supplierService->getSupplier($id);

        if (!$supplier) {
            return ResponseProtocol::failed(
                null,
                'Supplier not found',
                404
            );
        }

        return ResponseProtocol::success(
            new SupplierResource($supplier),
            'Supplier retrieved successfully',
            200
        );
    }

    /**
     * Update the specified supplier.
     */
    public function update(UpdateSupplierRequest $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validated();
            $supplier = $this->supplierService->updateSupplier($id, $validated);

            return ResponseProtocol::success(
                new SupplierResource($supplier),
                'Supplier updated successfully',
                200
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ResponseProtocol::failed(
                null,
                'Supplier not found',
                404
            );
        } catch (\Exception $e) {
            return ResponseProtocol::failed(
                ['error' => $e->getMessage()],
                'Failed to update supplier',
                500
            );
        }
    }

    /**
     * Remove the specified supplier.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $deleted = $this->supplierService->deleteSupplier($id);

            return ResponseProtocol::success(
                null,
                'Supplier deleted successfully',
                200
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ResponseProtocol::failed(
                null,
                'Supplier not found',
                404
            );
        } catch (\Exception $e) {
            return ResponseProtocol::failed(
                ['error' => $e->getMessage()],
                'Failed to delete supplier',
                500
            );
        }
    }

    /**
     * Search suppliers by query.
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

        $suppliers = \Arkenstone\Core\ECommerce\Stock\Models\Supplier::query()
            ->search($query)
            ->paginate($perPage);

        return ResponseProtocol::success(
            SupplierResource::collection($suppliers),
            'Search results retrieved successfully',
            200
        );
    }
}

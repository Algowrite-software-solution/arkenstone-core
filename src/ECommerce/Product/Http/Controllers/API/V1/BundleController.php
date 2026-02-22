<?php

namespace Arkenstone\Core\ECommerce\Product\Http\Controllers\API\V1;

use Illuminate\Routing\Controller;
use Arkenstone\Core\ECommerce\Product\Http\Requests\StoreBundleRequest;
use Arkenstone\Core\ECommerce\Product\Http\Requests\UpdateBundleRequest;
use Arkenstone\Core\ECommerce\Product\Http\Resources\BundleResource;
use Arkenstone\Core\ECommerce\Product\Models\Bundle;
use Arkenstone\Core\ECommerce\Product\Services\BundleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BundleController extends Controller
{
    protected BundleService $bundleService;

    public function __construct(BundleService $bundleService)
    {
        $this->bundleService = $bundleService;
    }

    public function index(): AnonymousResourceCollection
    {
        $bundles = $this->bundleService->getAll();
        return BundleResource::collection($bundles);
    }

    public function store(StoreBundleRequest $request): BundleResource
    {
        $bundle = $this->bundleService->create($request->validated());
        return new BundleResource($bundle);
    }

    public function show(int $id): BundleResource
    {
        return new BundleResource($this->bundleService->get($id));
    }

    public function update(UpdateBundleRequest $request, int $id): BundleResource
    {
        $bundle = $this->bundleService->update($id, $request->validated());
        return new BundleResource($bundle);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->bundleService->delete($id);
        return response()->json(['message' => 'Bundle deleted successfully']);
    }
}

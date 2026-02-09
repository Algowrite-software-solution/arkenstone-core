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
use Illuminate\Support\Facades\Log;

class BundleController extends Controller
{
    protected BundleService $bundleService;

    public function __construct(BundleService $bundleService)
    {
        $this->bundleService = $bundleService;
    }

    public function index(): AnonymousResourceCollection
    {
        Log::info("data");
        return BundleResource::collection(Bundle::with('products')->paginate(10));
    }

    public function store(StoreBundleRequest $request): BundleResource
    {
        $bundle = $this->bundleService->create($request->validated());
        return new BundleResource($bundle);
    }

    public function show(Bundle $bundle): BundleResource
    {
        return new BundleResource($bundle->load('items.product'));
    }

    public function update(UpdateBundleRequest $request, Bundle $bundle): BundleResource
    {
        $bundle = $this->bundleService->update($bundle, $request->validated());
        return new BundleResource($bundle);
    }

    public function destroy(Bundle $bundle): JsonResponse
    {
        $bundle->delete();
        return response()->json(['message' => 'Bundle deleted successfully']);
    }
}

<?php

namespace Arkenstone\Core\ECommerce\Product\Services;

use Arkenstone\Core\ECommerce\Contracts\BrandServiceInterface;
use Arkenstone\Core\ECommerce\Product\Models\Brand;
use Illuminate\Database\Eloquent\Collection;

class BrandService implements BrandServiceInterface
{
    public function getName(): string
    {
        return "Brand Service";
    }

    public function getAllBrands(): Collection
    {
        return Brand::all();
    }

    public function getBrandById(int $id): ?Brand
    {
        return Brand::find($id);
    }

    public function createBrand(array $data): Brand
    {
        return Brand::create($data);
    }

    public function updateBrand(int $id, array $data): bool
    {
        $brand = Brand::find($id);

        if (!$brand) {
            return false;
        }

        return $brand->update($data);
    }

    public function deleteBrand(int $id): bool
    {
        $brand = Brand::find($id);

        if (!$brand) {
            return false;
        }

        return $brand->delete();
    }

    public function getActiveBrands(): Collection
    {
        return Brand::where('is_active', true)->get();
    }
}

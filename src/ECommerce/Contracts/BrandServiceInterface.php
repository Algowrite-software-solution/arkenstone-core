<?php

namespace Arkenstone\Core\ECommerce\Contracts;

interface BrandServiceInterface
{
    public function getAllBrands();
    public function getBrandById(int $id);
    public function createBrand(array $data);
    public function updateBrand(int $id, array $data);
    public function deleteBrand(int $id);
    public function getActiveBrands();
}

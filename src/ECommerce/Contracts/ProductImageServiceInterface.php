<?php

namespace Arkenstone\Core\ECommerce\Contracts;

interface ProductImageServiceInterface   
{
    public function getImagesByProductId(int $productId);
    public function getImageById(int $id);
    public function createImage(array $data);
    public function updateImage(int $id, array $data);
    public function deleteImage(int $id);
    public function setPrimaryImage(int $productId, int $imageId);
    public function getPrimaryImage(int $productId);
}

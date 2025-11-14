<?php

namespace Arkenstone\Core\ECommerce\Product\Services;

use Arkenstone\Core\ECommerce\Contracts\Service;

class ProductService implements Service
{

    public function getName(): string
    {
        return "Product Service";
    }

   public function getProducts($filters)
   {
      return [1,2];
   }

   public function getAllProducts()
   {
      return [1,2,3];
   }

   public function getProductById($id)
   {
      return [1];
      
   }
}

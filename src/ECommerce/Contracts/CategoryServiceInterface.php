<?php

namespace Arkenstone\Core\ECommerce\Contracts;

interface CategoryServiceInterface 
{
    public function getAllCategories();
    public function getCategoryById(int $id);
    public function createCategory(array $data);
    public function updateCategory(int $id, array $data);
    public function deleteCategory(int $id);
    public function getActiveCategories();
    public function getCategoryChildren(int $id);
    public function getRootCategories();
}

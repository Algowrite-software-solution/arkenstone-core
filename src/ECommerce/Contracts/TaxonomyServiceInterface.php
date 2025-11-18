<?php

namespace Arkenstone\Core\ECommerce\Contracts;

interface TaxonomyServiceInterface
{
    public function getAllTaxonomies();
    public function getTaxonomyById(int $id);
    public function createTaxonomy(array $data);
    public function updateTaxonomy(int $id, array $data);
    public function deleteTaxonomy(int $id);
    public function getActiveTaxonomies();
    public function getTaxonomiesByType(int $typeId);
}

<?php

namespace Arkenstone\Core\ECommerce\Product\Helper;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class ProductFilter
{
    /**
     * The Eloquent query builder instance.
     * @var Builder
     */
    protected Builder $query;

    /**
     * The array of filters from the HTTP request.
     * @var array
     */
    protected array $filters;

    /**
     * @param Builder $query The query to begin with.
     * @param array $filters The filters to apply.
     */
    public function __construct(Builder $query, array $filters)
    {
        $this->query = $query;
        $this->filters = $filters;
    }

    /**
     * Applies all registered filters from the filters array.
     */
    public function apply(): Builder
    {

        // default is active
        if (!empty($this->filters['get_inactive'])) {
            if (!isset($this->filters['is_active'])) {
                $this->filters['is_active'] = true;
            }
        }
        

        // Loop through all the provided filters
        foreach ($this->filters as $name => $value) {
            // Check if a corresponding method exists for the filter and the value is not empty
            if (method_exists($this, $name) && !empty($value)) {
                // Call the method, e.g., $this->category('laptops')
                $this->$name($value);
            }
        }

        return $this->query;
    }

    /**
     *  |-----------------------------------------------------------------------------|
     *  |  Individual Filter Methods                                                  |
     *  |-----------------------------------------------------------------------------|
     */


    /**
     * Handles the 'is_active' filter.
     */
    protected function is_active(string $value): void
    {
        Log::info("is_active", [$value]);
        // convert to boolean either it is 0, 1, true, false or somerandome string
        $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
        $this->query->where('is_active', $value);
    }
    

    
    /**
     * Handles the 'name' filter.
     * Maps to the scopeFilterByName() in the Product model.
     */
    protected function name(string $value): void
    {
        if (!empty($value)) {
            $this->query->filterByName($value);
        }
    }

    /**
     * Handles the 'min_price' filter.
     * Maps to the scopeMinPrice() in the Product model.
     */
    protected function min_price(float $value): void
    {

        $this->query->minPrice($value);
    }

    /**
     * Handles the 'max_price' filter.
     * Maps to the scopeMaxPrice() in the Product model.
     */
    protected function max_price(float $value): void
    {
        $this->query->maxPrice($value);
    }

    /**
     * Handles the 'categories' filter.
     * Maps to the scopeByCategory() in the Product model.
     */
    protected function categories(array $value): void
    {
        $this->query->byCategories($value);
    }

    /**
     * Handles the 'all_categories' filter.
     * Maps to the scopeByAllCategories() in the Product model.
     */
    protected function all_categories(array $value): void
    {
        $this->query->byAllCategories($value);
    }

    /**
     * Handles the 'brand' filter.
     * Maps to the scopeByBrand() in the Product model.
     */
    protected function brand(int $value): void
    {
        $this->query->byBrand($value);
    }

    /**
     * Handles the 'brand_id' filter.
     * Maps to the scopeByBrand() in the Product model.
     */
    protected function brand_id(int $value): void
    {
        $this->query->byBrand($value);
    }

}
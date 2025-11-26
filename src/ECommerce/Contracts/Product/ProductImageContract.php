<?php

namespace Arkenstone\Core\ECommerce\Contracts\Product;

interface ProductImageContract
{
    /**
     * Delete the model from the database.
     */
    public function delete();

    /**
     * Update the model in the database.
     */
    public function update(array $attributes = [], array $options = []);

    /**
     * Get the public URL of the image.
     *
     * @return string
     */
    public function getUrl(): string;
}
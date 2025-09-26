<?php

namespace App\Services\Admin;
use App\Models\Product;
use App\Services\Contracts\Admin\ProductServiceInterface;

class ProductService implements ProductServiceInterface
{
    /**
     * Create a new class instance.
     */
    public function store(array $data): Product
    {
        $product = Product::create($data);
        if (isset($data['categories'])) {
            $product->categories()->sync($data['categories']);
        }

        return $product->load('categories:id,name');
    }

    public function update(Product $product, array $data): Product
    {
        $product->update($data);
        if (isset($data['categories'])) {
            $product->categories()->syncWithoutDetaching($data['categories']);
        }

        return $product->load('categories:id,name');
    }

    public function delete(Product $product): void
    {
        $product->categories()->detach();
        $product->delete();
    }

}

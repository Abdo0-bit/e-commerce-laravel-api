<?php

namespace App\Services\Client;

use App\Models\Product;
use App\Services\Contracts\Client\ProductServiceInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductService implements ProductServiceInterface
{
    public function index(array $filters = []):LengthAwarePaginator
    {
        $query = Product::query()->with('categories');

        if (isset($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (isset($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }
        if (isset($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }

        if (isset($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        return $query->paginate($filters['per_page'] ?? 10);
    }

    public function show(Product $product): Product
    {
        return $product->load('categories');
    }
}

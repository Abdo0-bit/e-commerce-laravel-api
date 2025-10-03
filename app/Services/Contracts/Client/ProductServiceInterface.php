<?php

namespace App\Services\Contracts\Client;

use App\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;

interface ProductServiceInterface
{
    public function index(array $filters = []):LengthAwarePaginator;

    public function show(Product $product):Product;
}

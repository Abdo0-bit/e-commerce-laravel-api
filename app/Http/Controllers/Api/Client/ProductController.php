<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\Contracts\Client\ProductServiceInterface;
use Illuminate\Http\Request;

class ProductController extends Controller
{

    public function __construct(private ProductServiceInterface $productService) {}
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = $this->productService->index(request()->all());
        return response()->json($products);
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        $product = $this->productService->show($product);
        return response()->json($product);
    }


}

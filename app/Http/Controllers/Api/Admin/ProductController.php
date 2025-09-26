<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Resources\Admin\ProductResource;
use App\Services\Contracts\Admin\ProductServiceInterface;

class ProductController extends Controller
{
    
    public function __construct(private ProductServiceInterface $productService) {}
    
    
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::with('categories:name')->paginate(10);
        return ProductResource::collection($products);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request)
    {
        $product = $this->productService->store($request->validated());
        return new ProductResource($product);
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        $product->load('categories:id,name');
        return new ProductResource($product);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request , Product $product)
    {
        $product = $this->productService->update($product, $request->validated());
        return new ProductResource($product);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $this->productService->delete($product);
        return response()->noContent();
    }
}

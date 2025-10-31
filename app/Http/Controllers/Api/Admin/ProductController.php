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
     * @OA\Get(
     *     path="/api/admin/products",
     *     tags={"Admin Products"},
     *     summary="Get all products (Admin)",
     *     description="Retrieve a paginated list of all products for admin management",
     *     security={{"sanctum": {}}},
     *     
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number for pagination",
     *         @OA\Schema(type="integer", minimum=1, example=1)
     *     ),
     *     
     *     @OA\Response(
     *         response=200,
     *         description="Products retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="current_page", type="integer", example=1),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Product")
     *             ),
     *             @OA\Property(property="first_page_url", type="string", example="http://localhost/api/admin/products?page=1"),
     *             @OA\Property(property="from", type="integer", example=1),
     *             @OA\Property(property="last_page", type="integer", example=7),
     *             @OA\Property(property="last_page_url", type="string", example="http://localhost/api/admin/products?page=7"),
     *             @OA\Property(property="per_page", type="integer", example=10),
     *             @OA\Property(property="total", type="integer", example=67)
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::with('categories:name')->paginate(10);
        return ProductResource::collection($products);
    }

    /**
     * @OA\Post(
     *     path="/api/admin/products",
     *     tags={"Admin Products"},
     *     summary="Create new product",
     *     description="Create a new product with all required information",
     *     security={{"sanctum": {}}},
     *     
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"name", "price", "quantity", "category_id"},
     *                 @OA\Property(property="name", type="string", example="iPhone 15 Pro", description="Product name"),
     *                 @OA\Property(property="description", type="string", example="Latest iPhone with Pro features", description="Product description"),
     *                 @OA\Property(property="price", type="number", format="decimal", example=999.00, description="Product price"),
     *                 @OA\Property(property="quantity", type="integer", example=50, description="Stock quantity"),
     *                 @OA\Property(property="category_id", type="integer", example=1, description="Category ID"),
     *                 @OA\Property(property="status", type="string", enum={"active", "inactive"}, example="active", description="Product status"),
     *                 @OA\Property(property="featured", type="boolean", example=true, description="Whether product is featured"),
     *                 @OA\Property(property="image", type="string", format="binary", description="Product image file")
     *             )
     *         )
     *     ),
     *     
     *     @OA\Response(
     *         response=201,
     *         description="Product created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Product")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="name",
     *                     type="array",
     *                     @OA\Items(type="string", example="The name field is required.")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request)
    {
        $product = $this->productService->store($request->validated());
        return new ProductResource($product);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/products/{product}",
     *     tags={"Admin Products"},
     *     summary="Get single product (Admin)",
     *     description="Retrieve detailed information about a specific product for admin management",
     *     security={{"sanctum": {}}},
     *     
     *     @OA\Parameter(
     *         name="product",
     *         in="path",
     *         required=true,
     *         description="Product ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     
     *     @OA\Response(
     *         response=200,
     *         description="Product retrieved successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Product")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No query results for model [App\\Models\\Product] 999")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        $product->load('categories:id,name');
        return new ProductResource($product);
    }

    /**
     * @OA\Put(
     *     path="/api/admin/products/{product}",
     *     tags={"Admin Products"},
     *     summary="Update product",
     *     description="Update an existing product with new information",
     *     security={{"sanctum": {}}},
     *     
     *     @OA\Parameter(
     *         name="product",
     *         in="path",
     *         required=true,
     *         description="Product ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="name", type="string", example="iPhone 15 Pro Max", description="Product name"),
     *                 @OA\Property(property="description", type="string", example="Updated iPhone with enhanced features", description="Product description"),
     *                 @OA\Property(property="price", type="number", format="decimal", example=1199.00, description="Product price"),
     *                 @OA\Property(property="quantity", type="integer", example=25, description="Stock quantity"),
     *                 @OA\Property(property="category_id", type="integer", example=1, description="Category ID"),
     *                 @OA\Property(property="status", type="string", enum={"active", "inactive"}, example="active", description="Product status"),
     *                 @OA\Property(property="featured", type="boolean", example=false, description="Whether product is featured"),
     *                 @OA\Property(property="image", type="string", format="binary", description="New product image file (optional)")
     *             )
     *         )
     *     ),
     *     
     *     @OA\Response(
     *         response=200,
     *         description="Product updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Product")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No query results for model [App\\Models\\Product] 999")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="price",
     *                     type="array",
     *                     @OA\Items(type="string", example="The price must be a number.")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request , Product $product)
    {
        $product = $this->productService->update($product, $request->validated());
        return new ProductResource($product);
    }

    /**
     * @OA\Delete(
     *     path="/api/admin/products/{product}",
     *     tags={"Admin Products"},
     *     summary="Delete product",
     *     description="Delete a product permanently from the system",
     *     security={{"sanctum": {}}},
     *     
     *     @OA\Parameter(
     *         name="product",
     *         in="path",
     *         required=true,
     *         description="Product ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     
     *     @OA\Response(
     *         response=204,
     *         description="Product deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No query results for model [App\\Models\\Product] 999")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $this->productService->delete($product);
        return response()->noContent();
    }
}

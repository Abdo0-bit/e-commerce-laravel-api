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
     * @OA\Get(
     *     path="/api/client/products",
     *     tags={"Client Products"},
     *     summary="Get all products",
     *     description="Retrieve a paginated list of all active products available for purchase. Supports filtering by category, search, and sorting.",
     *     
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number for pagination",
     *         @OA\Schema(type="integer", minimum=1, example=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page (max 100)",
     *         @OA\Schema(type="integer", minimum=1, maximum=100, example=15)
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search term for product name or description",
     *         @OA\Schema(type="string", example="iPhone")
     *     ),
     *     @OA\Parameter(
     *         name="category",
     *         in="query",
     *         description="Filter by category ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="sort",
     *         in="query", 
     *         description="Sort field",
     *         @OA\Schema(type="string", enum={"name", "price", "created_at"}, example="name")
     *     ),
     *     @OA\Parameter(
     *         name="direction",
     *         in="query",
     *         description="Sort direction",
     *         @OA\Schema(type="string", enum={"asc", "desc"}, example="asc")
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
     *             @OA\Property(property="first_page_url", type="string", example="http://localhost/api/client/products?page=1"),
     *             @OA\Property(property="from", type="integer", example=1),
     *             @OA\Property(property="last_page", type="integer", example=5),
     *             @OA\Property(property="last_page_url", type="string", example="http://localhost/api/client/products?page=5"),
     *             @OA\Property(property="next_page_url", type="string", example="http://localhost/api/client/products?page=2"),
     *             @OA\Property(property="path", type="string", example="http://localhost/api/client/products"),
     *             @OA\Property(property="per_page", type="integer", example=15),
     *             @OA\Property(property="prev_page_url", type="string", example=null),
     *             @OA\Property(property="to", type="integer", example=15),
     *             @OA\Property(property="total", type="integer", example=67)
     *         )
     *     )
     * )
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = $this->productService->index(request()->all());
        return response()->json($products);
    }

    /**
     * @OA\Get(
     *     path="/api/client/products/{product}",
     *     tags={"Client Products"},
     *     summary="Get single product",
     *     description="Retrieve detailed information about a specific product including related products and category information",
     *     
     *     @OA\Parameter(
     *         name="product",
     *         in="path",
     *         required=true,
     *         description="Product ID or slug",
     *         @OA\Schema(type="string", example="1")
     *     ),
     *     
     *     @OA\Response(
     *         response=200,
     *         description="Product retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(
     *                 property="data", 
     *                 ref="#/components/schemas/Product"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No query results for model [App\\Models\\Product] 999")
     *         )
     *     )
     * )
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        $product = $this->productService->show($product);
        return response()->json($product);
    }


}

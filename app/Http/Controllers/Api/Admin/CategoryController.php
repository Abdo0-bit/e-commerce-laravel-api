<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\Admin\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Services\Contracts\Admin\CategoryServiceInterface;

class CategoryController extends Controller
{

    public function __construct(private CategoryServiceInterface $categoryService) {}
    

    /**
     * @OA\Get(
     *     path="/api/admin/categories",
     *     tags={"Admin Categories"},
     *     summary="Get all categories (Admin)",
     *     description="Retrieve a list of all categories for admin management",
     *     security={{"sanctum": {}}},
     *     
     *     @OA\Response(
     *         response=200,
     *         description="Categories retrieved successfully",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Category")
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
        $categories = $this->categoryService->index();
        return CategoryResource::collection($categories);
    }

    /**
     * @OA\Post(
     *     path="/api/admin/categories",
     *     tags={"Admin Categories"},
     *     summary="Create new category",
     *     description="Create a new product category",
     *     security={{"sanctum": {}}},
     *     
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"name"},
     *                 @OA\Property(property="name", type="string", example="Electronics", description="Category name"),
     *                 @OA\Property(property="description", type="string", example="Electronic devices and gadgets", description="Category description"),
     *                 @OA\Property(property="status", type="string", enum={"active", "inactive"}, example="active", description="Category status"),
     *                 @OA\Property(property="image", type="string", format="binary", description="Category image file")
     *             )
     *         )
     *     ),
     *     
     *     @OA\Response(
     *         response=201,
     *         description="Category created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Category")
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
    public function store(StoreCategoryRequest $request)
    {
        $category = $this->categoryService->store($request->validated());
        return new CategoryResource($category)->response()->setStatusCode(201);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/categories/{category}",
     *     tags={"Admin Categories"},
     *     summary="Get single category (Admin)",
     *     description="Retrieve detailed information about a specific category including associated products",
     *     security={{"sanctum": {}}},
     *     
     *     @OA\Parameter(
     *         name="category",
     *         in="path",
     *         required=true,
     *         description="Category ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     
     *     @OA\Response(
     *         response=200,
     *         description="Category retrieved successfully",
     *         @OA\JsonContent(
     *             allOf={
     *                 @OA\Schema(ref="#/components/schemas/Category"),
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="products",
     *                         type="array",
     *                         @OA\Items(
     *                             type="object",
     *                             @OA\Property(property="id", type="integer", example=1),
     *                             @OA\Property(property="name", type="string", example="iPhone 15 Pro"),
     *                             @OA\Property(property="price", type="string", example="999.00")
     *                         ),
     *                         description="Products in this category"
     *                     )
     *                 )
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No query results for model [App\\Models\\Category] 999")
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
    public function show(Category $category)
    {
        $category->load('products:id,name,price');
        return new CategoryResource($category);
    }

    /**
     * @OA\Put(
     *     path="/api/admin/categories/{category}",
     *     tags={"Admin Categories"},
     *     summary="Update category",
     *     description="Update an existing category with new information",
     *     security={{"sanctum": {}}},
     *     
     *     @OA\Parameter(
     *         name="category",
     *         in="path",
     *         required=true,
     *         description="Category ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="name", type="string", example="Electronics & Gadgets", description="Updated category name"),
     *                 @OA\Property(property="description", type="string", example="Updated description for electronic devices", description="Updated category description"),
     *                 @OA\Property(property="status", type="string", enum={"active", "inactive"}, example="inactive", description="Updated category status"),
     *                 @OA\Property(property="image", type="string", format="binary", description="New category image file (optional)")
     *             )
     *         )
     *     ),
     *     
     *     @OA\Response(
     *         response=200,
     *         description="Category updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Category")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No query results for model [App\\Models\\Category] 999")
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
     *                     property="name",
     *                     type="array",
     *                     @OA\Items(type="string", example="The name has already been taken.")
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
    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $category = $this->categoryService->update($category, $request->validated());
        return new CategoryResource($category);
    }

    /**
     * @OA\Delete(
     *     path="/api/admin/categories/{category}",
     *     tags={"Admin Categories"},
     *     summary="Delete category",
     *     description="Delete a category permanently from the system. Note that this may affect associated products.",
     *     security={{"sanctum": {}}},
     *     
     *     @OA\Parameter(
     *         name="category",
     *         in="path",
     *         required=true,
     *         description="Category ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     
     *     @OA\Response(
     *         response=204,
     *         description="Category deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No query results for model [App\\Models\\Category] 999")
     *         )
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Cannot delete category with associated products",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Cannot delete category with associated products")
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
    public function destroy(Category $category)
    {
        $this->categoryService->delete($category);
        return response()->noContent();
    }
}

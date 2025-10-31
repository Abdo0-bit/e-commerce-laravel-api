<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Contracts\Client\CartServiceInterface;
use App\Http\Resources\Cart\CartResource;
use App\Http\Requests\AddToCartRequest;
use App\Http\Requests\UpdateCartRequest;
use App\Models\Product;
use App\Events\CartUpdated;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{

    public function __construct(private CartServiceInterface $cartService) {}

    /**
     * @OA\Get(
     *     path="/api/cart",
     *     tags={"Cart"},
     *     summary="Get cart contents",
     *     description="Retrieve all items in the current user's cart. For authenticated users, returns the persistent cart. For guests, returns session-based cart.",
     *     security={{"sanctum": {}}},
     *     
     *     @OA\Response(
     *         response=200,
     *         description="Cart retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="items",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="product_id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="iPhone 15 Pro"),
     *                         @OA\Property(property="price", type="string", example="999.00"),
     *                         @OA\Property(property="quantity", type="integer", example=2),
     *                         @OA\Property(property="subtotal", type="string", example="1998.00"),
     *                         @OA\Property(property="image", type="string", example="https://example.com/iphone.jpg")
     *                     )
     *                 ),
     *                 @OA\Property(property="total_items", type="integer", example=3),
     *                 @OA\Property(property="total_amount", type="string", example="2497.00")
     *             ),
     *             @OA\Examples(
     *                 example="cart_with_items",
     *                 summary="Cart with items",
     *                 value={
     *                     "status": "success",
     *                     "data": {
     *                         "items": {
     *                             {
     *                                 "product_id": 1,
     *                                 "name": "iPhone 15 Pro",
     *                                 "price": "999.00",
     *                                 "quantity": 2,
     *                                 "subtotal": "1998.00",
     *                                 "image": "https://example.com/iphone.jpg"
     *                             },
     *                             {
     *                                 "product_id": 2,
     *                                 "name": "MacBook Pro",
     *                                 "price": "1999.00",
     *                                 "quantity": 1,
     *                                 "subtotal": "1999.00",
     *                                 "image": "https://example.com/macbook.jpg"
     *                             }
     *                         },
     *                         "total_items": 3,
     *                         "total_amount": "3997.00"
     *                     }
     *                 }
     *             ),
     *             @OA\Examples(
     *                 example="empty_cart",
     *                 summary="Empty cart",
     *                 value={
     *                     "status": "success",
     *                     "data": {
     *                         "items": {},
     *                         "total_items": 0,
     *                         "total_amount": "0.00"
     *                     }
     *                 }
     *             )
     *         )
     *     )
     * )
     * Display a listing of the resource.
     */
    public function index()
    {
        $cart = $this->cartService->getCart();
        
        return response()->json([
            'status' => 'success',
            'data' => new CartResource($cart),
        ]);
            
    }

    /**
     * @OA\Post(
     *     path="/api/cart",
     *     tags={"Cart"},
     *     summary="Add product to cart",
     *     description="Add a product to the cart with specified quantity. If product already exists, the quantities will be combined.",
     *     security={{"sanctum": {}}},
     *     
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"product_id"},
     *             @OA\Property(property="product_id", type="integer", example=1, description="ID of the product to add"),
     *             @OA\Property(property="quantity", type="integer", example=2, minimum=1, description="Quantity to add (defaults to 1)")
     *         )
     *     ),
     *     
     *     @OA\Response(
     *         response=200,
     *         description="Product added to cart successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Product added to cart successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 ref="#/components/schemas/CartResource"
     *             )
     *         )
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
     *                     property="product_id",
     *                     type="array",
     *                     @OA\Items(type="string", example="The product id field is required.")
     *                 )
     *             )
     *         )
     *     )
     * )
     * Store a newly created resource in storage.
     */
    public function store(AddToCartRequest $request)
    {
        $product = Product::findOrFail($request->product_id);
        $this->cartService->add($product, $request->quantity ?? 1);
        $cart = $this->cartService->getCart();

        // Broadcast cart update
        $this->broadcastCartUpdate($cart);

        return response()->json([
            'status' => 'success',
            'message' => 'Product added to cart successfully.',
            'data' => new CartResource($cart),
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/cart/{product}",
     *     tags={"Cart"},
     *     summary="Update product quantity in cart",
     *     description="Update the quantity of a specific product in the cart. Set quantity to 0 to remove the item.",
     *     security={{"sanctum": {}}},
     *     
     *     @OA\Parameter(
     *         name="product",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Product ID",
     *         example=1
     *     ),
     *     
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"quantity"},
     *             @OA\Property(property="quantity", type="integer", example=3, minimum=0, description="New quantity (0 to remove)")
     *         )
     *     ),
     *     
     *     @OA\Response(
     *         response=200,
     *         description="Cart updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Cart updated successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 ref="#/components/schemas/CartResource"
     *             )
     *         )
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
     *                     property="quantity",
     *                     type="array",
     *                     @OA\Items(type="string", example="The quantity field is required.")
     *                 )
     *             )
     *         )
     *     )
     * )
     * Update the specified resource in storage.
     */
    public function update(UpdateCartRequest $request, Product $product)
    {
        $this->cartService->update($product, $request->quantity);
        $cart = $this->cartService->getCart();

        // Broadcast cart update
        $this->broadcastCartUpdate($cart);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Cart updated successfully.',
            'data' => new CartResource($cart),
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/cart/{product}",
     *     tags={"Cart"},
     *     summary="Remove product from cart",
     *     description="Remove a specific product completely from the cart",
     *     security={{"sanctum": {}}},
     *     
     *     @OA\Parameter(
     *         name="product",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Product ID",
     *         example=1
     *     ),
     *     
     *     @OA\Response(
     *         response=200,
     *         description="Product removed from cart successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Product removed from cart successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 ref="#/components/schemas/CartResource"
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
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $this->cartService->remove($product);
        $cart = $this->cartService->getCart();

        // Broadcast cart update
        $this->broadcastCartUpdate($cart);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Product removed from cart successfully.',
            'data' => new CartResource($cart),
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/cart/clear",
     *     tags={"Cart"},
     *     summary="Clear entire cart",
     *     description="Remove all items from the cart",
     *     security={{"sanctum": {}}},
     *     
     *     @OA\Response(
     *         response=200,
     *         description="Cart cleared successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Cart cleared successfully.")
     *         )
     *     )
     * )
     * Clear the cart.
     */
    public function clear()
    {
        $this->cartService->clear();
        $cart = $this->cartService->getCart();

        // Broadcast cart update
        $this->broadcastCartUpdate($cart);

        return response()->json([
            'status' => 'success',
            'message' => 'Cart cleared successfully.'
        ]);
    }

    /**
     * Broadcast cart updates to real-time listeners
     */
    private function broadcastCartUpdate(array $cart): void
    {
        $cartId = session()->getId();
        $userId = Auth::id();

        broadcast(new CartUpdated($cartId, $cart, $userId));
    }
}

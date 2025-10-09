<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Contracts\Client\CartServiceInterface;
use App\Http\Resources\Cart\CartResource;
use App\Http\Requests\AddToCartRequest;
use App\Http\Requests\UpdateCartRequest;
use App\Models\Product;

class CartController extends Controller
{

    public function __construct(private CartServiceInterface $cartService) {}

    /**
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
     * Store a newly created resource in storage.
     */
    public function store(AddToCartRequest $request)
    {
        $product = Product::findOrFail($request->product_id);
        $this->cartService->add($product, $request->quantity ?? 1);

        return response()->json([
            'status' => 'success',
            'message' => 'Product added to cart successfully.',
            'data' => new CartResource($this->cartService->getCart()),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCartRequest $request, Product $product)
    {
        $this->cartService->update($product, $request->quantity);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Cart updated successfully.',
            'data' => new CartResource($this->cartService->getCart()),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $this->cartService->remove($product);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Product removed from cart successfully.',
            'data' => new CartResource($this->cartService->getCart()),
        ]);
    }

    /**
     * Clear the cart.
     */
    public function clear()
    {
        $this->cartService->clear();

        return response()->json([
            'status' => 'success',
            'message' => 'Cart cleared successfully.'
        ]);
    }
    
}

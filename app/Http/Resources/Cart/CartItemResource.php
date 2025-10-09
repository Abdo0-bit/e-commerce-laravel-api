<?php

namespace App\Http\Resources\Cart;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Client\ProductResource;

class CartItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'product_id'=> $this['product_id'],
            'name'=> $this['name'],
            'price'=> $this['price'],
            'quantity'=> $this['quantity'],
            'total_price'=> $this['total_price'],
            'product_details' => new ProductResource($this['product']),
            
        ];
    }
}

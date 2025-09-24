<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'images' => $this->images,
            'is_active' => $this->is_active,
            'categories' => $this->whenLoaded('categories', fn()=> $this->categories->pluck('name')),
            'created_at' => $this->created_at,
        ];
    }
}

<?php

namespace App\Services\Contracts\Client;

use App\Models\Product;

interface CartServiceInterface
{
    public function add(Product $product, int $quantity = 1): bool;

    public function update(Product $product, int $quantity): void;

    public function remove(Product $product): void;

    public function clear(): void;

    public function getCart(): array;

    public function getTotal(): float;

    public function mergeGuestCart(int $userId): void;
    
    public function getTTL(): int;

    public function extendExpiration(): bool;

    public function exists(): bool;
    
}

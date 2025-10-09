<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\User;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// User-specific channels for order updates
Broadcast::channel('user.{userId}', function (User $user, int $userId) {
    return $user->id === $userId;
});

// Admin channels for order management
Broadcast::channel('admin.orders', function (User $user) {
    return $user->role === 'admin';
});

// Cart channels (public for guest users, private for authenticated users)
Broadcast::channel('cart.{cartId}', function ($user, string $cartId) {
    // Allow access to cart channel for both authenticated and guest users
    return true;
});

<?php

namespace App\Providers;

use App\Services\Admin\CategoryService;
use App\Services\Admin\ProductService as AdminProductService;
use App\Services\Client\ProductService as ClientProductService;
use App\Services\Contracts\Admin\CategoryServiceInterface;
use App\Services\Contracts\Admin\ProductServiceInterface as AdminProductServiceInterface;
use App\Services\Contracts\Client\CartServiceInterface;
use App\Services\Contracts\Client\ProductServiceInterface as ClientProductServiceInterface;
use App\Services\Contracts\PaymentServiceInterface;
use App\Services\StripePaymentService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Admin Services
        $this->app->bind(AdminProductServiceInterface::class, AdminProductService::class);
        $this->app->bind(CategoryServiceInterface::class, CategoryService::class);
        // Client Services
        $this->app->bind(ClientProductServiceInterface::class, ClientProductService::class);
        $this->app->bind(CartServiceInterface::class, \App\Services\Client\CartService::class);
        // Payment Services
        $this->app->bind(PaymentServiceInterface::class, StripePaymentService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}

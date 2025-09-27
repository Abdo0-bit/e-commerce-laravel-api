<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\Order;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\OrderSummaryResource;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        return response()->json([
            'products_count' =>Product::count(),
            'categories_count' => Category::count(),
            'orders_count' => Order::where('status', '!=', 'canceled')->count(),
            'pending_orders'=> Order::where('status', 'pending')->count(),
            'delivered_orders'=> Order::where('status', 'delivered')->count(),
            'total_revenue' => Order::where('payment_status', 'paid')->sum('total_amount'),
            'latest_orders' => OrderSummaryResource::collection(Order::with('user')->latest()->take(5)->get()),
        ]);
    }
}

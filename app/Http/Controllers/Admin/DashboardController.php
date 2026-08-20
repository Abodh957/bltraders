<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Color;
use App\Models\Order;
use App\Models\Product;
use App\Models\Shop;
use App\Models\Store;
use App\Models\SubCategory;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Admins are not customers — same rule the customer list uses.
        $adminIds = DB::table('model_has_roles')->where('role_id', 1)->pluck('model_id')->toArray();

        $customers = User::whereNotIn('id', $adminIds);

        $shops = [
            'total'    => Shop::count(),
            'pending'  => Shop::where('status', 'pending')->count(),
            'approved' => Shop::where('status', 'approved')->count(),
            'rejected' => Shop::where('status', 'rejected')->count(),
        ];

        $products = [
            'total'    => Product::count(),
            'active'   => Product::where('status', 1)->count(),
            'featured' => Product::where('is_featured', 1)->count(),
            'out'      => Product::where('stock', '<=', 0)->count(),
            'low'      => Product::where('stock', '>', 0)->where('stock', '<=', 5)->count(),
        ];

        // Cancelled orders never count towards revenue.
        $liveOrders = Order::where('status', '!=', 'cancelled');

        $orders = [
            'total'     => Order::count(),
            'pending'   => Order::where('status', 'pending')->count(),
            'delivered' => Order::where('status', 'delivered')->count(),
            'cancelled' => Order::where('status', 'cancelled')->count(),
            'revenue'   => (float) (clone $liveOrders)->sum('total_amount'),
            'this_month'=> (float) (clone $liveOrders)->whereMonth('created_at', now()->month)
                                ->whereYear('created_at', now()->year)->sum('total_amount'),
        ];

        $statusBreakdown = Order::select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')->pluck('total', 'status')->toArray();

        $catalogue = [
            'stores'         => Store::count(),
            'categories'     => Category::count(),
            'sub_categories' => SubCategory::count(),
            'brands'         => Brand::count(),
            'banners'        => Banner::count(),
            'colors'         => Color::count(),
        ];

        $recentOrders = Order::with(['store', 'user'])
            ->orderByDesc('created_at')->limit(5)->get();

        // 'id' must be qualified — the shops join makes a bare `id` ambiguous.
        $recentCustomers = User::whereNotIn('users.id', $adminIds)
            ->leftJoin('shops', 'shops.user_id', '=', 'users.id')
            ->select('users.id', 'users.phone_no', 'users.created_at',
                     'shops.shop_name', 'shops.city', 'shops.status as shop_status')
            ->orderByDesc('users.created_at')->limit(5)->get();

        // Best sellers — same rule as the trending API.
        $topProducts = Product::query()
            ->joinSub(
                DB::table('order_items')
                    ->join('orders', 'orders.id', '=', 'order_items.order_id')
                    ->whereNull('orders.deleted_at')
                    ->where('orders.status', '!=', 'cancelled')
                    ->whereNotNull('order_items.product_id')
                    ->groupBy('order_items.product_id')
                    ->select('order_items.product_id',
                             DB::raw('SUM(order_items.quantity) as total_sold'),
                             DB::raw('SUM(order_items.line_total) as revenue')),
                'sales',
                fn($j) => $j->on('sales.product_id', '=', 'products.id')
            )
            ->select('products.*', 'sales.total_sold', 'sales.revenue')
            ->orderByDesc('sales.total_sold')->limit(5)->get();

        $lowStock = Product::where('status', 1)->where('stock', '<=', 5)
            ->orderBy('stock')->limit(5)->get();

        $stats = [
            'customers'       => $customers->count(),
            'customers_month' => (clone $customers)->whereMonth('created_at', now()->month)
                                     ->whereYear('created_at', now()->year)->count(),
        ];

        return view('admin.layouts.dashboard', compact(
            'stats', 'shops', 'products', 'orders', 'statusBreakdown',
            'catalogue', 'recentOrders', 'recentCustomers', 'topProducts', 'lowStock'
        ));
    }
}

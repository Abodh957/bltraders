<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{

    /**
     * GET /api/products
     *
     * Filters (all optional):
     *   ?store_id=1
     *   ?category_id=1
     *   ?sub_category_id=1
     *   ?search=keyword        (searches name & sku)
     *   ?featured=1
     *   ?per_page=10           (default 15)
     *   ?page=1
     */
    public function index(Request $request)
    {
        $query = Product::with(['category', 'subCategory', 'primaryImage', 'colors', 'specifications'])
            ->where('status', 1);

        if ($request->filled('store_id')) {
            $query->where('store_id', $request->store_id);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('sub_category_id')) {
            $query->where('sub_category_id', $request->sub_category_id);
        }

        if ($request->filled('featured')) {
            $query->where('is_featured', 1);
        }

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(function ($qb) use ($q) {
                $qb->where('name', 'like', '%' . $q . '%')
                   ->orWhere('sku', 'like', '%' . $q . '%');
            });
        }

        $query->orderBy('created_at', 'desc');

        $perPage  = (int) $request->get('per_page', 15);
        $products = $query->paginate($perPage);

        return response()->json([
            'status' => true,
            'data'   => $products->map(fn($p) => $this->formatList($p)),
            'meta'   => [
                'total'        => $products->total(),
                'per_page'     => $products->perPage(),
                'current_page' => $products->currentPage(),
                'last_page'    => $products->lastPage(),
            ],
        ]);
    }

    /**
     * GET /api/products/{id}
     */
    public function show(string $id)
    {
        $product = Product::with([
            'category',
            'subCategory',
            'images',
            'colors',
            'specifications',
        ])->where('status', 1)->find($id);

        if (!$product) {
            return response()->json(['status' => false, 'message' => 'Product not found.'], 404);
        }

        return response()->json([
            'status' => true,
            'data'   => $this->formatDetail($product),
        ]);
    }

    /**
     * GET /api/products/trending
     *
     * Best sellers: products ranked by how many units were actually ordered.
     * Cancelled and soft-deleted orders are excluded, so the ranking only
     * reflects real sales.
     *
     * Filters (all optional):
     *   ?store_id=1
     *   ?category_id=1
     *   ?sub_category_id=1
     *   ?days=30               (trending window — only orders from last N days)
     *   ?per_page=10           (default 15, max 100)
     *   ?page=1
     */
    public function trending(Request $request)
    {
        $request->validate([
            'store_id'        => 'nullable|integer|exists:stores,id',
            'category_id'     => 'nullable|integer|exists:categories,id',
            'sub_category_id' => 'nullable|integer|exists:sub_categories,id',
            'days'            => 'nullable|integer|min:1|max:365',
            'per_page'        => 'nullable|integer|min:1|max:100',
        ]);

        // Units sold per product, ignoring cancelled / deleted orders.
        $sales = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereNull('orders.deleted_at')
            ->where('orders.status', '!=', 'cancelled')
            ->whereNotNull('order_items.product_id')
            ->when($request->filled('days'), fn($q) => $q->where(
                'orders.created_at', '>=', now()->subDays((int) $request->days)
            ))
            ->groupBy('order_items.product_id')
            ->select(
                'order_items.product_id',
                DB::raw('SUM(order_items.quantity) as total_sold'),
                DB::raw('COUNT(DISTINCT order_items.order_id) as orders_count'),
                DB::raw('SUM(order_items.line_total) as total_revenue')
            );

        $query = Product::with(['category', 'subCategory', 'primaryImage', 'colors'])
            ->joinSub($sales, 'sales', fn($join) => $join->on('sales.product_id', '=', 'products.id'))
            ->where('products.status', 1)
            ->select('products.*', 'sales.total_sold', 'sales.orders_count', 'sales.total_revenue');

        if ($request->filled('store_id')) {
            $query->where('products.store_id', $request->store_id);
        }

        if ($request->filled('category_id')) {
            $query->where('products.category_id', $request->category_id);
        }

        if ($request->filled('sub_category_id')) {
            $query->where('products.sub_category_id', $request->sub_category_id);
        }

        $query->orderByDesc('sales.total_sold')
              ->orderByDesc('sales.orders_count')
              ->orderByDesc('products.id');

        $perPage  = (int) $request->get('per_page', 15);
        $products = $query->paginate($perPage);

        $offset = ($products->currentPage() - 1) * $products->perPage();

        return response()->json([
            'status' => true,
            'data'   => $products->getCollection()->values()->map(function ($p, $i) use ($offset) {
                return array_merge($this->formatList($p), [
                    'rank'          => $offset + $i + 1,
                    'total_sold'    => (int) $p->total_sold,
                    'orders_count'  => (int) $p->orders_count,
                    'total_revenue' => round((float) $p->total_revenue, 2),
                ]);
            })->values(),
            'meta'   => [
                'total'        => $products->total(),
                'per_page'     => $products->perPage(),
                'current_page' => $products->currentPage(),
                'last_page'    => $products->lastPage(),
                'days'         => $request->filled('days') ? (int) $request->days : null,
            ],
        ]);
    }

    // ── Formatters ────────────────────────────────────────────────────────────

    private function formatList(Product $p): array
    {
        $primary = $p->primaryImage;

        return [
            'id'            => $p->id,
            'name'          => $p->name,
            'slug'          => $p->slug,
            'sku'           => $p->sku,
            'price'         => (float) $p->price,
            'sale_price'    => $p->sale_price ? (float) $p->sale_price : null,
            'stock'         => $p->stock,
            'is_gst_paid'   => (bool) $p->is_gst_paid,
            'gst_percentage'=> $p->gst_percentage ? (float) $p->gst_percentage : null,
            'is_featured'   => (bool) $p->is_featured,
            'category'      => $p->category ? ['id' => $p->category->id, 'name' => $p->category->name] : null,
            'sub_category'  => $p->subCategory ? ['id' => $p->subCategory->id, 'name' => $p->subCategory->sub_category] : null,
            'primary_image' => ProductImage::urlFor($primary?->image_path),
            'colors'        => $p->colors->map(fn($c) => [
                'id'       => $c->id,
                'name'     => $c->name,
                'hex_code' => $c->hex_code,
            ])->values(),
            'created_at'    => $p->created_at,
        ];
    }

    private function formatDetail(Product $p): array
    {
        return [
            'id'             => $p->id,
            'name'           => $p->name,
            'slug'           => $p->slug,
            'sku'            => $p->sku,
            'description'    => $p->description,
            'price'          => (float) $p->price,
            'sale_price'     => $p->sale_price ? (float) $p->sale_price : null,
            'stock'          => $p->stock,
            'in_stock'       => $p->stock > 0,
            'is_gst_paid'    => (bool) $p->is_gst_paid,
            'gst_percentage' => $p->gst_percentage ? (float) $p->gst_percentage : null,
            'is_featured'    => (bool) $p->is_featured,
            'category'       => $p->category ? ['id' => $p->category->id, 'name' => $p->category->name] : null,
            'sub_category'   => $p->subCategory ? ['id' => $p->subCategory->id, 'name' => $p->subCategory->sub_category] : null,
            'images'         => $p->images->map(fn($img) => [
                'id'         => $img->id,
                'url'        => ProductImage::urlFor($img->image_path),
                'is_primary' => (bool) $img->is_primary,
                'sort_order' => $img->sort_order,
            ])->values(),
            'colors'         => $p->colors->map(fn($c) => [
                'id'       => $c->id,
                'name'     => $c->name,
                'hex_code' => $c->hex_code,
            ])->values(),
            'specifications' => $p->specifications->map(fn($s) => [
                'key'   => $s->spec_key,
                'value' => $s->spec_value,
            ])->values(),
            'created_at'     => $p->created_at,
            'updated_at'     => $p->updated_at,
        ];
    }
}

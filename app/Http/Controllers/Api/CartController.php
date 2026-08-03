<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductImage;
use App\Services\BillingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Store-wise cart.
 *
 * A user holds one cart per store. Adding a product resolves the cart from the
 * product's own store, so items from different stores never mix in one cart and
 * each store checks out (and bills) independently.
 */
class CartController extends Controller
{
    public function __construct(private BillingService $billing)
    {
    }

    /**
     * GET /api/cart            — every store cart the user has
     * GET /api/cart?store_id=1 — just that store's cart
     */
    public function index(Request $request)
    {
        $request->validate([
            'store_id' => 'nullable|integer|exists:stores,id',
        ]);

        $carts = Cart::with($this->itemRelations())
            ->where('user_id', $request->user()->id)
            ->when($request->filled('store_id'), fn($q) => $q->where('store_id', $request->store_id))
            ->with('store')
            ->get();

        $payload = $carts
            ->map(fn(Cart $cart) => $this->formatCart($cart))
            ->filter(fn($c) => count($c['items']) > 0)
            ->values();

        return response()->json([
            'status' => true,
            'data'   => [
                'carts'         => $payload,
                'grand_summary' => [
                    'total_carts'  => $payload->count(),
                    'items_count'  => (int) $payload->sum(fn($c) => $c['summary']['items_count']),
                    'total_amount' => round((float) $payload->sum(fn($c) => $c['summary']['total_amount']), 2),
                ],
            ],
        ]);
    }

    /**
     * POST /api/cart/add
     * { product_id, quantity?, color_id?, replace? }
     *
     * quantity defaults to 1. By default the quantity is ADDED to whatever is
     * already in the cart; pass replace=true to set it absolutely instead.
     */
    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'quantity'   => 'nullable|integer|min:1',
            'color_id'   => 'nullable|integer|exists:colors,id',
            'replace'    => 'nullable|boolean',
        ]);

        $user     = $request->user();
        $quantity = (int) ($request->quantity ?? 1);
        $maxQty   = (int) config('cart.max_quantity_per_item', 99);

        $product = Product::with('colors')->find($request->product_id);

        if (!$product || (int) $product->status !== 1) {
            return response()->json(['status' => false, 'message' => 'This product is not available.'], 404);
        }

        if (empty($product->store_id)) {
            return response()->json(['status' => false, 'message' => 'This product is not linked to any store.'], 422);
        }

        // A colour, when given, must actually belong to this product.
        if ($request->filled('color_id') && !$product->colors->contains('id', (int) $request->color_id)) {
            return response()->json(['status' => false, 'message' => 'Selected colour is not available for this product.'], 422);
        }

        $colorId = $request->filled('color_id') ? (int) $request->color_id : null;

        try {
            $item = DB::transaction(function () use ($user, $product, $colorId, $quantity, $maxQty, $request) {
                $attributes = ['user_id' => $user->id, 'store_id' => $product->store_id];

                try {
                    $cart = Cart::firstOrCreate($attributes);
                } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
                    // Two "add to cart" taps landing at once — reuse the winner.
                    $cart = Cart::where($attributes)->firstOrFail();
                }

                $item = CartItem::where('cart_id', $cart->id)
                    ->where('product_id', $product->id)
                    ->when($colorId === null,
                        fn($q) => $q->whereNull('color_id'),
                        fn($q) => $q->where('color_id', $colorId))
                    ->lockForUpdate()
                    ->first();

                $newQty = ($item && !$request->boolean('replace'))
                    ? $item->quantity + $quantity
                    : $quantity;

                $newQty = min($newQty, $maxQty);

                if ($newQty > (int) $product->stock) {
                    abort(response()->json([
                        'status'          => false,
                        'message'         => (int) $product->stock > 0
                            ? 'Only ' . $product->stock . ' unit(s) left in stock.'
                            : 'This product is out of stock.',
                        'available_stock' => (int) $product->stock,
                    ], 422));
                }

                if ($item) {
                    $item->update(['quantity' => $newQty]);
                } else {
                    $item = CartItem::create([
                        'cart_id'    => $cart->id,
                        'product_id' => $product->id,
                        'color_id'   => $colorId,
                        'quantity'   => $newQty,
                    ]);
                }

                return $item;
            });
        } catch (\Illuminate\Http\Exceptions\HttpResponseException $e) {
            return $e->getResponse();
        }

        $cart = Cart::with($this->itemRelations())->with('store')->find($item->cart_id);

        return response()->json([
            'status'  => true,
            'message' => 'Product added to cart.',
            'data'    => $this->formatCart($cart),
        ], 201);
    }

    /**
     * POST /api/cart/update/{itemId}
     * { quantity }  — quantity 0 removes the line.
     */
    public function update(Request $request, string $itemId)
    {
        $request->validate([
            'quantity' => 'required|integer|min:0',
        ]);

        $item = $this->findUserItem($request, $itemId);
        if (!$item) {
            return response()->json(['status' => false, 'message' => 'Cart item not found.'], 404);
        }

        $quantity = (int) $request->quantity;

        if ($quantity === 0) {
            $cartId = $item->cart_id;
            $item->delete();
            $this->dropCartIfEmpty($cartId);

            return response()->json([
                'status'  => true,
                'message' => 'Item removed from cart.',
                'data'    => $this->reloadCart($cartId),
            ]);
        }

        $maxQty = (int) config('cart.max_quantity_per_item', 99);
        if ($quantity > $maxQty) {
            return response()->json([
                'status'  => false,
                'message' => 'You can order a maximum of ' . $maxQty . ' unit(s) of one item.',
            ], 422);
        }

        $product = $item->product;
        if (!$product || (int) $product->status !== 1) {
            return response()->json(['status' => false, 'message' => 'This product is no longer available.'], 422);
        }

        if ($quantity > (int) $product->stock) {
            return response()->json([
                'status'          => false,
                'message'         => (int) $product->stock > 0
                    ? 'Only ' . $product->stock . ' unit(s) left in stock.'
                    : 'This product is out of stock.',
                'available_stock' => (int) $product->stock,
            ], 422);
        }

        $item->update(['quantity' => $quantity]);

        return response()->json([
            'status'  => true,
            'message' => 'Cart updated.',
            'data'    => $this->reloadCart($item->cart_id),
        ]);
    }

    /**
     * DELETE /api/cart/item/{itemId}
     */
    public function removeItem(Request $request, string $itemId)
    {
        $item = $this->findUserItem($request, $itemId);
        if (!$item) {
            return response()->json(['status' => false, 'message' => 'Cart item not found.'], 404);
        }

        $cartId = $item->cart_id;
        $item->delete();
        $this->dropCartIfEmpty($cartId);

        return response()->json([
            'status'  => true,
            'message' => 'Item removed from cart.',
            'data'    => $this->reloadCart($cartId),
        ]);
    }

    /**
     * DELETE /api/cart?store_id=1 — empty one store cart, or all carts if
     * store_id is omitted.
     */
    public function clear(Request $request)
    {
        $request->validate([
            'store_id' => 'nullable|integer|exists:stores,id',
        ]);

        $carts = Cart::where('user_id', $request->user()->id)
            ->when($request->filled('store_id'), fn($q) => $q->where('store_id', $request->store_id))
            ->get();

        foreach ($carts as $cart) {
            $cart->items()->delete();
            $cart->delete();
        }

        return response()->json([
            'status'  => true,
            'message' => $request->filled('store_id') ? 'Cart cleared.' : 'All carts cleared.',
        ]);
    }

    /**
     * GET /api/cart/count — badge count for the app header.
     */
    public function count(Request $request)
    {
        $count = CartItem::whereIn('cart_id', Cart::where('user_id', $request->user()->id)->select('id'))
            ->sum('quantity');

        return response()->json([
            'status' => true,
            'data'   => ['items_count' => (int) $count],
        ]);
    }

    /**
     * GET /api/cart/summary?store_id=1 — billing preview before checkout.
     */
    public function summary(Request $request)
    {
        $request->validate([
            'store_id' => 'required|integer|exists:stores,id',
        ]);

        $cart = Cart::with($this->itemRelations())->with('store')
            ->where('user_id', $request->user()->id)
            ->where('store_id', $request->store_id)
            ->first();

        if (!$cart || $cart->items->isEmpty()) {
            return response()->json(['status' => false, 'message' => 'Your cart is empty for this store.'], 404);
        }

        return response()->json([
            'status' => true,
            'data'   => $this->formatCart($cart),
        ]);
    }

    // ── Internals ─────────────────────────────────────────────────────────────

    private function itemRelations(): array
    {
        return ['items.product.primaryImage', 'items.product.category', 'items.color'];
    }

    private function findUserItem(Request $request, string $itemId): ?CartItem
    {
        return CartItem::with('product')
            ->whereKey($itemId)
            ->whereIn('cart_id', Cart::where('user_id', $request->user()->id)->select('id'))
            ->first();
    }

    private function dropCartIfEmpty(int $cartId): void
    {
        $cart = Cart::withCount('items')->find($cartId);
        if ($cart && $cart->items_count === 0) {
            $cart->delete();
        }
    }

    private function reloadCart(int $cartId): ?array
    {
        $cart = Cart::with($this->itemRelations())->with('store')->find($cartId);

        return $cart ? $this->formatCart($cart) : null;
    }

    /**
     * Build the cart payload. Unavailable lines (product deactivated, deleted or
     * out of stock) are kept in the list so the app can show them greyed out,
     * but they are excluded from the billing totals.
     */
    private function formatCart(Cart $cart): array
    {
        $items    = [];
        $billable = [];

        foreach ($cart->items as $item) {
            $product = $item->product;

            if (!$product) {
                continue; // product hard-deleted; nothing meaningful to show
            }

            $available   = (int) $product->status === 1 && $product->deleted_at === null;
            $stock       = (int) $product->stock;
            $inStock     = $stock > 0;
            $enoughStock = $stock >= $item->quantity;
            $isBillable  = $available && $inStock && $enoughStock;

            $line = $this->billing->calculateLine($product, (int) $item->quantity);

            if ($isBillable) {
                $billable[] = $line;
            }

            $items[] = [
                'id'              => $item->id,
                'product_id'      => $product->id,
                'name'            => $product->name,
                'slug'            => $product->slug,
                'sku'             => $product->sku,
                'image'           => ProductImage::urlFor($product->primaryImage?->image_path),
                'category'        => $product->category ? ['id' => $product->category->id, 'name' => $product->category->name] : null,
                'color'           => $item->color ? [
                    'id'       => $item->color->id,
                    'name'     => $item->color->name,
                    'hex_code' => $item->color->hex_code,
                ] : null,
                'quantity'        => (int) $item->quantity,
                'mrp'             => $line['mrp'],
                'unit_price'      => $line['unit_price'],
                'discount_amount' => $line['discount_amount'],
                'tax_type'        => $line['tax_type'],
                'gst_percentage'  => $line['gst_percentage'],
                'taxable_amount'  => $line['taxable_amount'],
                'gst_amount'      => $line['gst_amount'],
                'line_total'      => $line['line_total'],
                'available_stock' => $stock,
                'is_available'    => $isBillable,
                'unavailable_reason' => $isBillable ? null : (
                    !$available ? 'Product is no longer available.'
                        : (!$inStock ? 'Out of stock.' : 'Only ' . $stock . ' unit(s) left in stock.')
                ),
            ];
        }

        $summary = $this->billing->summarise($billable);

        return [
            'cart_id' => $cart->id,
            'store'   => $cart->store ? [
                'id'   => $cart->store->id,
                'name' => $cart->store->name,
                'slug' => $cart->store->slug,
            ] : null,
            'items'                => $items,
            'summary'              => $summary,
            'has_unavailable_items' => collect($items)->contains(fn($i) => !$i['is_available']),
            'is_checkout_ready'    => count($items) > 0 && !collect($items)->contains(fn($i) => !$i['is_available']),
        ];
    }
}

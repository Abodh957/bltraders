<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Shop;
use App\Services\BillingService;
use Illuminate\Http\Request;
use App\Support\StoreContext;
use Illuminate\Support\Facades\DB;

/**
 * Orders are placed per store: one store cart becomes one order, so billing,
 * GST and fulfilment stay scoped to a single store.
 */
class OrderController extends Controller
{
    public function __construct(private BillingService $billing)
    {
    }

    /**
     * POST /api/orders
     * { store_id, payment_method?, shipping_name?, shipping_phone?,
     *   shipping_address?, shipping_city?, shipping_state?, shipping_country?,
     *   shipping_pincode?, notes? }
     *
     * Address fields fall back to the user's registered shop when omitted.
     */
    public function store(Request $request)
    {
        $request->validate([
            'store_id'         => 'nullable|integer|exists:stores,id',
            'payment_method'   => 'nullable|in:cod,online',
            'shipping_name'    => 'nullable|string|max:255',
            'shipping_phone'   => 'nullable|string|max:20',
            'shipping_address' => 'nullable|string|max:1000',
            'shipping_city'    => 'nullable|string|max:100',
            'shipping_state'   => 'nullable|string|max:100',
            'shipping_country' => 'nullable|string|max:100',
            'shipping_pincode' => 'nullable|string|max:15',
            'notes'            => 'nullable|string|max:1000',
        ]);

        $user = $request->user();
        $shop = Shop::where('user_id', $user->id)->first();

        // ?store_id / body store_id wins; otherwise the customer's selected store.
        $storeId = StoreContext::resolve($request);

        if ($storeId === null) {
            return response()->json([
                'status'  => false,
                'message' => 'Select a store first, or pass store_id.',
            ], 422);
        }

        $shipping = $this->resolveShippingAddress($request, $shop, $user);

        if (empty($shipping['shipping_address']) || empty($shipping['shipping_phone'])) {
            return response()->json([
                'status'  => false,
                'message' => 'Delivery address and phone number are required.',
                'errors'  => [
                    'shipping_address' => empty($shipping['shipping_address']) ? ['Delivery address is required.'] : [],
                    'shipping_phone'   => empty($shipping['shipping_phone']) ? ['Phone number is required.'] : [],
                ],
            ], 422);
        }

        try {
            $order = DB::transaction(function () use ($request, $user, $shop, $shipping, $storeId) {
                $cart = Cart::with(['items.product', 'items.color'])
                    ->where('user_id', $user->id)
                    ->where('store_id', $storeId)
                    ->lockForUpdate()
                    ->first();

                if (!$cart || $cart->items->isEmpty()) {
                    $this->fail('Your cart is empty for this store.', 422);
                }

                $lines = [];
                $rows  = [];

                foreach ($cart->items as $item) {
                    // Lock the row so two checkouts can't both claim the last unit.
                    $product = Product::with('primaryImage')
                        ->whereKey($item->product_id)
                        ->lockForUpdate()
                        ->first();

                    if (!$product || (int) $product->status !== 1) {
                        $this->fail('"' . ($item->product->name ?? 'A product') . '" is no longer available. Please remove it from your cart.', 422);
                    }

                    if ((int) $product->stock < (int) $item->quantity) {
                        $this->fail(
                            (int) $product->stock > 0
                                ? 'Only ' . $product->stock . ' unit(s) of "' . $product->name . '" left in stock.'
                                : '"' . $product->name . '" is out of stock.',
                            422
                        );
                    }

                    $line    = $this->billing->calculateLine($product, (int) $item->quantity);
                    $lines[] = $line;

                    $rows[] = [
                        'product_id'     => $product->id,
                        'color_id'       => $item->color_id,
                        'product_name'   => $product->name,
                        'product_sku'    => $product->sku,
                        'product_image'  => $product->primaryImage?->image_path,
                        'color_name'     => $item->color?->name,
                        'mrp'            => $line['mrp'],
                        'unit_price'     => $line['unit_price'],
                        'quantity'       => $line['quantity'],
                        'tax_type'       => $line['tax_type'],
                        'gst_percentage' => $line['gst_percentage'],
                        'taxable_amount' => $line['taxable_amount'],
                        'gst_amount'     => $line['gst_amount'],
                        'line_total'     => $line['line_total'],
                    ];

                    $product->decrement('stock', (int) $item->quantity);
                }

                $summary = $this->billing->summarise($lines);

                $order = Order::create(array_merge([
                    'order_number'    => Order::generateOrderNumber(),
                    'user_id'         => $user->id,
                    'shop_id'         => $shop?->id,
                    'store_id'        => $cart->store_id,
                    'status'          => 'pending',
                    'payment_method'  => $request->payment_method ?? 'cod',
                    'payment_status'  => 'pending',
                    'items_count'     => $summary['items_count'],
                    'mrp_total'       => $summary['mrp_total'],
                    'subtotal'        => $summary['subtotal'],
                    'tax_amount'      => $summary['tax_amount'],
                    'discount_amount' => $summary['discount_amount'],
                    'shipping_charge' => $summary['shipping_charge'],
                    'total_amount'    => $summary['total_amount'],
                    'notes'           => $request->notes,
                    'placed_at'       => now(),
                ], $shipping));

                foreach ($rows as $row) {
                    $order->items()->create($row);
                }

                OrderStatusHistory::create([
                    'order_id'   => $order->id,
                    'from_status' => null,
                    'to_status'  => 'pending',
                    'note'       => 'Order placed.',
                    'changed_by' => $user->id,
                ]);

                // Cart has become an order — clear it.
                $cart->items()->delete();
                $cart->delete();

                return $order;
            });
        } catch (\Illuminate\Http\Exceptions\HttpResponseException $e) {
            return $e->getResponse();
        }

        $order->load(['items.product.primaryImage', 'items.color', 'store', 'statusHistories']);

        return response()->json([
            'status'  => true,
            'message' => 'Order placed successfully.',
            'data'    => $this->formatDetail($order),
        ], 201);
    }

    /**
     * GET /api/orders
     *   ?status=pending  ?store_id=1  ?per_page=15  ?page=1
     */
    public function index(Request $request)
    {
        $request->validate([
            'status'   => 'nullable|in:pending,confirmed,processing,shipped,delivered,cancelled',
            'store_id' => 'nullable|integer|exists:stores,id',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $query = Order::with(['store', 'items'])
            ->where('user_id', $request->user()->id)
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->status))
            ->when(StoreContext::resolve($request) !== null,
                fn($q) => $q->where('store_id', StoreContext::resolve($request)))
            ->orderByDesc('created_at');

        $perPage = (int) $request->get('per_page', 15);
        $orders  = $query->paginate($perPage);

        return response()->json([
            'status' => true,
            'data'   => $orders->getCollection()->map(fn(Order $o) => $this->formatList($o))->values(),
            'meta'   => [
                'total'        => $orders->total(),
                'per_page'     => $orders->perPage(),
                'current_page' => $orders->currentPage(),
                'last_page'    => $orders->lastPage(),
            ],
        ]);
    }

    /**
     * GET /api/orders/{id}
     */
    public function show(Request $request, string $id)
    {
        $order = $this->findUserOrder($request, $id);

        if (!$order) {
            return response()->json(['status' => false, 'message' => 'Order not found.'], 404);
        }

        return response()->json([
            'status' => true,
            'data'   => $this->formatDetail($order),
        ]);
    }

    /**
     * POST /api/orders/{id}/cancel
     * { reason? }
     */
    public function cancel(Request $request, string $id)
    {
        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $order = $this->findUserOrder($request, $id);

        if (!$order) {
            return response()->json(['status' => false, 'message' => 'Order not found.'], 404);
        }

        if ($order->status === 'cancelled') {
            return response()->json(['status' => false, 'message' => 'This order is already cancelled.'], 422);
        }

        if (!$order->isCancellable()) {
            return response()->json([
                'status'  => false,
                'message' => 'This order can no longer be cancelled as it is already ' . $order->status . '.',
            ], 422);
        }

        DB::transaction(function () use ($order, $request) {
            $from = $order->status;

            // Put the reserved stock back.
            foreach ($order->items as $item) {
                if ($item->product_id) {
                    Product::whereKey($item->product_id)->increment('stock', (int) $item->quantity);
                }
            }

            $order->update([
                'status'         => 'cancelled',
                'cancel_reason'  => $request->reason,
                'cancelled_at'   => now(),
                'payment_status' => $order->payment_status === 'paid' ? 'refunded' : $order->payment_status,
            ]);

            OrderStatusHistory::create([
                'order_id'    => $order->id,
                'from_status' => $from,
                'to_status'   => 'cancelled',
                'note'        => $request->reason ?: 'Cancelled by customer.',
                'changed_by'  => $request->user()->id,
            ]);
        });

        return response()->json([
            'status'  => true,
            'message' => 'Order cancelled successfully.',
            'data'    => $this->formatDetail($order->fresh(['items.product.primaryImage', 'items.color', 'store', 'statusHistories'])),
        ]);
    }

    /**
     * GET /api/orders/{id}/invoice — billing document for the placed order.
     */
    public function invoice(Request $request, string $id)
    {
        $order = $this->findUserOrder($request, $id);

        if (!$order) {
            return response()->json(['status' => false, 'message' => 'Order not found.'], 404);
        }

        $gstBreakup = [];
        foreach ($order->items as $item) {
            if ((float) $item->gst_amount <= 0) {
                continue;
            }
            $slab = number_format((float) $item->gst_percentage, 2, '.', '');
            $gstBreakup[$slab] ??= ['gst_percentage' => (float) $slab, 'taxable_amount' => 0.0, 'gst_amount' => 0.0];
            $gstBreakup[$slab]['taxable_amount'] += (float) $item->taxable_amount;
            $gstBreakup[$slab]['gst_amount']     += (float) $item->gst_amount;
        }

        $gstBreakup = array_values(array_map(fn($r) => [
            'gst_percentage' => $r['gst_percentage'],
            'taxable_amount' => round($r['taxable_amount'], 2),
            'gst_amount'     => round($r['gst_amount'], 2),
        ], $gstBreakup));

        return response()->json([
            'status' => true,
            'data'   => [
                'invoice_number' => $order->order_number,
                'invoice_date'   => optional($order->placed_at ?? $order->created_at)->toDateTimeString(),
                'store'          => $order->store ? ['id' => $order->store->id, 'name' => $order->store->name] : null,
                'billed_to'      => [
                    'name'    => $order->shipping_name,
                    'phone'   => $order->shipping_phone,
                    'address' => $order->shipping_address,
                    'city'    => $order->shipping_city,
                    'state'   => $order->shipping_state,
                    'country' => $order->shipping_country,
                    'pincode' => $order->shipping_pincode,
                ],
                'items'   => $order->items->map(fn(OrderItem $i) => $this->formatItem($i))->values(),
                'billing' => $this->billingBlock($order),
                'gst_breakup'    => $gstBreakup,
                'payment_method' => $order->payment_method,
                'payment_status' => $order->payment_status,
                'status'         => $order->status,
            ],
        ]);
    }

    // ── Internals ─────────────────────────────────────────────────────────────

    /** Abort the transaction with a JSON error response. */
    private function fail(string $message, int $code): void
    {
        abort(response()->json(['status' => false, 'message' => $message], $code));
    }

    private function findUserOrder(Request $request, string $id): ?Order
    {
        return Order::with(['items.product.primaryImage', 'items.color', 'store', 'statusHistories'])
            ->whereKey($id)
            ->where('user_id', $request->user()->id)
            ->first();
    }

    /**
     * Request fields win; anything left blank falls back to the user's shop.
     */
    private function resolveShippingAddress(Request $request, ?Shop $shop, $user): array
    {
        return [
            'shipping_name'    => $request->shipping_name    ?: ($shop->shop_name ?? $user->name),
            'shipping_phone'   => $request->shipping_phone   ?: $user->phone_no,
            'shipping_address' => $request->shipping_address ?: ($shop->shop_address ?? null),
            'shipping_city'    => $request->shipping_city    ?: ($shop->city ?? null),
            'shipping_state'   => $request->shipping_state   ?: ($shop->state ?? null),
            'shipping_country' => $request->shipping_country ?: ($shop->country ?? null),
            'shipping_pincode' => $request->shipping_pincode ?: ($shop->pincode ?? null),
        ];
    }

    private function billingBlock(Order $order): array
    {
        return [
            'items_count'     => (int) $order->items_count,
            'mrp_total'       => (float) $order->mrp_total,
            'discount_amount' => (float) $order->discount_amount,
            'subtotal'        => (float) $order->subtotal,
            'tax_amount'      => (float) $order->tax_amount,
            'shipping_charge' => (float) $order->shipping_charge,
            'total_amount'    => (float) $order->total_amount,
        ];
    }

    private function formatItem(OrderItem $i): array
    {
        return [
            'id'             => $i->id,
            'product_id'     => $i->product_id,
            'name'           => $i->product_name,
            'sku'            => $i->product_sku,
            'image'          => ProductImage::urlFor($i->product_image),
            'color'          => $i->color_name ? ['id' => $i->color_id, 'name' => $i->color_name] : null,
            'mrp'            => (float) $i->mrp,
            'unit_price'     => (float) $i->unit_price,
            'quantity'       => (int) $i->quantity,
            'tax_type'       => $i->tax_type,
            'gst_percentage' => (float) $i->gst_percentage,
            'taxable_amount' => (float) $i->taxable_amount,
            'gst_amount'     => (float) $i->gst_amount,
            'line_total'     => (float) $i->line_total,
        ];
    }

    private function formatList(Order $order): array
    {
        return [
            'id'             => $order->id,
            'order_number'   => $order->order_number,
            'status'         => $order->status,
            'payment_method' => $order->payment_method,
            'payment_status' => $order->payment_status,
            'store'          => $order->store ? ['id' => $order->store->id, 'name' => $order->store->name] : null,
            'items_count'    => (int) $order->items_count,
            'total_amount'   => (float) $order->total_amount,
            'is_cancellable' => $order->isCancellable(),
            'placed_at'      => optional($order->placed_at)->toDateTimeString(),
            'created_at'     => $order->created_at,
        ];
    }

    private function formatDetail(Order $order): array
    {
        return [
            'id'             => $order->id,
            'order_number'   => $order->order_number,
            'status'         => $order->status,
            'payment_method' => $order->payment_method,
            'payment_status' => $order->payment_status,
            'store'          => $order->store ? [
                'id'   => $order->store->id,
                'name' => $order->store->name,
                'slug' => $order->store->slug,
            ] : null,
            'items'   => $order->items->map(fn(OrderItem $i) => $this->formatItem($i))->values(),
            'billing' => $this->billingBlock($order),
            'shipping_address' => [
                'name'    => $order->shipping_name,
                'phone'   => $order->shipping_phone,
                'address' => $order->shipping_address,
                'city'    => $order->shipping_city,
                'state'   => $order->shipping_state,
                'country' => $order->shipping_country,
                'pincode' => $order->shipping_pincode,
            ],
            'notes'          => $order->notes,
            'cancel_reason'  => $order->cancel_reason,
            'is_cancellable' => $order->isCancellable(),
            'timeline'       => $order->statusHistories->map(fn(OrderStatusHistory $h) => [
                'from_status' => $h->from_status,
                'to_status'   => $h->to_status,
                'note'        => $h->note,
                'at'          => optional($h->created_at)->toDateTimeString(),
            ])->values(),
            'placed_at'    => optional($order->placed_at)->toDateTimeString(),
            'cancelled_at' => optional($order->cancelled_at)->toDateTimeString(),
            'delivered_at' => optional($order->delivered_at)->toDateTimeString(),
            'created_at'   => $order->created_at,
        ];
    }
}

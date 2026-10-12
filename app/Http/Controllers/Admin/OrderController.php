<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    protected $columns;

    public function __construct()
    {
        $this->columns = [
            'orders.id', 'orders.order_number', 'orders.total_amount',
            'orders.status', 'orders.payment_status', 'orders.created_at',
        ];
    }

    public function index()
    {
        $stores = Store::orderBy('name')->get();

        return view('admin.orders.index', compact('stores'));
    }

    /** Detail screen: items, billing, payment, timeline. */
    public function show(string $id)
    {
        $order = Order::with(['items', 'store', 'user', 'shop', 'statusHistories.changedBy'])->find($id);

        if (!$order) {
            return redirect()->route('orders.index')->with('error', 'Order not found.');
        }

        return view('admin.orders.show', compact('order'));
    }

    /** POST — move the order along its lifecycle. */
    public function updateStatus(Request $request, string $id)
    {
        $order = Order::with('items')->find($id);

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found.'], 404);
        }

        $request->validate([
            'status' => ['required', Rule::in(array_keys(Order::FLOW))],
            'note'   => 'nullable|string|max:500',
        ]);

        $from = $order->status;
        $to   = $request->status;

        if ($from === $to) {
            return response()->json(['success' => false, 'message' => 'Order is already ' . $to . '.'], 422);
        }

        if (!in_array($to, Order::FLOW[$from] ?? [], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot move an order from ' . $from . ' to ' . $to . '.',
            ], 422);
        }

        DB::transaction(function () use ($order, $from, $to, $request) {
            // Cancelling puts the reserved stock back, exactly like the customer-side cancel.
            if ($to === 'cancelled') {
                foreach ($order->items as $item) {
                    if ($item->product_id) {
                        Product::whereKey($item->product_id)->increment('stock', (int) $item->quantity);
                    }
                }
                $order->cancelled_at  = now();
                $order->cancel_reason = $request->note ?: 'Cancelled by admin.';
                if ($order->payment_status === 'paid') {
                    $order->payment_status = 'refunded';
                }
            }

            if ($to === 'delivered') {
                $order->delivered_at = now();
            }

            $order->status = $to;
            $order->save();

            OrderStatusHistory::create([
                'order_id'    => $order->id,
                'from_status' => $from,
                'to_status'   => $to,
                'note'        => $request->note ?: 'Status changed by admin.',
                'changed_by'  => auth()->id(),
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Order status updated to ' . $to . '.',
        ]);
    }

    /** POST — record payment method, amount received and payment status. */
    public function updatePayment(Request $request, string $id)
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found.'], 404);
        }

        $request->validate([
            'payment_method' => ['required', Rule::in(Order::PAYMENT_METHODS)],
            'paid_amount'    => 'required|numeric|min:0|max:99999999.99',
            'payment_status' => ['nullable', Rule::in(Order::PAYMENT_STATUSES)],
            'payment_note'   => 'nullable|string|max:255',
        ]);

        $paid = round((float) $request->paid_amount, 2);

        if ($paid > (float) $order->total_amount + 0.01) {
            return response()->json([
                'success' => false,
                'message' => 'Paid amount cannot be more than the order total (' . number_format((float) $order->total_amount, 2) . ').',
            ], 422);
        }

        DB::transaction(function () use ($order, $request, $paid) {
            $before = (float) $order->paid_amount;

            $order->payment_method = $request->payment_method;
            $order->paid_amount    = $paid;
            $order->payment_note   = $request->payment_note;
            // Blank status = work it out from the money received.
            $order->payment_status = $request->payment_status ?: $order->derivedPaymentStatus();
            $order->save();

            OrderStatusHistory::create([
                'order_id'    => $order->id,
                'from_status' => $order->status,
                'to_status'   => $order->status,
                'note'        => sprintf(
                    'Payment updated by admin: %s, received %s (was %s). Status: %s.%s',
                    strtoupper($request->payment_method),
                    number_format($paid, 2),
                    number_format($before, 2),
                    $order->payment_status,
                    $request->payment_note ? ' Note: ' . $request->payment_note : ''
                ),
                'changed_by'  => auth()->id(),
            ]);
        });

        $order->refresh();

        return response()->json([
            'success'        => true,
            'message'        => 'Payment details updated.',
            'paid_amount'    => (float) $order->paid_amount,
            'due_amount'     => $order->dueAmount(),
            'payment_status' => $order->payment_status,
        ]);
    }

    /**
     * POST — change a line's quantity, or remove the line.
     *
     * Totals are recalculated from the amounts stored on the order item, never
     * from the live product price, so an old order is never silently repriced.
     * Stock moves by the difference so inventory stays honest.
     */
    public function updateItem(Request $request, string $id)
    {
        $order = Order::with('items')->find($id);

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found.'], 404);
        }

        if (!$order->itemsEditable()) {
            return response()->json([
                'success' => false,
                'message' => 'Items cannot be edited once the order is ' . $order->status . '.',
            ], 422);
        }

        $request->validate([
            'item_id'  => 'required|integer',
            'quantity' => 'required|integer|min:0|max:100000',
        ]);

        $item = $order->items->firstWhere('id', (int) $request->item_id);

        if (!$item) {
            return response()->json(['success' => false, 'message' => 'Order item not found.'], 404);
        }

        $newQty = (int) $request->quantity;

        if ($newQty === (int) $item->quantity) {
            return response()->json(['success' => false, 'message' => 'Quantity is already ' . $newQty . '.'], 422);
        }

        if ($newQty === 0 && $order->items->count() === 1) {
            return response()->json([
                'success' => false,
                'message' => 'An order must keep at least one item. Cancel the order instead.',
            ], 422);
        }

        DB::transaction(function () use ($order, $item, $newQty) {
            $oldQty = (int) $item->quantity;

            // Stock follows the difference: fewer units ordered = stock back.
            if ($item->product_id) {
                $diff = $oldQty - $newQty;
                if ($diff > 0) {
                    Product::whereKey($item->product_id)->increment('stock', $diff);
                } elseif ($diff < 0) {
                    Product::whereKey($item->product_id)->decrement('stock', abs($diff));
                }
            }

            $note = $newQty === 0
                ? sprintf('Item removed by admin: %s (was %d).', $item->product_name, $oldQty)
                : sprintf('Item quantity changed by admin: %s %d -> %d.', $item->product_name, $oldQty, $newQty);

            if ($newQty === 0) {
                $item->delete();
            } else {
                $this->recalculateLine($item, $newQty);
            }

            $this->recalculateOrder($order);

            OrderStatusHistory::create([
                'order_id'    => $order->id,
                'from_status' => $order->status,
                'to_status'   => $order->status,
                'note'        => $note,
                'changed_by'  => auth()->id(),
            ]);
        });

        $order->refresh()->load('items');

        return response()->json([
            'success' => true,
            'message' => $newQty === 0 ? 'Item removed.' : 'Item quantity updated.',
            'totals'  => [
                'items_count'  => (int) $order->items_count,
                'subtotal'     => (float) $order->subtotal,
                'tax_amount'   => (float) $order->tax_amount,
                'total_amount' => (float) $order->total_amount,
                'paid_amount'  => (float) $order->paid_amount,
                'due_amount'   => $order->dueAmount(),
            ],
        ]);
    }

    /** Re-price one line from its own stored unit price and GST rate. */
    private function recalculateLine(OrderItem $item, int $qty): void
    {
        $unit  = (float) $item->unit_price;
        $gross = round($unit * $qty, 2);
        $rate  = (float) $item->gst_percentage;

        if ($item->tax_type === 'inclusive' && $rate > 0) {
            $taxable = round($gross / (1 + $rate / 100), 2);
            $gst     = round($gross - $taxable, 2);
            $total   = $gross;
        } elseif ($item->tax_type === 'exclusive' && $rate > 0) {
            $taxable = $gross;
            $gst     = round($gross * $rate / 100, 2);
            $total   = round($taxable + $gst, 2);
        } else {
            $taxable = $gross;
            $gst     = 0.0;
            $total   = $gross;
        }

        $item->update([
            'quantity'       => $qty,
            'taxable_amount' => $taxable,
            'gst_amount'     => $gst,
            'line_total'     => $total,
        ]);
    }

    /** Roll the remaining lines back up into the order totals. */
    private function recalculateOrder(Order $order): void
    {
        $items = OrderItem::where('order_id', $order->id)->get();

        $itemsCount = 0;
        $mrpTotal = $subtotal = $tax = $discount = 0.0;

        foreach ($items as $i) {
            $qty         = (int) $i->quantity;
            $itemsCount += $qty;
            $mrpTotal   += (float) $i->mrp * $qty;
            $subtotal   += (float) $i->taxable_amount;
            $tax        += (float) $i->gst_amount;
            $discount   += max(0, (float) $i->mrp - (float) $i->unit_price) * $qty;
        }

        $shipping = (float) $order->shipping_charge;

        $order->update([
            'items_count'     => $itemsCount,
            'mrp_total'       => round($mrpTotal, 2),
            'subtotal'        => round($subtotal, 2),
            'tax_amount'      => round($tax, 2),
            'discount_amount' => round($discount, 2),
            'total_amount'    => round($subtotal + $tax + $shipping, 2),
        ]);
    }

    public function getData(Request $request)
    {
        if (isset($request->order[0]['column'])) {
            $request->order_column = $request->order[0]['column'];
            $request->order_dir    = $request->order[0]['dir'];
        }

        $records = (new Order)->fetchData($request, $this->columns);

        if ($request->filled('payment_status')) {
            $records->where('orders.payment_status', $request->payment_status);
        }

        $total  = $records->get();
        $orders = isset($request->start)
            ? $records->offset($request->start)->limit($request->length)->get()
            : $records->get();

        $statusColors = [
            'pending' => 'warning', 'confirmed' => 'info', 'processing' => 'info',
            'shipped' => 'primary', 'delivered' => 'success', 'cancelled' => 'danger',
        ];
        $payColors = [
            'pending' => 'warning', 'partial' => 'info', 'paid' => 'success',
            'failed' => 'danger', 'refunded' => 'dark',
        ];

        $result = [];
        $i = ($request->start ?? 0) + 1;

        foreach ($orders as $o) {
            $sc = $statusColors[$o->status] ?? 'dark';
            $pc = $payColors[$o->payment_status] ?? 'dark';
            $due = $o->dueAmount();

            $result[] = [
                'srno'         => $i++,
                'order_number' => '<span class="fw-bold text-dark">' . e($o->order_number) . '</span>',
                'customer'     => e($o->shipping_name ?: ($o->user->phone_no ?? '-'))
                                  . '<div class="fs-11 text-muted">' . e($o->shipping_phone ?: '-') . '</div>',
                'store'        => e($o->store->name ?? '-'),
                'items'        => (int) $o->items_count,
                'total'        => '₹' . number_format((float) $o->total_amount, 2)
                                  . ($due > 0 ? '<div class="fs-11 text-danger">Due ₹' . number_format($due, 2) . '</div>'
                                              : '<div class="fs-11 text-success">Paid</div>'),
                'status'       => '<span class="badge bg-soft-' . $sc . ' text-' . $sc . '">' . ucfirst($o->status) . '</span>',
                'payment'      => '<span class="badge bg-soft-' . $pc . ' text-' . $pc . '">' . ucfirst($o->payment_status) . '</span>'
                                  . '<div class="fs-11 text-muted">' . strtoupper(str_replace('_', ' ', $o->payment_method)) . '</div>',
                'created_at'   => dateFormat($o->created_at),
                'actions'      => '<div class="hstack gap-2 justify-content-end">'
                    . '<a href="' . route('orders.show', $o->id) . '" class="avatar-text avatar-md" title="View / Manage">'
                    . '<i class="feather feather-eye"></i></a></div>',
            ];
        }

        return json_encode([
            'data'            => $result,
            'recordsTotal'    => count($total),
            'recordsFiltered' => count($total),
        ]);
    }

    /** Thumbnail URL for an order item image. */
    public static function itemImage(?string $path): ?string
    {
        return ProductImage::urlFor($path);
    }
}

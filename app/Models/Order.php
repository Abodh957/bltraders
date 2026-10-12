<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'paid_amount'  => 'decimal:2',
        'placed_at'    => 'datetime',
        'cancelled_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    /** Statuses a customer is still allowed to cancel from. */
    public const CANCELLABLE = ['pending', 'confirmed'];

    /** Payment methods the admin can record against an order. */
    public const PAYMENT_METHODS = ['cod', 'online', 'cash', 'upi', 'bank_transfer', 'cheque', 'card'];

    /** Payment states. "partial" means some money is in, but not all of it. */
    public const PAYMENT_STATUSES = ['pending', 'partial', 'paid', 'failed', 'refunded'];

    /** Items can only be edited while the order has not shipped. */
    public const ITEMS_EDITABLE = ['pending', 'confirmed', 'processing'];

    /** Allowed forward transitions, used by both API and admin. */
    public const FLOW = [
        'pending'    => ['confirmed', 'cancelled'],
        'confirmed'  => ['processing', 'cancelled'],
        'processing' => ['shipped', 'cancelled'],
        'shipped'    => ['delivered'],
        'delivered'  => [],
        'cancelled'  => [],
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function deliveryAddress()
    {
        // withTrashed: the customer may have removed the address after ordering.
        return $this->belongsTo(DeliveryAddress::class)->withTrashed();
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusHistories()
    {
        return $this->hasMany(OrderStatusHistory::class)->orderBy('id');
    }

    public function isCancellable(): bool
    {
        return in_array($this->status, self::CANCELLABLE, true);
    }

    public function itemsEditable(): bool
    {
        return in_array($this->status, self::ITEMS_EDITABLE, true);
    }

    /** What is still owed on this order. Never negative. */
    public function dueAmount(): float
    {
        return max(0, round((float) $this->total_amount - (float) $this->paid_amount, 2));
    }

    /**
     * Payment status worked out from the money actually received. The admin can
     * still override it manually (failed / refunded are set by hand).
     */
    public function derivedPaymentStatus(): string
    {
        $paid  = (float) $this->paid_amount;
        $total = (float) $this->total_amount;

        if ($paid <= 0)       return 'pending';
        if ($paid + 0.01 >= $total) return 'paid';

        return 'partial';
    }

    /**
     * Generate a collision-safe order number: BL-YYYYMMDD-000123
     * Must be called inside the same transaction that creates the order.
     */
    public static function generateOrderNumber(): string
    {
        $prefix = 'BL-' . date('Ymd') . '-';

        $last = static::withTrashed()
            ->where('order_number', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->lockForUpdate()
            ->value('order_number');

        $next = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix . str_pad((string) $next, 6, '0', STR_PAD_LEFT);
    }

    public function fetchData($request, $columns)
    {
        $query = Order::with(['user', 'store', 'shop'])->where('orders.id', '!=', '');

        if (!empty($request->status)) {
            $query->where('orders.status', $request->status);
        }
        if (!empty($request->store_id)) {
            $query->where('orders.store_id', $request->store_id);
        }
        if (isset($request->from_date)) {
            $query->whereRaw('DATE_FORMAT(orders.created_at, "%Y-%m-%d") >= "' . date("Y-m-d", strtotime($request->from_date)) . '"');
        }
        if (isset($request->end_date)) {
            $query->whereRaw('DATE_FORMAT(orders.created_at, "%Y-%m-%d") <= "' . date("Y-m-d", strtotime($request->end_date)) . '"');
        }
        if (isset($request['search']['value']) && !empty($request['search']['value'])) {
            $searchValue = $request['search']['value'];
            $query->where(function ($q) use ($searchValue) {
                $q->where('orders.order_number', 'like', '%' . $searchValue . '%')
                    ->orWhere('orders.shipping_name', 'like', '%' . $searchValue . '%')
                    ->orWhere('orders.shipping_phone', 'like', '%' . $searchValue . '%')
                    ->orWhereHas('user', function ($q2) use ($searchValue) {
                        $q2->where('phone_no', 'like', '%' . $searchValue . '%');
                    });
            });
        }

        if (isset($request->order_column)) {
            $query->orderBy($columns[$request->order_column], $request->order_dir);
        } else {
            $query->orderBy('orders.created_at', 'desc');
        }

        return $query;
    }
}

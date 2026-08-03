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
        'placed_at'    => 'datetime',
        'cancelled_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    /** Statuses a customer is still allowed to cancel from. */
    public const CANCELLABLE = ['pending', 'confirmed'];

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

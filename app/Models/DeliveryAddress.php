<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeliveryAddress extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    /** How many saved addresses one customer may keep. */
    public const MAX_PER_USER = 20;

    public const TYPES = ['home', 'shop', 'office', 'other'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /** One-line address, used for the order snapshot. */
    public function fullAddress(): string
    {
        return collect([$this->address_line1, $this->address_line2, $this->landmark])
            ->filter(fn($part) => filled($part))
            ->implode(', ');
    }

    /** The shipping_* columns an order stores when placed against this address. */
    public function toShippingSnapshot(): array
    {
        return [
            'shipping_name'    => $this->name,
            'shipping_phone'   => $this->phone,
            'shipping_address' => $this->fullAddress(),
            'shipping_city'    => $this->city,
            'shipping_state'   => $this->state,
            'shipping_country' => $this->country,
            'shipping_pincode' => $this->pincode,
        ];
    }
}

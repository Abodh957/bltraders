<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Invoice {{ $order->order_number }}</title>
<style>
    /* dompdf renders CSS 2.1 + a little 3 — no flexbox/grid, so layout is tables. */
    @page { margin: 28px 30px 60px 30px; }
    * { font-family: "DejaVu Sans", sans-serif; }
    body { font-size: 10px; color: #1b2436; margin: 0; }
    table { border-collapse: collapse; width: 100%; }
    .muted { color: #64748b; }
    .right { text-align: right; }
    .center { text-align: center; }
    .bold { font-weight: bold; }

    .brand-bar { height: 5px; background: #f97316; margin-bottom: 14px; }

    .head td { vertical-align: top; }
    .seller-name { font-size: 18px; font-weight: bold; color: #1b2436; margin: 0 0 3px 0; }
    .doc-title { font-size: 20px; font-weight: bold; color: #f97316; letter-spacing: 1px; margin: 0 0 6px 0; }
    .meta td { padding: 1px 0; }
    .meta .k { color: #64748b; padding-right: 8px; }

    .badge { display: inline-block; padding: 2px 7px; border-radius: 3px; font-size: 9px; font-weight: bold; text-transform: uppercase; }
    .b-pending    { background: #fff4e5; color: #b45309; }
    .b-confirmed, .b-processing { background: #e0f2fe; color: #0369a1; }
    .b-shipped    { background: #ede9fe; color: #6d28d9; }
    .b-delivered, .b-paid { background: #dcfce7; color: #15803d; }
    .b-cancelled, .b-failed { background: #fee2e2; color: #b91c1c; }
    .b-refunded   { background: #f1f5f9; color: #475569; }

    .box { border: 1px solid #e5e7eb; }
    .box-h { background: #f8fafc; border-bottom: 1px solid #e5e7eb; padding: 6px 9px; font-size: 9px; font-weight: bold; color: #64748b; text-transform: uppercase; letter-spacing: .5px; }
    .box-b { padding: 8px 9px; line-height: 1.5; }

    .items th { background: #1b2436; color: #fff; font-size: 9px; font-weight: bold; padding: 7px 5px; text-align: left; }
    .items th.right { text-align: right; }
    .items td { padding: 7px 5px; border-bottom: 1px solid #eef0f4; vertical-align: top; }
    .items tr.alt td { background: #fafbfc; }
    .item-name { font-weight: bold; }
    .item-sub { color: #64748b; font-size: 8.5px; }

    .gst th { background: #f8fafc; color: #64748b; font-size: 8.5px; padding: 5px; text-align: left; border-bottom: 1px solid #e5e7eb; }
    .gst th.right { text-align: right; }
    .gst td { padding: 5px; border-bottom: 1px solid #f1f5f9; }

    .totals td { padding: 4px 0; }
    .totals .k { color: #64748b; }
    .grand td { border-top: 2px solid #1b2436; padding-top: 7px; font-size: 13px; font-weight: bold; }
    .grand .amt { color: #f97316; }

    .words { background: #fff7ed; border-left: 3px solid #f97316; padding: 7px 10px; margin-top: 12px; }

    .stamp { position: absolute; top: 250px; left: 110px; font-size: 80px; font-weight: bold; color: #dc2626;
             opacity: 0.12; transform: rotate(-25deg); letter-spacing: 6px; }

    .footer { position: fixed; bottom: -40px; left: 0; right: 0; text-align: center; font-size: 8.5px; color: #94a3b8;
              border-top: 1px solid #e5e7eb; padding-top: 6px; }
</style>
</head>
<body>

@if($order->status === 'cancelled')
    <div class="stamp">CANCELLED</div>
@endif

<div class="brand-bar"></div>

{{-- Seller + invoice meta --}}
<table class="head">
    <tr>
        <td style="width: 58%;">
            <table>
                <tr>
                    @if($logo)
                        <td style="width: 62px; vertical-align: top;">
                            <img src="{{ $logo }}" style="width: 54px; height: 54px;" alt="">
                        </td>
                    @endif
                    <td style="vertical-align: top;">
                        <p class="seller-name">{{ $seller['name'] }}</p>
                        @if(!empty($seller['address']))<div class="muted">{{ $seller['address'] }}</div>@endif
                        @if(!empty($seller['phone']))<div class="muted">Phone: {{ $seller['phone'] }}</div>@endif
                        @if(!empty($seller['email']))<div class="muted">Email: {{ $seller['email'] }}</div>@endif
                        @if(!empty($seller['gstin']))<div><span class="muted">GSTIN:</span> <span class="bold">{{ $seller['gstin'] }}</span></div>@endif
                    </td>
                </tr>
            </table>
        </td>
        <td style="width: 42%;" class="right">
            <p class="doc-title">TAX INVOICE</p>
            <table class="meta" style="width: auto; margin-left: auto;">
                <tr><td class="k right">Invoice No.</td><td class="bold right">{{ $order->order_number }}</td></tr>
                <tr><td class="k right">Date</td><td class="right">{{ $invoiceDate }}</td></tr>
                <tr><td class="k right">Payment</td><td class="right">{{ strtoupper($order->payment_method) }}
                    <span class="badge b-{{ $order->payment_status }}">{{ $order->payment_status }}</span></td></tr>
                <tr><td class="k right">Order Status</td><td class="right">
                    <span class="badge b-{{ $order->status }}">{{ $order->status }}</span></td></tr>
            </table>
        </td>
    </tr>
</table>

{{-- Billed to + store --}}
<table style="margin-top: 16px;">
    <tr>
        <td style="width: 58%; vertical-align: top; padding-right: 10px;">
            <div class="box">
                <div class="box-h">Billed / Ship To</div>
                <div class="box-b">
                    <div class="bold" style="font-size: 11px;">{{ $order->shipping_name ?: '-' }}</div>
                    @if($order->shop && $order->shop->shop_name && $order->shop->shop_name !== $order->shipping_name)
                        <div>{{ $order->shop->shop_name }}</div>
                    @endif
                    <div>{{ $order->shipping_address }}</div>
                    <div>
                        {{ collect([$order->shipping_city, $order->shipping_state])->filter()->implode(', ') }}
                        @if($order->shipping_pincode) - {{ $order->shipping_pincode }} @endif
                    </div>
                    @if($order->shipping_country)<div>{{ $order->shipping_country }}</div>@endif
                    @if($order->shipping_phone)<div class="muted">Phone: {{ $order->shipping_phone }}</div>@endif
                </div>
            </div>
        </td>
        <td style="width: 42%; vertical-align: top;">
            <div class="box">
                <div class="box-h">Order Details</div>
                <div class="box-b">
                    <table class="meta">
                        <tr><td class="k">Store</td><td class="bold">{{ $order->store->name ?? '-' }}</td></tr>
                        <tr><td class="k">Items</td><td>{{ (int) $order->items_count }}</td></tr>
                        @if($order->delivered_at)
                            <tr><td class="k">Delivered</td><td>{{ $order->delivered_at->format('d M Y') }}</td></tr>
                        @endif
                        @if($order->cancelled_at)
                            <tr><td class="k">Cancelled</td><td>{{ $order->cancelled_at->format('d M Y') }}</td></tr>
                        @endif
                        @if($order->notes)
                            <tr><td class="k">Notes</td><td>{{ $order->notes }}</td></tr>
                        @endif
                    </table>
                </div>
            </div>
        </td>
    </tr>
</table>

{{-- Line items --}}
<table class="items" style="margin-top: 16px;">
    <thead>
        <tr>
            <th style="width: 4%;">#</th>
            <th style="width: 30%;">Item</th>
            <th class="right" style="width: 6%;">Qty</th>
            <th class="right" style="width: 10%;">MRP</th>
            <th class="right" style="width: 10%;">Rate</th>
            <th class="right" style="width: 12%;">Taxable</th>
            <th class="right" style="width: 7%;">GST</th>
            <th class="right" style="width: 9%;">GST Amt</th>
            <th class="right" style="width: 12%;">Amount</th>
        </tr>
    </thead>
    <tbody>
        @foreach($items as $i => $item)
            <tr class="{{ $i % 2 ? 'alt' : '' }}">
                <td>{{ $i + 1 }}</td>
                <td>
                    <div class="item-name">{{ $item->product_name }}</div>
                    @php
                        $sub = collect([
                            $item->product_sku ? 'SKU: ' . $item->product_sku : null,
                            $item->color_name ? 'Colour: ' . $item->color_name : null,
                            $item->tax_type === 'inclusive' ? 'GST incl.' : null,
                        ])->filter()->implode('  |  ');
                    @endphp
                    @if($sub)<div class="item-sub">{{ $sub }}</div>@endif
                </td>
                <td class="right">{{ (int) $item->quantity }}</td>
                <td class="right">&#8377;{{ number_format((float) $item->mrp, 2) }}</td>
                <td class="right">&#8377;{{ number_format((float) $item->unit_price, 2) }}</td>
                <td class="right">&#8377;{{ number_format((float) $item->taxable_amount, 2) }}</td>
                <td class="right">{{ rtrim(rtrim(number_format((float) $item->gst_percentage, 2), '0'), '.') }}%</td>
                <td class="right">&#8377;{{ number_format((float) $item->gst_amount, 2) }}</td>
                <td class="right bold">&#8377;{{ number_format((float) $item->line_total, 2) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

{{-- GST breakup + totals --}}
<table style="margin-top: 14px;">
    <tr>
        <td style="width: 55%; vertical-align: top; padding-right: 18px;">
            @if(count($gstBreakup))
                <div class="box">
                    <div class="box-h">GST Summary</div>
                    <table class="gst">
                        <tr>
                            <th>GST Rate</th>
                            <th class="right">Taxable Value</th>
                            <th class="right">GST Amount</th>
                        </tr>
                        @foreach($gstBreakup as $slab)
                            <tr>
                                <td>{{ rtrim(rtrim(number_format($slab['gst_percentage'], 2), '0'), '.') }}%</td>
                                <td class="right">&#8377;{{ number_format($slab['taxable_amount'], 2) }}</td>
                                <td class="right">&#8377;{{ number_format($slab['gst_amount'], 2) }}</td>
                            </tr>
                        @endforeach
                    </table>
                </div>
            @endif
        </td>
        <td style="width: 45%; vertical-align: top;">
            <table class="totals">
                <tr><td class="k">MRP Total</td><td class="right">&#8377;{{ number_format((float) $order->mrp_total, 2) }}</td></tr>
                @if((float) $order->discount_amount > 0)
                    <tr><td class="k">Discount</td><td class="right" style="color:#15803d;">- &#8377;{{ number_format((float) $order->discount_amount, 2) }}</td></tr>
                @endif
                <tr><td class="k">Taxable Value</td><td class="right">&#8377;{{ number_format((float) $order->subtotal, 2) }}</td></tr>
                <tr><td class="k">GST</td><td class="right">&#8377;{{ number_format((float) $order->tax_amount, 2) }}</td></tr>
                <tr><td class="k">Shipping</td><td class="right">
                    {{ (float) $order->shipping_charge > 0 ? '₹' . number_format((float) $order->shipping_charge, 2) : 'Free' }}</td></tr>
                <tr class="grand"><td>Grand Total</td><td class="right amt">&#8377;{{ number_format((float) $order->total_amount, 2) }}</td></tr>
            </table>
        </td>
    </tr>
</table>

<div class="words">
    <span class="muted">Amount in words:</span> <span class="bold">{{ $amountWords }}</span>
</div>

<div class="footer">
    This is a computer generated invoice and does not require a signature.
    &nbsp;|&nbsp; {{ $seller['name'] }} &nbsp;|&nbsp; {{ $order->order_number }}
</div>

</body>
</html>

@extends('admin.layouts.app')
@section('content')
@php
    $statusColors = ['pending'=>'warning','confirmed'=>'info','processing'=>'info',
                     'shipped'=>'primary','delivered'=>'success','cancelled'=>'danger'];
    $payColors    = ['pending'=>'warning','partial'=>'info','paid'=>'success',
                     'failed'=>'danger','refunded'=>'dark'];
    $sc   = $statusColors[$order->status] ?? 'dark';
    $pc   = $payColors[$order->payment_status] ?? 'dark';
    $next = \App\Models\Order::FLOW[$order->status] ?? [];
    $due  = $order->dueAmount();
@endphp
<main class="nxl-container">
    <div class="nxl-content">
        <div class="page-header">
            <div class="page-header-left d-flex align-items-center">
                <div class="page-header-title"><h5 class="m-b-10">{{ $order->order_number }}</h5></div>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('orders.index') }}">Orders</a></li>
                    <li class="breadcrumb-item">Detail</li>
                </ul>
            </div>
            <div class="page-header-right ms-auto">
                <div class="page-header-right-items">
                    <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                        <a href="{{ route('orders.index') }}" class="btn btn-secondary">
                            <i class="feather-arrow-left me-2"></i><span>Back</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="main-content" data-order="{{ $order->id }}">
            <div class="row">

                {{-- Summary --}}
                <div class="col-lg-12">
                    <div class="card stretch stretch-full">
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-lg-3 col-md-6">
                                    <p class="fs-11 text-muted text-uppercase mb-1">Order Status</p>
                                    <span class="badge bg-soft-{{ $sc }} text-{{ $sc }} fs-12" id="statusBadge">{{ ucfirst($order->status) }}</span>
                                    <p class="fs-11 text-muted mb-0 mt-2">Placed {{ $order->placed_at ? dateFormat($order->placed_at) : dateFormat($order->created_at) }}</p>
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <p class="fs-11 text-muted text-uppercase mb-1">Payment</p>
                                    <span class="badge bg-soft-{{ $pc }} text-{{ $pc }} fs-12" id="payBadge">{{ ucfirst($order->payment_status) }}</span>
                                    <p class="fs-11 text-muted mb-0 mt-2">{{ strtoupper(str_replace('_',' ',$order->payment_method)) }}</p>
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <p class="fs-11 text-muted text-uppercase mb-1">Order Total</p>
                                    <h4 class="mb-0" id="grandTotal">₹{{ number_format((float) $order->total_amount, 2) }}</h4>
                                    <p class="fs-11 text-muted mb-0 mt-1"><span id="itemsCount">{{ (int) $order->items_count }}</span> item(s)</p>
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <p class="fs-11 text-muted text-uppercase mb-1">Received / Due</p>
                                    <h4 class="mb-0 text-success" id="paidAmount">₹{{ number_format((float) $order->paid_amount, 2) }}</h4>
                                    <p class="fs-11 mb-0 mt-1 {{ $due > 0 ? 'text-danger' : 'text-success' }}" id="dueAmount">
                                        {{ $due > 0 ? 'Due ₹' . number_format($due, 2) : 'Nothing due' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Customer + shipping --}}
                <div class="col-lg-6">
                    <div class="card stretch stretch-full">
                        <div class="card-header"><h5 class="card-title"><i class="feather-user me-2"></i>Customer & Delivery</h5></div>
                        <div class="card-body">
                            <table class="table table-hover mb-0">
                                <tr><td class="fw-semibold text-dark" style="width:35%">Customer</td>
                                    <td>{{ $order->shipping_name ?: '-' }}
                                        @if($order->user)
                                            <a href="{{ route('customers.show', $order->user->id) }}" class="fs-11 ms-1">(profile)</a>
                                        @endif
                                    </td></tr>
                                <tr><td class="fw-semibold text-dark">Phone</td><td>{{ $order->shipping_phone ?: '-' }}</td></tr>
                                <tr><td class="fw-semibold text-dark">Shop</td><td>{{ $order->shop->shop_name ?? '-' }}</td></tr>
                                <tr><td class="fw-semibold text-dark">Store</td><td>{{ $order->store->name ?? '-' }}</td></tr>
                                <tr><td class="fw-semibold text-dark">Address</td>
                                    <td>{{ $order->shipping_address }}<br>
                                        {{ collect([$order->shipping_city,$order->shipping_state])->filter()->implode(', ') }}
                                        @if($order->shipping_pincode) - {{ $order->shipping_pincode }} @endif
                                    </td></tr>
                                @if($order->notes)
                                    <tr><td class="fw-semibold text-dark">Notes</td><td>{{ $order->notes }}</td></tr>
                                @endif
                                @if($order->cancel_reason)
                                    <tr><td class="fw-semibold text-dark">Cancel reason</td><td class="text-danger">{{ $order->cancel_reason }}</td></tr>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Status + payment forms --}}
                <div class="col-lg-6">
                    <div class="card stretch stretch-full">
                        <div class="card-header"><h5 class="card-title"><i class="feather-truck me-2"></i>Update Status</h5></div>
                        <div class="card-body">
                            @if(count($next))
                                <div class="row g-2 align-items-end">
                                    <div class="col-lg-5">
                                        <label class="form-label">Next status</label>
                                        <select id="newStatus" class="form-control">
                                            @foreach($next as $s)
                                                <option value="{{ $s }}">{{ ucfirst($s) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-lg-5">
                                        <label class="form-label">Note (optional)</label>
                                        <input type="text" id="statusNote" class="form-control" placeholder="e.g. packed today">
                                    </div>
                                    <div class="col-lg-2">
                                        <button class="btn btn-primary w-100" id="statusBtn">Update</button>
                                    </div>
                                </div>
                                <p class="fs-11 text-muted mb-0 mt-2">Cancelling an order returns its stock automatically.</p>
                            @else
                                <p class="text-muted mb-0">This order is <b>{{ $order->status }}</b> — there is no next status.</p>
                            @endif

                            <hr>
                            <h6 class="mb-3"><i class="feather-credit-card me-2"></i>Payment</h6>
                            <div class="row g-2">
                                <div class="col-lg-4">
                                    <label class="form-label">Method</label>
                                    <select id="payMethod" class="form-control">
                                        @foreach(\App\Models\Order::PAYMENT_METHODS as $m)
                                            <option value="{{ $m }}" {{ $order->payment_method === $m ? 'selected' : '' }}>
                                                {{ strtoupper(str_replace('_',' ',$m)) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-4">
                                    <label class="form-label">Amount received (₹)</label>
                                    <input type="number" step="0.01" min="0" id="payAmount" class="form-control"
                                           value="{{ number_format((float) $order->paid_amount, 2, '.', '') }}">
                                </div>
                                <div class="col-lg-4">
                                    <label class="form-label">Payment status</label>
                                    <select id="payStatus" class="form-control">
                                        <option value="">Auto (from amount)</option>
                                        @foreach(\App\Models\Order::PAYMENT_STATUSES as $s)
                                            <option value="{{ $s }}" {{ $order->payment_status === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-9">
                                    <label class="form-label">Note (optional)</label>
                                    <input type="text" id="payNote" class="form-control"
                                           value="{{ $order->payment_note }}" placeholder="e.g. UPI ref 1234">
                                </div>
                                <div class="col-lg-3 d-flex align-items-end">
                                    <button class="btn btn-primary w-100" id="payBtn">Save Payment</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Items --}}
                <div class="col-lg-12">
                    <div class="card stretch stretch-full">
                        <div class="card-header">
                            <h5 class="card-title"><i class="feather-package me-2"></i>Order Items</h5>
                            @if(!$order->itemsEditable())
                                <span class="badge bg-soft-dark text-dark">Order {{ $order->status }} — items locked</span>
                            @endif
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0" id="itemsTable">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Rate</th>
                                            <th style="width:170px">Qty</th>
                                            <th>GST</th>
                                            <th class="text-end">Line Total</th>
                                            @if($order->itemsEditable())<th class="text-end">Action</th>@endif
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($order->items as $item)
                                            <tr data-item="{{ $item->id }}">
                                                <td>
                                                    <div class="fw-semibold text-dark">{{ $item->product_name }}</div>
                                                    <div class="fs-11 text-muted">
                                                        {{ collect([$item->product_sku ? 'SKU: '.$item->product_sku : null,
                                                                    $item->color_name ? 'Colour: '.$item->color_name : null])->filter()->implode('  |  ') }}
                                                    </div>
                                                </td>
                                                <td>₹{{ number_format((float) $item->unit_price, 2) }}</td>
                                                <td>
                                                    @if($order->itemsEditable())
                                                        <div class="input-group input-group-sm">
                                                            <input type="number" min="1" class="form-control qty-input"
                                                                   value="{{ (int) $item->quantity }}">
                                                            <button class="btn btn-light-brand qty-save" type="button">Save</button>
                                                        </div>
                                                    @else
                                                        {{ (int) $item->quantity }}
                                                    @endif
                                                </td>
                                                <td>{{ rtrim(rtrim(number_format((float) $item->gst_percentage, 2), '0'), '.') }}%
                                                    <div class="fs-11 text-muted">₹{{ number_format((float) $item->gst_amount, 2) }}</div>
                                                </td>
                                                <td class="text-end fw-semibold line-total">₹{{ number_format((float) $item->line_total, 2) }}</td>
                                                @if($order->itemsEditable())
                                                    <td class="text-end">
                                                        <a href="javascript:void(0)" class="avatar-text avatar-md text-danger item-remove" title="Remove">
                                                            <i class="feather feather-trash-2"></i></a>
                                                    </td>
                                                @endif
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer">
                            <div class="row">
                                <div class="col-lg-6"></div>
                                <div class="col-lg-6">
                                    <table class="table table-sm mb-0">
                                        <tr><td class="text-muted">Taxable</td><td class="text-end" id="sumSubtotal">₹{{ number_format((float) $order->subtotal, 2) }}</td></tr>
                                        <tr><td class="text-muted">GST</td><td class="text-end" id="sumTax">₹{{ number_format((float) $order->tax_amount, 2) }}</td></tr>
                                        <tr><td class="text-muted">Shipping</td><td class="text-end">₹{{ number_format((float) $order->shipping_charge, 2) }}</td></tr>
                                        <tr class="fw-bold"><td>Grand Total</td><td class="text-end" id="sumTotal">₹{{ number_format((float) $order->total_amount, 2) }}</td></tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Timeline --}}
                <div class="col-lg-12">
                    <div class="card stretch stretch-full">
                        <div class="card-header"><h5 class="card-title"><i class="feather-clock me-2"></i>History</h5></div>
                        <div class="card-body">
                            @forelse($order->statusHistories as $h)
                                <div class="hstack justify-content-between py-2 border-bottom">
                                    <div>
                                        <span class="fw-semibold text-dark">
                                            {{ $h->from_status ? ucfirst($h->from_status).' → ' : '' }}{{ ucfirst($h->to_status) }}
                                        </span>
                                        <div class="fs-12 text-muted">{{ $h->note }}</div>
                                    </div>
                                    <span class="fs-11 text-muted">{{ optional($h->created_at)->format('d M Y, h:i A') }}</span>
                                </div>
                            @empty
                                <p class="text-muted mb-0">No history yet.</p>
                            @endforelse
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    @push('script')
    <script>
        $(document).ready(function () {
            $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
            var orderId = $('.main-content').data('order');

            function toast(icon, title) {
                Swal.fire({ toast: true, position: 'top-end', showConfirmButton: false,
                            timer: 3000, timerProgressBar: true, icon: icon, title: title });
            }
            function fail(xhr) {
                toast('error', (xhr.responseJSON && xhr.responseJSON.message) || 'Something went wrong.');
            }
            function money(n) {
                return '₹' + Number(n).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }

            $('#statusBtn').on('click', function () {
                var status = $('#newStatus').val();
                if (!confirm('Change the order status to "' + status + '"?')) return;
                $.post('{{ url("admin/orders") }}/' + orderId + '/status',
                    { status: status, note: $('#statusNote').val() })
                    .done(function (res) { toast('success', res.message); setTimeout(function(){ location.reload(); }, 900); })
                    .fail(fail);
            });

            $('#payBtn').on('click', function () {
                $.post('{{ url("admin/orders") }}/' + orderId + '/payment', {
                    payment_method: $('#payMethod').val(),
                    paid_amount:    $('#payAmount').val(),
                    payment_status: $('#payStatus').val(),
                    payment_note:   $('#payNote').val()
                }).done(function (res) {
                    toast('success', res.message);
                    setTimeout(function(){ location.reload(); }, 900);
                }).fail(fail);
            });

            function saveQty(row, qty) {
                $.post('{{ url("admin/orders") }}/' + orderId + '/item',
                    { item_id: row.data('item'), quantity: qty })
                    .done(function (res) {
                        toast('success', res.message);
                        $('#itemsCount').text(res.totals.items_count);
                        $('#sumSubtotal').text(money(res.totals.subtotal));
                        $('#sumTax').text(money(res.totals.tax_amount));
                        $('#sumTotal').text(money(res.totals.total_amount));
                        $('#grandTotal').text(money(res.totals.total_amount));
                        setTimeout(function(){ location.reload(); }, 900);
                    }).fail(fail);
            }

            $(document).on('click', '.qty-save', function () {
                var row = $(this).closest('tr');
                var qty = parseInt(row.find('.qty-input').val(), 10);
                if (!qty || qty < 1) { toast('error', 'Quantity must be 1 or more.'); return; }
                saveQty(row, qty);
            });

            $(document).on('click', '.item-remove', function () {
                var row = $(this).closest('tr');
                if (!confirm('Remove this item from the order?')) return;
                saveQty(row, 0);
            });
        });
    </script>
    @endpush

    @include('admin.layouts.footer')
</main>
@endsection

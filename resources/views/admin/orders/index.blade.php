@extends('admin.layouts.app')
@section('content')
<main class="nxl-container">
    <div class="nxl-content">
        <div class="page-header">
            <div class="page-header-left d-flex align-items-center">
                <div class="page-header-title"><h5 class="m-b-10">Orders</h5></div>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item">Orders</li>
                </ul>
            </div>
        </div>

        <div class="main-content">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card stretch stretch-full">
                        {{-- .card-header is flex + space-between, so the filter grid lives
                             in its own block instead — otherwise the columns overflow the card. --}}
                        <div class="card-body border-bottom py-3">
                            <div class="row g-3">
                                <div class="col-xl-3 col-lg-4 col-md-6">
                                    <label class="form-label fs-11 text-muted text-uppercase mb-1">Order Status</label>
                                    <select id="filterStatus" class="form-control form-control-sm">
                                        <option value="">All</option>
                                        @foreach(['pending','confirmed','processing','shipped','delivered','cancelled'] as $s)
                                            <option value="{{ $s }}">{{ ucfirst($s) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-xl-3 col-lg-4 col-md-6">
                                    <label class="form-label fs-11 text-muted text-uppercase mb-1">Payment Status</label>
                                    <select id="filterPayment" class="form-control form-control-sm">
                                        <option value="">All</option>
                                        @foreach(\App\Models\Order::PAYMENT_STATUSES as $s)
                                            <option value="{{ $s }}">{{ ucfirst($s) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-xl-2 col-lg-4 col-md-6">
                                    <label class="form-label fs-11 text-muted text-uppercase mb-1">Store</label>
                                    <select id="filterStore" class="form-control form-control-sm">
                                        <option value="">All</option>
                                        @foreach($stores as $store)
                                            <option value="{{ $store->id }}">{{ $store->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-xl-2 col-lg-4 col-md-6">
                                    <label class="form-label fs-11 text-muted text-uppercase mb-1">From</label>
                                    <input type="date" id="fromDate" class="form-control form-control-sm">
                                </div>
                                <div class="col-xl-2 col-lg-4 col-md-6">
                                    <label class="form-label fs-11 text-muted text-uppercase mb-1">To</label>
                                    <input type="date" id="endDate" class="form-control form-control-sm">
                                </div>
                                <div class="col-xl-12 col-lg-4 d-flex align-items-end">
                                    <button type="button" id="clearFilters" class="btn btn-sm btn-light-brand">
                                        <i class="feather-rotate-ccw me-1"></i>Clear filters
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover" id="OrdersTable" data-url="{{ route('orders.data') }}">
                                    <thead>
                                        <tr>
                                            <th>Sr.</th>
                                            <th>Order No.</th>
                                            <th>Customer</th>
                                            <th>Store</th>
                                            <th>Items</th>
                                            <th>Total</th>
                                            <th>Status</th>
                                            <th>Payment</th>
                                            <th>Date</th>
                                            <th class="text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
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

            var table = $('#OrdersTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: $('#OrdersTable').data('url'),
                    type: 'POST',
                    data: function (d) {
                        d.status         = $('#filterStatus').val();
                        d.payment_status = $('#filterPayment').val();
                        d.store_id       = $('#filterStore').val();
                        d.from_date      = $('#fromDate').val();
                        d.end_date       = $('#endDate').val();
                    }
                },
                paging: true,
                pageLength: 10,
                lengthChange: false,
                searching: true,
                order: [],
                columns: [
                    { data: 'srno',         name: 'orders.id',           orderable: false, searchable: false },
                    { data: 'order_number', name: 'orders.order_number' },
                    { data: 'customer',     name: 'customer',            orderable: false },
                    { data: 'store',        name: 'store',               orderable: false },
                    { data: 'items',        name: 'items',               orderable: false },
                    { data: 'total',        name: 'orders.total_amount' },
                    { data: 'status',       name: 'orders.status' },
                    { data: 'payment',      name: 'orders.payment_status' },
                    { data: 'created_at',   name: 'orders.created_at' },
                    { data: 'actions',      name: 'actions',             orderable: false, searchable: false }
                ]
            });

            $('#filterStatus, #filterPayment, #filterStore, #fromDate, #endDate').on('change', function () {
                table.ajax.reload();
            });

            $('#clearFilters').on('click', function () {
                $('#filterStatus, #filterPayment, #filterStore, #fromDate, #endDate').val('');
                table.ajax.reload();
            });
        });
    </script>
    @endpush

    @include('admin.layouts.footer')
</main>
@endsection

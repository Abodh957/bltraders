@extends('admin.layouts.app')
@section('content')
@php
    // Small helper so a 0-total never divides by zero.
    $pct = fn($part, $total) => $total > 0 ? round($part / $total * 100) : 0;
@endphp
<!--! ================================================================ !-->
<!--! [Start] Main Content !-->
<!--! ================================================================ !-->
<main class="nxl-container">
    <div class="nxl-content">
        <!-- [ page-header ] start -->
        <div class="page-header">
            <div class="page-header-left d-flex align-items-center">
                <div class="page-header-title">
                    <h5 class="m-b-10">Dashboard</h5>
                </div>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item">Dashboard</li>
                </ul>
            </div>
            <div class="page-header-right ms-auto">
                <div class="page-header-right-items">
                    <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                        <a href="{{ route('products.index') }}" class="btn btn-primary">
                            <i class="feather-package me-2"></i>
                            <span>Manage Products</span>
                        </a>
                    </div>
                </div>
                <div class="d-md-none d-flex align-items-center">
                    <a href="javascript:void(0)" class="page-header-right-open-toggle">
                        <i class="feather-align-right fs-20"></i>
                    </a>
                </div>
            </div>
        </div>
        <!-- [ page-header ] end -->
        <!-- [ Main Content ] start -->
        <div class="main-content">
            <div class="row">

                <!-- [Customers] start -->
                <div class="col-xxl-3 col-md-6">
                    <div class="card stretch stretch-full">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between mb-4">
                                <div class="d-flex gap-4 align-items-center">
                                    <div class="avatar-text avatar-lg bg-gray-200">
                                        <i class="feather-users"></i>
                                    </div>
                                    <div>
                                        <div class="fs-4 fw-bold text-dark"><span class="counter">{{ $stats['customers'] }}</span></div>
                                        <h3 class="fs-13 fw-semibold text-truncate-1-line">Total Customers</h3>
                                    </div>
                                </div>
                                <a href="{{ route('customers.index') }}"><i class="feather-more-vertical"></i></a>
                            </div>
                            <div class="pt-4">
                                <div class="d-flex align-items-center justify-content-between">
                                    <span class="fs-12 fw-medium text-muted text-truncate-1-line">Approved Shops</span>
                                    <div class="w-100 text-end">
                                        <span class="fs-12 text-dark">{{ $shops['approved'] }}/{{ $shops['total'] }}</span>
                                        <span class="fs-11 text-muted">({{ $pct($shops['approved'], $shops['total']) }}%)</span>
                                    </div>
                                </div>
                                <div class="progress mt-2 ht-3">
                                    <div class="progress-bar bg-primary" role="progressbar"
                                         style="width: {{ $pct($shops['approved'], $shops['total']) }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- [Customers] end -->

                <!-- [Orders] start -->
                <div class="col-xxl-3 col-md-6">
                    <div class="card stretch stretch-full">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between mb-4">
                                <div class="d-flex gap-4 align-items-center">
                                    <div class="avatar-text avatar-lg bg-gray-200">
                                        <i class="feather-shopping-cart"></i>
                                    </div>
                                    <div>
                                        <div class="fs-4 fw-bold text-dark"><span class="counter">{{ $orders['total'] }}</span></div>
                                        <h3 class="fs-13 fw-semibold text-truncate-1-line">Total Orders</h3>
                                    </div>
                                </div>
                                <a href="javascript:void(0);"><i class="feather-more-vertical"></i></a>
                            </div>
                            <div class="pt-4">
                                <div class="d-flex align-items-center justify-content-between">
                                    <span class="fs-12 fw-medium text-muted text-truncate-1-line">Delivered</span>
                                    <div class="w-100 text-end">
                                        <span class="fs-12 text-dark">{{ $orders['delivered'] }}/{{ $orders['total'] }}</span>
                                        <span class="fs-11 text-muted">({{ $pct($orders['delivered'], $orders['total']) }}%)</span>
                                    </div>
                                </div>
                                <div class="progress mt-2 ht-3">
                                    <div class="progress-bar bg-success" role="progressbar"
                                         style="width: {{ $pct($orders['delivered'], $orders['total']) }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- [Orders] end -->

                <!-- [Revenue] start -->
                <div class="col-xxl-3 col-md-6">
                    <div class="card stretch stretch-full">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between mb-4">
                                <div class="d-flex gap-4 align-items-center">
                                    <div class="avatar-text avatar-lg bg-gray-200">
                                        <i class="feather-trending-up"></i>
                                    </div>
                                    <div>
                                        <div class="fs-4 fw-bold text-dark">₹{{ number_format($orders['revenue'], 2) }}</div>
                                        <h3 class="fs-13 fw-semibold text-truncate-1-line">Total Revenue</h3>
                                    </div>
                                </div>
                                <a href="javascript:void(0);"><i class="feather-more-vertical"></i></a>
                            </div>
                            <div class="pt-4">
                                <div class="d-flex align-items-center justify-content-between">
                                    <span class="fs-12 fw-medium text-muted text-truncate-1-line">This Month</span>
                                    <div class="w-100 text-end">
                                        <span class="fs-12 text-dark">₹{{ number_format($orders['this_month'], 2) }}</span>
                                        <span class="fs-11 text-muted">({{ $pct($orders['this_month'], $orders['revenue']) }}%)</span>
                                    </div>
                                </div>
                                <div class="progress mt-2 ht-3">
                                    <div class="progress-bar bg-warning" role="progressbar"
                                         style="width: {{ $pct($orders['this_month'], $orders['revenue']) }}%"></div>
                                </div>
                            </div>
                            <p class="fs-11 text-muted mb-0 mt-2">Cancelled orders excluded</p>
                        </div>
                    </div>
                </div>
                <!-- [Revenue] end -->

                <!-- [Products] start -->
                <div class="col-xxl-3 col-md-6">
                    <div class="card stretch stretch-full">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between mb-4">
                                <div class="d-flex gap-4 align-items-center">
                                    <div class="avatar-text avatar-lg bg-gray-200">
                                        <i class="feather-package"></i>
                                    </div>
                                    <div>
                                        <div class="fs-4 fw-bold text-dark"><span class="counter">{{ $products['total'] }}</span></div>
                                        <h3 class="fs-13 fw-semibold text-truncate-1-line">Total Products</h3>
                                    </div>
                                </div>
                                <a href="{{ route('products.index') }}"><i class="feather-more-vertical"></i></a>
                            </div>
                            <div class="pt-4">
                                <div class="d-flex align-items-center justify-content-between">
                                    <span class="fs-12 fw-medium text-muted text-truncate-1-line">Active</span>
                                    <div class="w-100 text-end">
                                        <span class="fs-12 text-dark">{{ $products['active'] }}/{{ $products['total'] }}</span>
                                        <span class="fs-11 text-muted">({{ $pct($products['active'], $products['total']) }}%)</span>
                                    </div>
                                </div>
                                <div class="progress mt-2 ht-3">
                                    <div class="progress-bar bg-primary" role="progressbar"
                                         style="width: {{ $pct($products['active'], $products['total']) }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- [Products] end -->

                <!-- [Shop Requests] start -->
                <div class="col-xxl-4 col-lg-6">
                    <div class="card stretch stretch-full">
                        <div class="card-header">
                            <h5 class="card-title"><i class="feather-shopping-bag me-2"></i>Shop Requests</h5>
                            @if($shops['pending'] > 0)
                                <span class="badge bg-soft-warning text-warning">{{ $shops['pending'] }} pending</span>
                            @endif
                        </div>
                        <div class="card-body">
                            @foreach([
                                ['Pending',  $shops['pending'],  'warning'],
                                ['Approved', $shops['approved'], 'success'],
                                ['Rejected', $shops['rejected'], 'danger'],
                            ] as [$label, $count, $color])
                                <div class="mb-3">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <span class="fs-12 fw-medium text-dark">{{ $label }}</span>
                                        <span class="fs-12 text-muted">{{ $count }} ({{ $pct($count, $shops['total']) }}%)</span>
                                    </div>
                                    <div class="progress mt-2 ht-3">
                                        <div class="progress-bar bg-{{ $color }}" role="progressbar"
                                             style="width: {{ $pct($count, $shops['total']) }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                            <a href="{{ route('customers.index') }}" class="btn btn-light-brand w-100 mt-2">
                                <i class="feather-users me-2"></i>Manage Customers
                            </a>
                        </div>
                    </div>
                </div>
                <!-- [Shop Requests] end -->

                <!-- [Order Status] start -->
                <div class="col-xxl-4 col-lg-6">
                    <div class="card stretch stretch-full">
                        <div class="card-header">
                            <h5 class="card-title"><i class="feather-truck me-2"></i>Order Status</h5>
                        </div>
                        <div class="card-body">
                            @if($orders['total'] > 0)
                                @foreach(['pending'=>'warning','confirmed'=>'info','processing'=>'info','shipped'=>'primary','delivered'=>'success','cancelled'=>'danger'] as $st => $color)
                                    @php $c = $statusBreakdown[$st] ?? 0; @endphp
                                    <div class="hstack justify-content-between py-2 border-bottom">
                                        <span class="hstack gap-2">
                                            <i class="wd-8 ht-8 bg-{{ $color }} rounded-circle"></i>
                                            <span class="fs-12 fw-medium text-dark">{{ ucfirst($st) }}</span>
                                        </span>
                                        <span class="fs-12 text-muted">{{ $c }}</span>
                                    </div>
                                @endforeach
                            @else
                                <div class="text-center py-5">
                                    <i class="feather-truck fs-1 mb-3 text-muted d-block"></i>
                                    <p class="text-muted mb-0">No orders yet.</p>
                                    <p class="fs-11 text-muted">Orders placed from the app will appear here.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <!-- [Order Status] end -->

                <!-- [Catalogue] start -->
                <div class="col-xxl-4 col-lg-12">
                    <div class="card stretch stretch-full">
                        <div class="card-header">
                            <h5 class="card-title"><i class="feather-grid me-2"></i>Catalogue</h5>
                        </div>
                        <div class="card-body">
                            <div class="row text-center g-3">
                                @foreach([
                                    ['Stores', $catalogue['stores'], 'feather-home', route('stores.index')],
                                    ['Categories', $catalogue['categories'], 'feather-layers', route('categories.index')],
                                    ['Sub Categories', $catalogue['sub_categories'], 'feather-list', route('sub-categories.index')],
                                    ['Brands', $catalogue['brands'], 'feather-tag', route('brands.index')],
                                    ['Banners', $catalogue['banners'], 'feather-image', route('banners.index')],
                                    ['Colors', $catalogue['colors'], 'feather-droplet', route('colors.index')],
                                ] as [$label, $count, $icon, $link])
                                    <div class="col-4">
                                        <a href="{{ $link }}" class="d-block text-decoration-none">
                                            <div class="avatar-text avatar-lg bg-gray-200 mx-auto mb-2">
                                                <i class="{{ $icon }}"></i>
                                            </div>
                                            <h5 class="fs-16 fw-bold text-dark mb-0">{{ $count }}</h5>
                                            <p class="fs-11 text-muted mb-0">{{ $label }}</p>
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                <!-- [Catalogue] end -->

                <!-- [Recent Orders] start -->
                <div class="col-xxl-8">
                    <div class="card stretch stretch-full">
                        <div class="card-header">
                            <h5 class="card-title"><i class="feather-clock me-2"></i>Recent Orders</h5>
                        </div>
                        <div class="card-body p-0">
                            @if($recentOrders->count())
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th>Order No.</th>
                                                <th>Customer</th>
                                                <th>Store</th>
                                                <th>Amount</th>
                                                <th>Status</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($recentOrders as $o)
                                                @php
                                                    $c = ['pending'=>'warning','confirmed'=>'info','processing'=>'info',
                                                          'shipped'=>'primary','delivered'=>'success','cancelled'=>'danger'][$o->status] ?? 'dark';
                                                @endphp
                                                <tr>
                                                    <td class="fw-semibold text-dark">{{ $o->order_number }}</td>
                                                    <td>{{ $o->shipping_name ?: ($o->user->phone_no ?? '-') }}</td>
                                                    <td>{{ $o->store->name ?? '-' }}</td>
                                                    <td>₹{{ number_format((float) $o->total_amount, 2) }}</td>
                                                    <td><span class="badge bg-soft-{{ $c }} text-{{ $c }}">{{ ucfirst($o->status) }}</span></td>
                                                    <td>{{ $o->created_at ? dateFormat($o->created_at) : '-' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <i class="feather-shopping-cart fs-1 mb-3 text-muted d-block"></i>
                                    <p class="text-muted mb-0">No orders placed yet.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <!-- [Recent Orders] end -->

                <!-- [Stock Alert] start -->
                <div class="col-xxl-4">
                    <div class="card stretch stretch-full">
                        <div class="card-header">
                            <h5 class="card-title"><i class="feather-alert-triangle me-2"></i>Stock Alert</h5>
                            @if($products['out'] > 0)
                                <span class="badge bg-soft-danger text-danger">{{ $products['out'] }} out of stock</span>
                            @endif
                        </div>
                        <div class="card-body">
                            @if($lowStock->count())
                                @foreach($lowStock as $p)
                                    <div class="hstack justify-content-between py-2 border-bottom">
                                        <span class="fs-12 fw-medium text-dark text-truncate-1-line">{{ $p->name }}</span>
                                        <span class="badge bg-soft-{{ $p->stock <= 0 ? 'danger' : 'warning' }} text-{{ $p->stock <= 0 ? 'danger' : 'warning' }}">
                                            {{ $p->stock <= 0 ? 'Out of stock' : $p->stock . ' left' }}
                                        </span>
                                    </div>
                                @endforeach
                            @else
                                <div class="text-center py-5">
                                    <i class="feather-check-circle fs-1 mb-3 text-success d-block"></i>
                                    <p class="text-muted mb-0">All products are well stocked.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <!-- [Stock Alert] end -->

                <!-- [Top Selling] start -->
                <div class="col-xxl-6">
                    <div class="card stretch stretch-full">
                        <div class="card-header">
                            <h5 class="card-title"><i class="feather-award me-2"></i>Top Selling Products</h5>
                        </div>
                        <div class="card-body p-0">
                            @if($topProducts->count())
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead>
                                            <tr><th>#</th><th>Product</th><th>Units Sold</th><th>Revenue</th></tr>
                                        </thead>
                                        <tbody>
                                            @foreach($topProducts as $i => $p)
                                                <tr>
                                                    <td class="fw-semibold text-dark">{{ $i + 1 }}</td>
                                                    <td>{{ $p->name }}</td>
                                                    <td>{{ (int) $p->total_sold }}</td>
                                                    <td>₹{{ number_format((float) $p->revenue, 2) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <i class="feather-award fs-1 mb-3 text-muted d-block"></i>
                                    <p class="text-muted mb-0">No sales recorded yet.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <!-- [Top Selling] end -->

                <!-- [Recent Customers] start -->
                <div class="col-xxl-6">
                    <div class="card stretch stretch-full">
                        <div class="card-header">
                            <h5 class="card-title"><i class="feather-user-plus me-2"></i>Recent Customers</h5>
                            <a href="{{ route('customers.index') }}" class="fs-12 fw-medium text-primary">See All</a>
                        </div>
                        <div class="card-body p-0">
                            @if($recentCustomers->count())
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead>
                                            <tr><th>Shop</th><th>Phone</th><th>City</th><th>Status</th></tr>
                                        </thead>
                                        <tbody>
                                            @foreach($recentCustomers as $c)
                                                @php
                                                    $sc = ['pending'=>'warning','approved'=>'success','rejected'=>'danger'][$c->shop_status] ?? 'dark';
                                                @endphp
                                                <tr>
                                                    <td class="fw-semibold text-dark">{{ $c->shop_name ? ucfirst($c->shop_name) : '-' }}</td>
                                                    <td>{{ $c->phone_no ?: '-' }}</td>
                                                    <td>{{ $c->city ? ucfirst($c->city) : '-' }}</td>
                                                    <td>
                                                        @if($c->shop_status)
                                                            <span class="badge bg-soft-{{ $sc }} text-{{ $sc }}">{{ ucfirst($c->shop_status) }}</span>
                                                        @else
                                                            <span class="badge bg-soft-dark text-dark">No Shop</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <i class="feather-users fs-1 mb-3 text-muted d-block"></i>
                                    <p class="text-muted mb-0">No customers registered yet.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <!-- [Recent Customers] end -->

            </div>
        </div>
        <!-- [ Main Content ] end -->
    </div>

    @include('admin.layouts.footer')
    <!-- [ Footer ] end -->
</main>
<!--! ================================================================ !-->
<!--! [End] Main Content !-->
<!--! ================================================================ !-->
@include('admin.layouts.message')
@endsection

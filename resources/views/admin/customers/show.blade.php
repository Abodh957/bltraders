@extends('admin.layouts.app')

@section('content')
<main class="nxl-container">
    <div class="nxl-content">
        <div class="page-header">
            <div class="page-header-left d-flex align-items-center">
                <div class="page-header-title">
                    <h5 class="m-b-10">Customer Details</h5>
                </div>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('customers.index') }}">Customer</a></li>
                    <li class="breadcrumb-item">Details</li>
                </ul>
            </div>
            <div class="page-header-right ms-auto">
                <div class="page-header-right-items">
                    <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                        <a href="{{ route('customers.index') }}" class="btn btn-secondary">
                            <i class="feather-arrow-left me-2"></i>
                            <span>Back</span>
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

        <div class="main-content">
            <div class="row">

                {{-- Customer account --}}
                <div class="col-lg-6">
                    <div class="card stretch stretch-full">
                        <div class="card-header">
                            <h5 class="card-title"><i class="feather-user me-2"></i>Customer Information</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-hover mb-0">
                                <tbody>
                                    <tr>
                                        <td class="fw-semibold text-dark" style="width:40%">Customer ID</td>
                                        <td>#{{ $customer->id }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-semibold text-dark">Name</td>
                                        <td>{{ $customer->name ? ucfirst($customer->name) : '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-semibold text-dark">Phone No.</td>
                                        <td>{{ $customer->phone_no ?: '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-semibold text-dark">Email</td>
                                        <td>{{ $customer->email ?: '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-semibold text-dark">Registered On</td>
                                        <td>{{ $customer->created_at ? dateFormat($customer->created_at) : '-' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Shop --}}
                <div class="col-lg-6">
                    <div class="card stretch stretch-full">
                        <div class="card-header">
                            <h5 class="card-title"><i class="feather-shopping-bag me-2"></i>Shop Information</h5>
                            @if($shop)
                                @php
                                    $badge = ['pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger'][$shop->status] ?? 'dark';
                                @endphp
                                <span class="badge bg-soft-{{ $badge }} text-{{ $badge }}">{{ ucfirst($shop->status) }}</span>
                            @endif
                        </div>
                        <div class="card-body">
                            @if($shop)
                                <table class="table table-hover mb-0">
                                    <tbody>
                                        <tr>
                                            <td class="fw-semibold text-dark" style="width:40%">Shop Name</td>
                                            <td>{{ $shop->shop_name ? ucfirst($shop->shop_name) : '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-semibold text-dark">Address</td>
                                            <td>{{ $shop->shop_address ?: '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-semibold text-dark">City</td>
                                            <td>{{ $shop->city ? ucfirst($shop->city) : '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-semibold text-dark">State</td>
                                            <td>{{ $shop->state ? ucfirst($shop->state) : '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-semibold text-dark">Country</td>
                                            <td>{{ $shop->country ? ucfirst($shop->country) : '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-semibold text-dark">Pincode</td>
                                            <td>{{ $shop->pincode ?: '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-semibold text-dark">Shop Document</td>
                                            <td>
                                                @if($shop->shop_document)
                                                    <a href="{{ url('public/uploads/shop_docs/' . $shop->shop_document) }}"
                                                       target="_blank" class="btn btn-sm btn-light-brand">
                                                        <i class="feather-external-link me-1"></i> View Document
                                                    </a>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="fw-semibold text-dark">Requested On</td>
                                            <td>{{ $shop->created_at ? dateFormat($shop->created_at) : '-' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            @else
                                <div class="text-center py-5">
                                    <i class="feather-slash fs-1 mb-3 text-muted d-block"></i>
                                    <p class="text-muted mb-0">This customer has not created a shop yet.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Order summary --}}
                <div class="col-lg-12">
                    <div class="card stretch stretch-full">
                        <div class="card-header">
                            <h5 class="card-title"><i class="feather-shopping-cart me-2"></i>Orders</h5>
                        </div>
                        <div class="card-body">
                            <div class="row text-center mb-4">
                                <div class="col-6 col-lg-3 mb-3 mb-lg-0">
                                    <h4 class="mb-1">{{ $orderStats['total'] }}</h4>
                                    <p class="fs-11 text-muted text-uppercase mb-0">Total Orders</p>
                                </div>
                                <div class="col-6 col-lg-3 mb-3 mb-lg-0">
                                    <h4 class="mb-1 text-success">{{ $orderStats['delivered'] }}</h4>
                                    <p class="fs-11 text-muted text-uppercase mb-0">Delivered</p>
                                </div>
                                <div class="col-6 col-lg-3">
                                    <h4 class="mb-1 text-danger">{{ $orderStats['cancelled'] }}</h4>
                                    <p class="fs-11 text-muted text-uppercase mb-0">Cancelled</p>
                                </div>
                                <div class="col-6 col-lg-3">
                                    <h4 class="mb-1">{{ number_format((float) $orderStats['value'], 2) }}</h4>
                                    <p class="fs-11 text-muted text-uppercase mb-0">Total Value</p>
                                </div>
                            </div>

                            @if($orders->count())
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th>Order No.</th>
                                                <th>Store</th>
                                                <th>Items</th>
                                                <th>Amount</th>
                                                <th>Payment</th>
                                                <th>Status</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($orders as $order)
                                                @php
                                                    $statusColor = [
                                                        'pending' => 'warning', 'confirmed' => 'info',
                                                        'processing' => 'info', 'shipped' => 'primary',
                                                        'delivered' => 'success', 'cancelled' => 'danger',
                                                    ][$order->status] ?? 'dark';
                                                @endphp
                                                <tr>
                                                    <td class="fw-semibold text-dark">{{ $order->order_number }}</td>
                                                    <td>{{ $order->store->name ?? '-' }}</td>
                                                    <td>{{ $order->items_count }}</td>
                                                    <td>{{ number_format((float) $order->total_amount, 2) }}</td>
                                                    <td>{{ strtoupper($order->payment_method) }}</td>
                                                    <td>
                                                        <span class="badge bg-soft-{{ $statusColor }} text-{{ $statusColor }}">
                                                            {{ ucfirst($order->status) }}
                                                        </span>
                                                    </td>
                                                    <td>{{ $order->created_at ? dateFormat($order->created_at) : '-' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="feather-shopping-cart fs-1 mb-3 text-muted d-block"></i>
                                    <p class="text-muted mb-0">No orders placed yet.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>
</main>
@endsection

@extends('admin.layouts.app')

@section('content')
<main class="nxl-container">
    <div class="nxl-content">
        <div class="page-header">
            <div class="page-header-left d-flex align-items-center">
                <div class="page-header-title">
                    <h5 class="m-b-10">Store Details</h5>
                </div>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('stores.index') }}">Stores</a></li>
                    <li class="breadcrumb-item">View</li>
                </ul>
            </div>
            <div class="page-header-right ms-auto">
                <div class="d-flex gap-2">
                    <a href="{{ route('stores.edit', $store->id) }}" class="btn btn-primary">
                        <i class="feather-edit-3 me-2"></i>Edit
                    </a>
                    <a href="{{ route('stores.index') }}" class="btn btn-secondary">
                        <i class="feather-arrow-left me-2"></i>Back
                    </a>
                </div>
            </div>
        </div>

        <div class="main-content">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card stretch stretch-full">
                        <div class="card-body">
                            <table class="table table-bordered">
                                <tbody>
                                    <tr>
                                        <th style="width:30%">Name</th>
                                        <td>{{ $store->name }}</td>
                                    </tr>
                                    <tr>
                                        <th>Slug</th>
                                        <td>{{ $store->slug }}</td>
                                    </tr>
                                    <tr>
                                        <th>Status</th>
                                        <td>
                                            @if($store->status == 1)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-danger">Inactive</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Total Categories</th>
                                        <td>{{ $store->categories_count }}</td>
                                    </tr>
                                    <tr>
                                        <th>Total Sub Categories</th>
                                        <td>{{ $store->sub_categories_count }}</td>
                                    </tr>
                                    <tr>
                                        <th>Created At</th>
                                        <td>{{ dateFormat($store->created_at) }}</td>
                                    </tr>
                                    <tr>
                                        <th>Updated At</th>
                                        <td>{{ dateFormat($store->updated_at) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('admin.layouts.footer')
</main>
@endsection

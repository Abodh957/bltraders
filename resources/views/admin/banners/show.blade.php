@extends('admin.layouts.app')

@section('content')
<main class="nxl-container">
    <div class="nxl-content">
        <div class="page-header">
            <div class="page-header-left d-flex align-items-center">
                <div class="page-header-title">
                    <h5 class="m-b-10">Banner Details</h5>
                </div>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('banners.index') }}">Banners</a></li>
                    <li class="breadcrumb-item">View</li>
                </ul>
            </div>
            <div class="page-header-right ms-auto">
                <div class="page-header-right-items">
                    <div class="d-flex align-items-center gap-2">
                        <a href="{{ route('banners.edit', $banner->id) }}" class="btn btn-primary">
                            <i class="feather-edit-3 me-2"></i>Edit
                        </a>
                        <a href="{{ route('banners.index') }}" class="btn btn-secondary">
                            <i class="feather-arrow-left me-2"></i>Back
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="main-content">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card stretch stretch-full">
                        <div class="card-body">
                            <div class="text-center mb-4">
                                <img src="{{ config('custom.public_path') . $banner->image_url }}"
                                    alt="{{ $banner->title }}"
                                    class="img-fluid rounded"
                                    style="max-height:300px;object-fit:cover;width:100%;"
                                    onerror="this.src='/public/adminAssets/images/placeholder.png'">
                            </div>

                            <table class="table table-bordered">
                                <tbody>
                                    <tr>
                                        <th style="width:30%">Title</th>
                                        <td>{{ $banner->title }}</td>
                                    </tr>
                                    <tr>
                                        <th>Heading</th>
                                        <td>{{ $banner->heading ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Order</th>
                                        <td>{{ $banner->order }}</td>
                                    </tr>
                                    <tr>
                                        <th>Status</th>
                                        <td>
                                            @if($banner->status == 1)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-danger">Inactive</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Created At</th>
                                        <td>{{ dateFormat($banner->created_at) }}</td>
                                    </tr>
                                    <tr>
                                        <th>Updated At</th>
                                        <td>{{ dateFormat($banner->updated_at) }}</td>
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

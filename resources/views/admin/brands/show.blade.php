@extends('admin.layouts.app')

@section('content')
<main class="nxl-container">
    <div class="nxl-content">
        <div class="page-header">
            <div class="page-header-left d-flex align-items-center">
                <div class="page-header-title">
                    <h5 class="m-b-10">Brand Details</h5>
                </div>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('brands.index') }}">Brands</a></li>
                    <li class="breadcrumb-item">View</li>
                </ul>
            </div>
            <div class="page-header-right ms-auto">
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('brands.edit', $brand->id) }}" class="btn btn-primary">
                        <i class="feather-edit-3 me-2"></i>Edit
                    </a>
                    <a href="{{ route('brands.index') }}" class="btn btn-secondary">
                        <i class="feather-arrow-left me-2"></i>Back
                    </a>
                </div>
            </div>
        </div>

        <div class="main-content">
            <div class="row">
                {{-- Cover image --}}
                @if($brand->cover_url)
                <div class="col-lg-12 mb-4">
                    <div class="card stretch stretch-full">
                        <div class="card-body p-0">
                            <img src="{{ config('custom.public_path') . $brand->cover_url }}"
                                alt="Cover" class="img-fluid w-100"
                                style="max-height:250px;object-fit:cover;border-radius:8px;"
                                onerror="this.parentElement.parentElement.style.display='none'">
                        </div>
                    </div>
                </div>
                @endif

                <div class="col-lg-4">
                    <div class="card stretch stretch-full text-center p-4">
                        @if($brand->logo_url)
                        <img src="{{ config('custom.public_path') . $brand->logo_url }}"
                            alt="{{ $brand->name }}"
                            class="mx-auto mb-3"
                            style="max-width:160px;max-height:120px;object-fit:contain;"
                            onerror="this.style.display='none'">
                        @else
                        <div class="avatar-text avatar-xxl bg-soft-primary text-primary mx-auto mb-3" style="width:80px;height:80px;font-size:28px;display:flex;align-items:center;justify-content:center;border-radius:50%;">
                            <i class="feather-tag"></i>
                        </div>
                        @endif
                        <h5 class="mb-1">{{ $brand->name }}</h5>
                        <p class="text-muted mb-2">{{ $brand->slug }}</p>
                        @if($brand->status == 1)
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-danger">Inactive</span>
                        @endif
                        @if($brand->website_url)
                        <div class="mt-3">
                            <a href="{{ $brand->website_url }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="feather-external-link me-1"></i>Visit Website
                            </a>
                        </div>
                        @endif
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="card stretch stretch-full">
                        <div class="card-body">
                            <h6 class="text-muted fw-bold mb-3 text-uppercase" style="font-size:11px;letter-spacing:1px;">Brand Information</h6>
                            <table class="table table-bordered">
                                <tbody>
                                    <tr>
                                        <th style="width:35%">Name</th>
                                        <td>{{ $brand->name }}</td>
                                    </tr>
                                    <tr>
                                        <th>Slug</th>
                                        <td>{{ $brand->slug }}</td>
                                    </tr>
                                    <tr>
                                        <th>Website</th>
                                        <td>
                                            @if($brand->website_url)
                                                <a href="{{ $brand->website_url }}" target="_blank">{{ $brand->website_url }}</a>
                                            @else -
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Description</th>
                                        <td>{{ $brand->description ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Sort Order</th>
                                        <td>{{ $brand->sort_order }}</td>
                                    </tr>
                                    <tr>
                                        <th>Status</th>
                                        <td>
                                            @if($brand->status == 1)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-danger">Inactive</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Created At</th>
                                        <td>{{ dateFormat($brand->created_at) }}</td>
                                    </tr>
                                    <tr>
                                        <th>Updated At</th>
                                        <td>{{ dateFormat($brand->updated_at) }}</td>
                                    </tr>
                                </tbody>
                            </table>

                            <h6 class="text-muted fw-bold mt-4 mb-3 text-uppercase" style="font-size:11px;letter-spacing:1px;">SEO Information</h6>
                            <table class="table table-bordered">
                                <tbody>
                                    <tr>
                                        <th style="width:35%">Meta Title</th>
                                        <td>{{ $brand->meta_title ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Meta Description</th>
                                        <td>{{ $brand->meta_description ?? '-' }}</td>
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

@extends('admin.layouts.app')

@section('content')
<main class="nxl-container">
    <div class="nxl-content">
        <div class="page-header">
            <div class="page-header-left d-flex align-items-center">
                <div class="page-header-title">
                    <h5 class="m-b-10">Sub Category Details</h5>
                </div>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('sub-categories.index') }}">Sub Categories</a></li>
                    <li class="breadcrumb-item">View</li>
                </ul>
            </div>
            <div class="page-header-right ms-auto">
                <div class="d-flex gap-2">
                    <a href="{{ route('sub-categories.edit', $subCategory->id) }}" class="btn btn-primary">
                        <i class="feather-edit-3 me-2"></i>Edit
                    </a>
                    <a href="{{ route('sub-categories.index') }}" class="btn btn-secondary">
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
                            @if($subCategory->image)
                                <div class="text-center mb-4">
                                    <img src="{{ $subCategory->image_url }}" alt="{{ $subCategory->sub_category }}"
                                        class="img-fluid rounded" style="max-height:200px;object-fit:cover;">
                                </div>
                            @endif
                            <table class="table table-bordered">
                                <tbody>
                                    <tr><th style="width:30%">Name</th><td>{{ $subCategory->sub_category }}</td></tr>
                                    <tr><th>Slug</th><td>{{ $subCategory->slug }}</td></tr>
                                    <tr><th>Store</th><td>{{ $subCategory->store->name ?? '-' }}</td></tr>
                                    <tr><th>Category</th><td>{{ $subCategory->category->name ?? '-' }}</td></tr>
                                    <tr><th>Description</th><td>{{ $subCategory->description ?? '-' }}</td></tr>
                                    <tr>
                                        <th>Status</th>
                                        <td>
                                            @if($subCategory->status == 1)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-danger">Inactive</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr><th>Created At</th><td>{{ dateFormat($subCategory->created_at) }}</td></tr>
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

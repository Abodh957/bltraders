@extends('admin.layouts.app')

@section('content')
<main class="nxl-container">
    <div class="nxl-content">
        <div class="page-header">
            <div class="page-header-left d-flex align-items-center">
                <div class="page-header-title">
                    <h5 class="m-b-10">Edit Category</h5>
                </div>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('categories.index') }}">Categories</a></li>
                    <li class="breadcrumb-item">Edit</li>
                </ul>
            </div>
            <div class="page-header-right ms-auto">
                <a href="{{ route('categories.index') }}" class="btn btn-secondary">
                    <i class="feather-arrow-left me-2"></i>Back
                </a>
            </div>
        </div>

        <div class="main-content">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card stretch stretch-full">
                        <div class="card-body lead-status">
                            <form action="{{ route('categories.update', $category->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <div class="row">
                                    <div class="col-lg-4 mb-4">
                                        <label class="form-label">Name <span class="text-danger">*</span></label>
                                        <input type="text" name="name" value="{{ old('name', $category->name) }}"
                                            placeholder="Enter category name"
                                            class="form-control @error('name') is-invalid @enderror" />
                                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>

                                    <div class="col-lg-4 mb-4">
                                        <label class="form-label">Store <span class="text-danger">*</span></label>
                                        <select name="store_id" class="form-select form-control @error('store_id') is-invalid @enderror">
                                            <option value="">Select store</option>
                                            @foreach ($stores as $store)
                                                <option value="{{ $store->id }}" {{ old('store_id', $category->store_id) == $store->id ? 'selected' : '' }}>
                                                    {{ $store->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('store_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>

                                    <div class="col-lg-4 mb-4">
                                        <label class="form-label">Image</label>
                                        <input type="file" name="image" accept="image/*"
                                            class="form-control @error('image') is-invalid @enderror" />
                                        @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        @if($category->image)
                                            <img src="{{ $category->image_url }}" alt="Current Image" class="mt-2" style="max-width:100px;border-radius:4px;">
                                        @endif
                                    </div>

                                    <div class="col-lg-12 mb-4">
                                        <label class="form-label">Description</label>
                                        <textarea name="description" rows="3" placeholder="Enter description"
                                            class="form-control @error('description') is-invalid @enderror">{{ old('description', $category->description) }}</textarea>
                                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>

                                <div class="row mt-4">
                                    <div class="col-lg-12 d-flex justify-content-end gap-2">
                                        <a href="{{ route('categories.index') }}" class="btn btn-secondary">
                                            <i class="feather-x me-2"></i>Cancel
                                        </a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="feather-save me-2"></i>Update
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('admin.layouts.footer')
</main>
@endsection

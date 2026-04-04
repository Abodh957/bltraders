@extends('admin.layouts.app')

@section('content')
<main class="nxl-container">
    <div class="nxl-content">
        <div class="page-header">
            <div class="page-header-left d-flex align-items-center">
                <div class="page-header-title">
                    <h5 class="m-b-10">Sub Category</h5>
                </div>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item">Add New</li>
                </ul>
            </div>
            <div class="page-header-right ms-auto">
                <div class="page-header-right-items">
                    <div class="d-flex d-md-none">
                        <a href="{{ route('sub-categories.index') }}" class="page-header-right-close-toggle">
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
                <div class="col-lg-12">
                    <div class="card stretch stretch-full">
                        <div class="card-body lead-status">
                            <form action="{{ route('sub-categories.update', $subCategory->id) }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf
                                @method('PUT') <!-- Include the PUT method for update -->

                                <div class="row">
                                    <!-- Name Field -->
                                    <div class="col-lg-4 mb-4">
                                        <label class="form-label">Sub Category</label>
                                        <input type="text" required name="sub_category"
                                            value="{{ old('name', $subCategory->sub_category) }}"
                                            placeholder="Please enter sub category"
                                            class="form-control @error('sub_category') is-invalid  @enderror" />
                                    </div>
                                    <div class="col-lg-4 mb-4">
                                        <label class="form-label">Category</label>
                                        <select
                                            class="form-select form-control @error('category') is-invalid  @enderror"
                                            data-select2-selector="visibility" name="category">
                                            <option value="" data-icon="feather-globe2">
                                                Please select category</option>
                                            @foreach ($categories as $category)
                                                <option value="{{$category->id}}"{{$category->id==$subCategory->category_id ? 'selected':''}} data-icon="feather-globe2">
                                                    {{$category->name}}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <!-- Image Field -->
                                    <div class="col-lg-4 mb-4">
                                        <label class="form-label">Image</label>
                                        <input type="file" name="image" accept="image/*"
                                            class="form-control @error('image') is-invalid  @enderror" />
                                        @if($subCategory->image)
                                            <img src="{{ config('custom.public_path') . $subCategory->image }}"
                                                alt="Current Image" class="mt-2" style="max-width: 100px;">
                                        @endif
                                    </div>

                                    <!-- Description Field -->
                                    <div class="col-lg-12 mb-4">
                                        <label class="form-label">Description</label>
                                        <textarea name="description" placeholder="Please enter a description"
                                            class="form-control @error('description') is-invalid  @enderror">{{ old('description', $subCategory->description) }}</textarea>
                                    </div>
                                </div>

                                <div class="row mt-4">
                                    <!-- Back Button -->
                                    <div class="col-lg-4 mb-4 d-flex justify-content-start">
                                        <a href="{{ route('sub-categories.index') }}" class="btn btn-secondary">
                                            <i class="feather-arrow-left me-2"></i>
                                            <span>Back</span>
                                        </a>
                                    </div>

                                    <!-- Save Button -->
                                    <div class="col-lg-4 mb-4 offset-lg-4 d-flex justify-content-end">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="feather-save me-2"></i>
                                            <span>Save</span>
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
</main>
@endsection
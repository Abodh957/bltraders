@extends('admin.layouts.app')

@section('content')
<main class="nxl-container">
    <div class="nxl-content">
        <div class="page-header">
            <div class="page-header-left d-flex align-items-center">
                <div class="page-header-title">
                    <h5 class="m-b-10">Categories</h5>
                </div>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item">Add New</li>
                </ul>
            </div>
            <div class="page-header-right ms-auto">
                <div class="page-header-right-items">
                    <div class="d-flex d-md-none">
                        <a href="{{ route('categories.index') }}" class="page-header-right-close-toggle">
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
                            <form action="{{ route('categories.store') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    <!-- Name Field -->
                                    <div class="col-lg-4 mb-4">
                                        <label class="form-label">Name</label>
                                        <input type="text" name="category" value="{{ old('name') }}"
                                            placeholder="Please enter a category name"
                                            class="form-control @error('category') is-invalid  @enderror" />
                                    </div>
                                    <div class="col-lg-4 mb-4">
                                        <label class="form-label">Main Category</label>
                                        <select
                                            class="form-select form-control @error('main_category') is-invalid  @enderror"
                                            data-select2-selector="visibility" name="main_category">
                                            <option value="" data-icon="feather-globe2">
                                                Please select main category</option>
                                            @foreach ($mainCategories as $mainCategory)
                                                <option value="{{$mainCategory->id}}" data-icon="feather-globe2">
                                                    {{$mainCategory->name}}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <!-- Image Field -->
                                    <div class="col-lg-4 mb-4">
                                        <label class="form-label">Image</label>
                                        <input type="file" accept="image/*" name="image" value="{{ old('image') }}"
                                            placeholder="Please select an image"
                                            class="form-control @error('image') is-invalid  @enderror" />
                                    </div>

                                    <!-- Description Field -->
                                    <div class="col-lg-12 mb-4">
                                        <label class="form-label">Description</label>
                                        <textarea name="description" placeholder="Please enter a description"
                                            class="form-control @error('description') is-invalid  @enderror">{{ old('description') }}</textarea>
                                    </div>
                                </div>

                                <div class="row mt-4">
                                    <!-- Back Button -->
                                    <div class="col-lg-4 mb-4 d-flex justify-content-start">
                                        <a href="{{ route('categories.index') }}" class="btn btn-secondary">
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
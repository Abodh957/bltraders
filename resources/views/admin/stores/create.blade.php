@extends('admin.layouts.app')

@section('content')
<main class="nxl-container">
    <div class="nxl-content">
        <div class="page-header">
            <div class="page-header-left d-flex align-items-center">
                <div class="page-header-title">
                    <h5 class="m-b-10">Add Store</h5>
                </div>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('stores.index') }}">Stores</a></li>
                    <li class="breadcrumb-item">Add New</li>
                </ul>
            </div>
            <div class="page-header-right ms-auto">
                <a href="{{ route('stores.index') }}" class="btn btn-secondary">
                    <i class="feather-arrow-left me-2"></i>Back
                </a>
            </div>
        </div>

        <div class="main-content">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card stretch stretch-full">
                        <div class="card-body lead-status">
                            <form action="{{ route('stores.store') }}" method="POST">
                                @csrf
                                <div class="row">
                                    <div class="col-lg-6 mb-4">
                                        <label class="form-label">Name <span class="text-danger">*</span></label>
                                        <input type="text" name="name" value="{{ old('name') }}"
                                            placeholder="Enter store name (e.g. Kitchenware)"
                                            class="form-control @error('name') is-invalid @enderror" />
                                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>

                                    <div class="col-lg-6 mb-4">
                                        <label class="form-label">Status <span class="text-danger">*</span></label>
                                        <select name="status" class="form-select form-control @error('status') is-invalid @enderror">
                                            <option value="1" {{ old('status', '1') == '1' ? 'selected' : '' }}>Active</option>
                                            <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>

                                <div class="row mt-4">
                                    <div class="col-lg-12 d-flex justify-content-end gap-2">
                                        <a href="{{ route('stores.index') }}" class="btn btn-secondary">
                                            <i class="feather-x me-2"></i>Cancel
                                        </a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="feather-save me-2"></i>Save
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

@extends('admin.layouts.app')

@section('content')
<main class="nxl-container">
    <div class="nxl-content">
        <div class="page-header">
            <div class="page-header-left d-flex align-items-center">
                <div class="page-header-title">
                    <h5 class="m-b-10">Add Sub Category</h5>
                </div>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('sub-categories.index') }}">Sub Categories</a></li>
                    <li class="breadcrumb-item">Add New</li>
                </ul>
            </div>
            <div class="page-header-right ms-auto">
                <a href="{{ route('sub-categories.index') }}" class="btn btn-secondary">
                    <i class="feather-arrow-left me-2"></i>Back
                </a>
            </div>
        </div>

        <div class="main-content">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card stretch stretch-full">
                        <div class="card-body lead-status">
                            <form action="{{ route('sub-categories.store') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    <div class="col-lg-4 mb-4">
                                        <label class="form-label">Name <span class="text-danger">*</span></label>
                                        <input type="text" name="sub_category" value="{{ old('sub_category') }}"
                                            placeholder="Enter sub category name"
                                            class="form-control @error('sub_category') is-invalid @enderror" />
                                        @error('sub_category')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>

                                    <div class="col-lg-4 mb-4">
                                        <label class="form-label">Store <span class="text-danger">*</span></label>
                                        <select name="store_id" id="store_id" class="form-select form-control @error('store_id') is-invalid @enderror">
                                            <option value="">Select store</option>
                                            @foreach ($stores as $store)
                                                <option value="{{ $store->id }}" {{ old('store_id') == $store->id ? 'selected' : '' }}>
                                                    {{ $store->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('store_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>

                                    <div class="col-lg-4 mb-4">
                                        <label class="form-label">Category <span class="text-danger">*</span></label>
                                        <select name="category_id" id="category_id" class="form-select form-control @error('category_id') is-invalid @enderror">
                                            <option value="">Select category</option>
                                            @foreach ($categories as $category)
                                                <option value="{{ $category->id }}"
                                                    data-store-id="{{ $category->store_id }}"
                                                    {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                                    {{ $category->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>

                                    <div class="col-lg-4 mb-4">
                                        <label class="form-label">Image <span class="text-danger">*</span></label>
                                        <input type="file" name="image" accept="image/*"
                                            class="form-control @error('image') is-invalid @enderror"
                                            onchange="previewImage(this)" />
                                        @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        <div class="mt-2">
                                            <img id="imagePreview" src="#" alt="Preview" class="d-none"
                                                style="max-width:100px;border-radius:4px;">
                                        </div>
                                    </div>

                                    <div class="col-lg-12 mb-4">
                                        <label class="form-label">Description</label>
                                        <textarea name="description" rows="3" placeholder="Enter description"
                                            class="form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
                                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>

                                <div class="row mt-4">
                                    <div class="col-lg-12 d-flex justify-content-end gap-2">
                                        <a href="{{ route('sub-categories.index') }}" class="btn btn-secondary">
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

    @push('script')
    <script>
        function previewImage(input) {
            var preview = document.getElementById('imagePreview');
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    preview.src = e.target.result;
                    preview.classList.remove('d-none');
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        function filterCategories(storeId) {
            var selected = '{{ old('category_id') }}';
            $('#category_id option').each(function () {
                var $opt = $(this);
                if ($opt.val() === '') return;
                var show = !storeId || $opt.data('storeId') == storeId;
                $opt.toggle(show);
                if (!show && $opt.is(':selected')) {
                    $('#category_id').val('');
                }
            });
        }

        $(document).ready(function () {
            var initialStore = $('#store_id').val();
            if (initialStore) filterCategories(initialStore);

            $('#store_id').on('change', function () {
                filterCategories($(this).val());
            });
        });
    </script>
    @endpush

    @include('admin.layouts.footer')
</main>
@endsection

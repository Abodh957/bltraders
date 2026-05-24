@extends('admin.layouts.app')

@section('content')
<main class="nxl-container">
    <div class="nxl-content">
        <div class="page-header">
            <div class="page-header-left d-flex align-items-center">
                <div class="page-header-title">
                    <h5 class="m-b-10">Edit Product</h5>
                </div>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('products.index') }}">Products</a></li>
                    <li class="breadcrumb-item">Edit</li>
                </ul>
            </div>
            <div class="page-header-right ms-auto">
                <a href="{{ route('products.index') }}" class="btn btn-secondary">
                    <i class="feather-arrow-left me-2"></i>Back
                </a>
            </div>
        </div>

        <div class="main-content">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card stretch stretch-full">
                        <div class="card-body lead-status">
                            <form action="{{ route('products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')

                                {{-- Basic Info --}}
                                <h6 class="text-muted fw-bold mb-3 text-uppercase" style="font-size:11px;letter-spacing:1px;">Basic Information</h6>
                                <div class="row">
                                    <div class="col-lg-4 mb-4">
                                        <label class="form-label">Product Name <span class="text-danger">*</span></label>
                                        <input type="text" name="name" value="{{ old('name', $product->name) }}"
                                            placeholder="Enter product name"
                                            class="form-control @error('name') is-invalid @enderror" />
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-lg-4 mb-4">
                                        <label class="form-label">SKU</label>
                                        <input type="text" name="sku" value="{{ old('sku', $product->sku) }}"
                                            placeholder="e.g. BL-PRO-2024"
                                            class="form-control @error('sku') is-invalid @enderror" />
                                        @error('sku')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-lg-4 mb-4">
                                        <label class="form-label">Status <span class="text-danger">*</span></label>
                                        <select name="status" class="form-control @error('status') is-invalid @enderror">
                                            <option value="1" {{ old('status', $product->status)==1 ? 'selected' : '' }}>Active</option>
                                            <option value="0" {{ old('status', $product->status)==0 ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                        @error('status')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-lg-12 mb-4">
                                        <label class="form-label">Description</label>
                                        <textarea name="description" rows="3" placeholder="Enter product description"
                                            class="form-control @error('description') is-invalid @enderror">{{ old('description', $product->description) }}</textarea>
                                        @error('description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Category --}}
                                <h6 class="text-muted fw-bold mb-3 text-uppercase" style="font-size:11px;letter-spacing:1px;">Category</h6>
                                <div class="row">
                                    <div class="col-lg-4 mb-4">
                                        <label class="form-label">Store <span class="text-danger">*</span></label>
                                        <select name="store_id" id="storeSelect"
                                            class="form-control @error('store_id') is-invalid @enderror">
                                            <option value="">Select Store</option>
                                            @foreach($stores as $store)
                                                <option value="{{ $store->id }}" {{ old('store_id', $product->store_id)==$store->id ? 'selected' : '' }}>
                                                    {{ $store->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('store_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-lg-4 mb-4">
                                        <label class="form-label">Category</label>
                                        <select name="category_id" id="categorySelect"
                                            class="form-control @error('category_id') is-invalid @enderror">
                                            <option value="">Select Category</option>
                                            @foreach($categories as $cat)
                                                <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id)==$cat->id ? 'selected' : '' }}>
                                                    {{ $cat->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('category_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-lg-4 mb-4">
                                        <label class="form-label">Sub Category</label>
                                        <select name="sub_category_id" id="subCategorySelect"
                                            class="form-control @error('sub_category_id') is-invalid @enderror">
                                            <option value="">Select Sub Category</option>
                                            @foreach($subCategories as $sub)
                                                <option value="{{ $sub->id }}" {{ old('sub_category_id', $product->sub_category_id)==$sub->id ? 'selected' : '' }}>
                                                    {{ $sub->sub_category }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('sub_category_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Pricing & Stock --}}
                                <h6 class="text-muted fw-bold mb-3 text-uppercase" style="font-size:11px;letter-spacing:1px;">Pricing & Stock</h6>
                                <div class="row">
                                    <div class="col-lg-3 mb-4">
                                        <label class="form-label">Price (₹) <span class="text-danger">*</span></label>
                                        <input type="number" name="price" value="{{ old('price', $product->price) }}"
                                            step="0.01" min="0"
                                            class="form-control @error('price') is-invalid @enderror" />
                                        @error('price')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-lg-3 mb-4">
                                        <label class="form-label">Sale Price (₹)</label>
                                        <input type="number" name="sale_price" value="{{ old('sale_price', $product->sale_price) }}"
                                            step="0.01" min="0"
                                            class="form-control @error('sale_price') is-invalid @enderror" />
                                        @error('sale_price')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-lg-3 mb-4">
                                        <label class="form-label">Stock <span class="text-danger">*</span></label>
                                        <input type="number" name="stock" value="{{ old('stock', $product->stock) }}" min="0"
                                            class="form-control @error('stock') is-invalid @enderror" />
                                        @error('stock')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-lg-3 mb-4">
                                        <label class="form-label">GST %</label>
                                        <input type="number" name="gst_percentage" value="{{ old('gst_percentage', $product->gst_percentage) }}"
                                            step="0.01" min="0" max="100"
                                            class="form-control @error('gst_percentage') is-invalid @enderror" />
                                        @error('gst_percentage')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-lg-12 mb-3">
                                        <div class="d-flex gap-4">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="is_gst_paid"
                                                    id="isGstPaid" value="1" {{ old('is_gst_paid', $product->is_gst_paid) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="isGstPaid">GST Paid</label>
                                            </div>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="is_featured"
                                                    id="isFeatured" value="1" {{ old('is_featured', $product->is_featured) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="isFeatured">Featured</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Colors --}}
                                <h6 class="text-muted fw-bold mb-3 text-uppercase" style="font-size:11px;letter-spacing:1px;">Colors</h6>
                                <div class="row mb-4">
                                    <div class="col-lg-12">
                                        <div class="d-flex flex-wrap gap-3">
                                            @foreach($colors as $color)
                                                <label class="d-flex align-items-center gap-2 border rounded px-3 py-2" style="cursor:pointer;">
                                                    <input type="checkbox" name="colors[]" value="{{ $color->id }}"
                                                        {{ in_array($color->id, old('colors', $selectedColors)) ? 'checked' : '' }}
                                                        class="form-check-input mt-0">
                                                    <span style="width:16px;height:16px;border-radius:50%;background:{{ $color->hex_code }};border:1px solid #ccc;display:inline-block;flex-shrink:0;"></span>
                                                    <span>{{ $color->name }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                        @error('colors')
                                            <div class="text-danger mt-1" style="font-size:0.875em;">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Existing Images --}}
                                @if($product->images->count())
                                <h6 class="text-muted fw-bold mb-3 text-uppercase" style="font-size:11px;letter-spacing:1px;">Current Images</h6>
                                <div class="row mb-4">
                                    <div class="col-lg-12">
                                        <div class="d-flex flex-wrap gap-3" id="existingImages">
                                            @foreach($product->images as $img)
                                            <div class="position-relative existing-img" data-id="{{ $img->id }}" style="width:90px;">
                                                <img src="{{ config('custom.public_path') }}/uploads/admin/products/{{ $img->image_path }}"
                                                    style="width:90px;height:90px;object-fit:cover;border-radius:6px;border:2px solid {{ $img->is_primary ? '#0d6efd' : '#dee2e6' }};">
                                                @if($img->is_primary)
                                                    <span class="badge bg-primary position-absolute" style="bottom:2px;left:2px;font-size:9px;">Primary</span>
                                                @endif
                                                <div class="d-flex gap-1 mt-1">
                                                    <button type="button" class="btn btn-xs btn-outline-primary set-primary-btn flex-grow-1"
                                                        data-id="{{ $img->id }}" title="Set Primary" style="font-size:10px;padding:2px 4px;">
                                                        <i class="feather-star" style="font-size:10px;"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-xs btn-outline-danger delete-img-btn flex-grow-1"
                                                        data-id="{{ $img->id }}" title="Delete" style="font-size:10px;padding:2px 4px;">
                                                        <i class="feather-trash-2" style="font-size:10px;"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                @endif

                                {{-- Add New Images --}}
                                <h6 class="text-muted fw-bold mb-3 text-uppercase" style="font-size:11px;letter-spacing:1px;">Add More Images</h6>
                                <div class="row mb-4">
                                    <div class="col-lg-12">
                                        <input type="file" name="images[]" id="imagesInput" multiple accept="image/*"
                                            class="form-control @error('images.*') is-invalid @enderror" />
                                        <small class="text-muted">Max 3MB each.</small>
                                        @error('images.*')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                        <div id="imagePreviewContainer" class="d-flex flex-wrap gap-2 mt-3"></div>
                                    </div>
                                </div>

                                {{-- Specifications --}}
                                <h6 class="text-muted fw-bold mb-3 text-uppercase" style="font-size:11px;letter-spacing:1px;">Product Specifications</h6>
                                <div class="row mb-4">
                                    <div class="col-lg-12">
                                        <table class="table table-bordered" id="specTable">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="width:40%">Specification Name</th>
                                                    <th style="width:50%">Value</th>
                                                    <th style="width:10%" class="text-center">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody id="specBody">
                                                @forelse($product->specifications as $spec)
                                                <tr class="spec-row">
                                                    <td><input type="text" name="spec_key[]" class="form-control" value="{{ $spec->spec_key }}"></td>
                                                    <td><input type="text" name="spec_value[]" class="form-control" value="{{ $spec->spec_value }}"></td>
                                                    <td class="text-center">
                                                        <button type="button" class="btn btn-sm btn-danger remove-spec"><i class="feather-x"></i></button>
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr class="spec-row">
                                                    <td><input type="text" name="spec_key[]" class="form-control" placeholder="e.g. Material, RAM, Size"></td>
                                                    <td><input type="text" name="spec_value[]" class="form-control" placeholder="e.g. Cotton, 8GB, XL"></td>
                                                    <td class="text-center">
                                                        <button type="button" class="btn btn-sm btn-danger remove-spec"><i class="feather-x"></i></button>
                                                    </td>
                                                </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                        <button type="button" id="addSpecBtn" class="btn btn-sm btn-outline-primary">
                                            <i class="feather-plus me-1"></i>Add Row
                                        </button>
                                    </div>
                                </div>

                                <div class="row mt-2">
                                    <div class="col-lg-12 d-flex justify-content-end gap-2">
                                        <a href="{{ route('products.index') }}" class="btn btn-secondary">
                                            <i class="feather-x me-2"></i>Cancel
                                        </a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="feather-save me-2"></i>Update Product
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
    $(document).ready(function () {
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

        // Store → Category AJAX
        $('#storeSelect').on('change', function () {
            var storeId = $(this).val();
            $('#categorySelect').html('<option value="">Select Category</option>');
            $('#subCategorySelect').html('<option value="">Select Sub Category</option>');
            if (!storeId) return;
            $.get('{{ route("products.categories") }}', { store_id: storeId }, function (data) {
                $.each(data, function (i, cat) {
                    $('#categorySelect').append('<option value="' + cat.id + '">' + cat.name + '</option>');
                });
            });
        });

        // Category → SubCategory AJAX
        $('#categorySelect').on('change', function () {
            var catId = $(this).val();
            $('#subCategorySelect').html('<option value="">Select Sub Category</option>');
            if (!catId) return;
            $.get('{{ route("products.subCategories") }}', { category_id: catId }, function (data) {
                $.each(data, function (i, sub) {
                    var selected = (sub.id == {{ $product->sub_category_id ?? 'null' }}) ? 'selected' : '';
                    $('#subCategorySelect').append('<option value="' + sub.id + '" ' + selected + '>' + sub.name + '</option>');
                });
            });
        });

        // Image preview
        $('#imagesInput').on('change', function () {
            $('#imagePreviewContainer').html('');
            $.each(this.files, function (i, file) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    $('#imagePreviewContainer').append(
                        '<img src="' + e.target.result + '" style="width:80px;height:80px;object-fit:cover;border-radius:6px;border:1px solid #dee2e6;">'
                    );
                };
                reader.readAsDataURL(file);
            });
        });

        // Delete existing image
        $(document).on('click', '.delete-img-btn', function () {
            var id   = $(this).data('id');
            var $box = $(this).closest('.existing-img');
            Swal.fire({
                title: 'Delete this image?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Yes, delete'
            }).then(function (result) {
                if (!result.isConfirmed) return;
                $.post('{{ route("products.deleteImage") }}', { image_id: id }, function (res) {
                    if (res.success) {
                        $box.remove();
                        Swal.fire({ toast: true, position: 'top-end', showConfirmButton: false, timer: 2000, icon: 'success', title: 'Image deleted.' });
                    }
                });
            });
        });

        // Set primary image
        $(document).on('click', '.set-primary-btn', function () {
            var id = $(this).data('id');
            $.post('{{ route("products.setPrimaryImage") }}', { image_id: id }, function (res) {
                if (res.success) { location.reload(); }
            });
        });

        // Add spec row
        $('#addSpecBtn').on('click', function () {
            $('#specBody').append(
                '<tr class="spec-row">' +
                '<td><input type="text" name="spec_key[]" class="form-control" placeholder="e.g. Material, RAM, Size"></td>' +
                '<td><input type="text" name="spec_value[]" class="form-control" placeholder="e.g. Cotton, 8GB, XL"></td>' +
                '<td class="text-center"><button type="button" class="btn btn-sm btn-danger remove-spec"><i class="feather-x"></i></button></td>' +
                '</tr>'
            );
        });

        // Remove spec row
        $(document).on('click', '.remove-spec', function () {
            if ($('.spec-row').length > 1) {
                $(this).closest('tr').remove();
            }
        });
    });
    </script>
    @endpush

    @include('admin.layouts.footer')
</main>
@endsection

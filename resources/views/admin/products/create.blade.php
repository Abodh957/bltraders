@extends('admin.layouts.app')

@section('content')
<main class="nxl-container">
    <div class="nxl-content">
        <div class="page-header">
            <div class="page-header-left d-flex align-items-center">
                <div class="page-header-title">
                    <h5 class="m-b-10">Add Product</h5>
                </div>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('products.index') }}">Products</a></li>
                    <li class="breadcrumb-item">Add New</li>
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
                            <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data">
                                @csrf

                                {{-- Basic Info --}}
                                <h6 class="text-muted fw-bold mb-3 text-uppercase" style="font-size:11px;letter-spacing:1px;">Basic Information</h6>
                                <div class="row">
                                    <div class="col-lg-4 mb-4">
                                        <label class="form-label">Product Name <span class="text-danger">*</span></label>
                                        <input type="text" name="name" value="{{ old('name') }}" id="nameInput"
                                            placeholder="Enter product name"
                                            class="form-control @error('name') is-invalid @enderror" />
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-lg-4 mb-4">
                                        <label class="form-label">SKU</label>
                                        <input type="text" name="sku" value="{{ old('sku') }}"
                                            placeholder="e.g. BL-PRO-2024"
                                            class="form-control @error('sku') is-invalid @enderror" />
                                        @error('sku')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-lg-4 mb-4">
                                        <label class="form-label">Status <span class="text-danger">*</span></label>
                                        <select name="status" class="form-control @error('status') is-invalid @enderror">
                                            <option value="1" {{ old('status','1')=='1' ? 'selected' : '' }}>Active</option>
                                            <option value="0" {{ old('status')=='0' ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                        @error('status')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-lg-12 mb-4">
                                        <label class="form-label">Description</label>
                                        <textarea name="description" rows="3" placeholder="Enter product description"
                                            class="form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
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
                                                <option value="{{ $store->id }}" {{ old('store_id')==$store->id ? 'selected' : '' }}>
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
                                        <input type="number" name="price" value="{{ old('price') }}" step="0.01" min="0"
                                            placeholder="0.00"
                                            class="form-control @error('price') is-invalid @enderror" />
                                        @error('price')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-lg-3 mb-4">
                                        <label class="form-label">Sale Price (₹)</label>
                                        <input type="number" name="sale_price" value="{{ old('sale_price') }}" step="0.01" min="0"
                                            placeholder="0.00"
                                            class="form-control @error('sale_price') is-invalid @enderror" />
                                        @error('sale_price')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-lg-3 mb-4">
                                        <label class="form-label">Stock <span class="text-danger">*</span></label>
                                        <input type="number" name="stock" value="{{ old('stock', 0) }}" min="0"
                                            class="form-control @error('stock') is-invalid @enderror" />
                                        @error('stock')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-lg-3 mb-4">
                                        <label class="form-label">GST %</label>
                                        <input type="number" name="gst_percentage" value="{{ old('gst_percentage') }}"
                                            step="0.01" min="0" max="100" placeholder="e.g. 18"
                                            class="form-control @error('gst_percentage') is-invalid @enderror" />
                                        @error('gst_percentage')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-lg-12 mb-3">
                                        <div class="d-flex gap-4">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="is_gst_paid"
                                                    id="isGstPaid" value="1" {{ old('is_gst_paid') ? 'checked' : '' }}>
                                                <label class="form-check-label" for="isGstPaid">GST Paid</label>
                                            </div>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="is_featured"
                                                    id="isFeatured" value="1" {{ old('is_featured') ? 'checked' : '' }}>
                                                <label class="form-check-label" for="isFeatured">Featured</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Colors --}}
                                <h6 class="text-muted fw-bold mb-3 text-uppercase" style="font-size:11px;letter-spacing:1px;">Colors</h6>
                                <div class="row mb-4">
                                    <div class="col-lg-12">
                                        @if($colors->isNotEmpty())
                                            <div class="d-flex flex-wrap gap-3">
                                                @foreach($colors as $color)
                                                    <label class="d-flex align-items-center gap-2 border rounded px-3 py-2"
                                                        style="cursor:pointer;@error('colors') border-color:#dc3545!important; @enderror">
                                                        <input type="checkbox" name="colors[]" value="{{ $color->id }}"
                                                            {{ in_array($color->id, old('colors', [])) ? 'checked' : '' }}
                                                            class="form-check-input mt-0">
                                                        <span style="width:16px;height:16px;border-radius:50%;background:{{ $color->hex_code }};border:1px solid #ccc;display:inline-block;flex-shrink:0;"></span>
                                                        <span>{{ $color->name }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        @else
                                            <p class="text-muted mb-0">No colors available. Add colors first.</p>
                                        @endif
                                        @error('colors')
                                            <div class="text-danger mt-1" style="font-size:0.875em;">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Images --}}
                                <h6 class="text-muted fw-bold mb-3 text-uppercase" style="font-size:11px;letter-spacing:1px;">Product Images</h6>
                                <div class="row mb-4">
                                    <div class="col-lg-12">
                                        <input type="file" name="images[]" id="imagesInput" multiple accept="image/*"
                                            class="form-control @error('images.*') is-invalid @enderror" />
                                        <small class="text-muted">First image will be set as primary. Max 3MB each.</small>
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
                                                <tr class="spec-row">
                                                    <td><input type="text" name="spec_key[]" class="form-control" placeholder="e.g. Material, RAM, Size"></td>
                                                    <td><input type="text" name="spec_value[]" class="form-control" placeholder="e.g. Cotton, 8GB, XL"></td>
                                                    <td class="text-center">
                                                        <button type="button" class="btn btn-sm btn-danger remove-spec">
                                                            <i class="feather-x"></i>
                                                        </button>
                                                    </td>
                                                </tr>
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
                                            <i class="feather-save me-2"></i>Save Product
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
                    $('#subCategorySelect').append('<option value="' + sub.id + '">' + sub.name + '</option>');
                });
            });
        });

        // Image preview with removable items
        var selectedFiles = [];

        $('#imagesInput').on('change', function () {
            $.each(this.files, function (i, file) {
                selectedFiles.push(file);
            });
            renderPreviews();
        });

        function renderPreviews() {
            $('#imagePreviewContainer').html('');
            selectedFiles.forEach(function (file, index) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    var badge = index === 0
                        ? '<span class="badge bg-primary position-absolute" style="bottom:2px;left:2px;font-size:9px;pointer-events:none;">Primary</span>'
                        : '';
                    $('#imagePreviewContainer').append(
                        '<div class="position-relative me-1 mb-1" style="width:80px;height:80px;" data-index="' + index + '">' +
                        '<img src="' + e.target.result + '" style="width:80px;height:80px;object-fit:cover;border-radius:6px;border:1px solid #dee2e6;">' +
                        badge +
                        '<button type="button" class="remove-preview-btn position-absolute btn btn-danger btn-xs p-0" ' +
                        'data-index="' + index + '" ' +
                        'style="top:-6px;right:-6px;width:20px;height:20px;border-radius:50%;font-size:11px;line-height:1;z-index:10;">' +
                        '&times;</button>' +
                        '</div>'
                    );
                };
                reader.readAsDataURL(file);
            });
            syncFilesToInput();
        }

        function syncFilesToInput() {
            var dt = new DataTransfer();
            selectedFiles.forEach(function (file) { dt.items.add(file); });
            document.getElementById('imagesInput').files = dt.files;
        }

        $(document).on('click', '.remove-preview-btn', function () {
            var idx = parseInt($(this).data('index'));
            selectedFiles.splice(idx, 1);
            renderPreviews();
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

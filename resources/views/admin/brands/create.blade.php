@extends('admin.layouts.app')

@section('content')
<main class="nxl-container">
    <div class="nxl-content">
        <div class="page-header">
            <div class="page-header-left d-flex align-items-center">
                <div class="page-header-title">
                    <h5 class="m-b-10">Add Brand</h5>
                </div>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('brands.index') }}">Brands</a></li>
                    <li class="breadcrumb-item">Add New</li>
                </ul>
            </div>
            <div class="page-header-right ms-auto">
                <a href="{{ route('brands.index') }}" class="btn btn-secondary">
                    <i class="feather-arrow-left me-2"></i>Back
                </a>
            </div>
        </div>

        <div class="main-content">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card stretch stretch-full">
                        <div class="card-body lead-status">
                            <form action="{{ route('brands.store') }}" method="POST" enctype="multipart/form-data">
                                @csrf

                                {{-- Basic Info --}}
                                <h6 class="text-muted fw-bold mb-3 text-uppercase" style="font-size:11px;letter-spacing:1px;">Basic Information</h6>
                                <div class="row">
                                    <div class="col-lg-4 mb-4">
                                        <label class="form-label">Store</label>
                                        <select name="store_id" class="form-control">
                                            <option value="">All Stores (global)</option>
                                            @foreach($stores as $s)
                                                <option value="{{ $s->id }}" {{ old('store_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                                            @endforeach
                                        </select>
                                        <small class="text-muted">Leave as "All Stores" to show it in every store.</small>
                                    </div>
                                    <div class="col-lg-4 mb-4">
                                        <label class="form-label">Brand Name <span class="text-danger">*</span></label>
                                        <input type="text" name="name" value="{{ old('name') }}" id="nameInput"
                                            placeholder="Enter brand name"
                                            class="form-control @error('name') is-invalid @enderror" />
                                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>

                                    <div class="col-lg-4 mb-4">
                                        <label class="form-label">Slug</label>
                                        <input type="text" name="slug" value="{{ old('slug') }}" id="slugInput"
                                            placeholder="Auto-generated from name"
                                            class="form-control @error('slug') is-invalid @enderror" />
                                        @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>

                                    <div class="col-lg-4 mb-4">
                                        <label class="form-label">Website URL</label>
                                        <input type="url" name="website_url" value="{{ old('website_url') }}"
                                            placeholder="https://example.com"
                                            class="form-control @error('website_url') is-invalid @enderror" />
                                        @error('website_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>

                                    <div class="col-lg-12 mb-4">
                                        <label class="form-label">Description</label>
                                        <textarea name="description" rows="3" placeholder="Enter brand description"
                                            class="form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
                                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>

                                {{-- Images --}}
                                <h6 class="text-muted fw-bold mb-3 text-uppercase" style="font-size:11px;letter-spacing:1px;">Images</h6>
                                <div class="row">
                                    <div class="col-lg-6 mb-4">
                                        <label class="form-label">Logo</label>
                                        <input type="file" name="logo" accept="image/*" id="logoInput"
                                            class="form-control @error('logo') is-invalid @enderror" />
                                        @error('logo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        <div class="mt-2">
                                            <img id="logoPreview" src="#" alt="Logo Preview"
                                                style="max-width:120px;max-height:80px;object-fit:contain;display:none;border-radius:6px;border:1px solid #dee2e6;">
                                        </div>
                                    </div>

                                    <div class="col-lg-6 mb-4">
                                        <label class="form-label">Cover Image</label>
                                        <input type="file" name="cover_image" accept="image/*" id="coverInput"
                                            class="form-control @error('cover_image') is-invalid @enderror" />
                                        @error('cover_image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        <div class="mt-2">
                                            <img id="coverPreview" src="#" alt="Cover Preview"
                                                style="max-width:200px;max-height:80px;object-fit:cover;display:none;border-radius:6px;border:1px solid #dee2e6;">
                                        </div>
                                    </div>
                                </div>

                                {{-- SEO --}}
                                <h6 class="text-muted fw-bold mb-3 text-uppercase" style="font-size:11px;letter-spacing:1px;">SEO</h6>
                                <div class="row">
                                    <div class="col-lg-6 mb-4">
                                        <label class="form-label">Meta Title</label>
                                        <input type="text" name="meta_title" value="{{ old('meta_title') }}"
                                            placeholder="SEO meta title (max 160 chars)"
                                            class="form-control @error('meta_title') is-invalid @enderror" />
                                        @error('meta_title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>

                                    <div class="col-lg-3 mb-4">
                                        <label class="form-label">Sort Order</label>
                                        <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" min="0"
                                            class="form-control @error('sort_order') is-invalid @enderror" />
                                        @error('sort_order')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>

                                    <div class="col-lg-3 mb-4">
                                        <label class="form-label">Status <span class="text-danger">*</span></label>
                                        <select name="status" class="form-select form-control @error('status') is-invalid @enderror">
                                            <option value="1" {{ old('status', '1') == '1' ? 'selected' : '' }}>Active</option>
                                            <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>

                                    <div class="col-lg-12 mb-4">
                                        <label class="form-label">Meta Description</label>
                                        <textarea name="meta_description" rows="2" placeholder="SEO meta description"
                                            class="form-control @error('meta_description') is-invalid @enderror">{{ old('meta_description') }}</textarea>
                                        @error('meta_description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>

                                <div class="row mt-2">
                                    <div class="col-lg-12 d-flex justify-content-end gap-2">
                                        <a href="{{ route('brands.index') }}" class="btn btn-secondary">
                                            <i class="feather-x me-2"></i>Cancel
                                        </a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="feather-save me-2"></i>Save Brand
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
        // Auto-generate slug from name
        document.getElementById('nameInput').addEventListener('input', function () {
            var slug = this.value.toLowerCase().replace(/[^a-z0-9\s-]/g, '').trim().replace(/\s+/g, '-');
            document.getElementById('slugInput').value = slug;
        });

        function previewImage(inputId, previewId) {
            document.getElementById(inputId).addEventListener('change', function (e) {
                var preview = document.getElementById(previewId);
                var file = e.target.files[0];
                if (file) { preview.src = URL.createObjectURL(file); preview.style.display = 'block'; }
            });
        }
        previewImage('logoInput',  'logoPreview');
        previewImage('coverInput', 'coverPreview');
    </script>
    @endpush

    @include('admin.layouts.footer')
</main>
@endsection

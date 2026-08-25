@extends('admin.layouts.app')

@section('content')
<main class="nxl-container">
    <div class="nxl-content">
        <div class="page-header">
            <div class="page-header-left d-flex align-items-center">
                <div class="page-header-title">
                    <h5 class="m-b-10">Edit Banner</h5>
                </div>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('banners.index') }}">Banners</a></li>
                    <li class="breadcrumb-item">Edit</li>
                </ul>
            </div>
            <div class="page-header-right ms-auto">
                <div class="page-header-right-items">
                    <a href="{{ route('banners.index') }}" class="btn btn-secondary">
                        <i class="feather-arrow-left me-2"></i>
                        <span>Back</span>
                    </a>
                </div>
            </div>
        </div>

        <div class="main-content">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card stretch stretch-full">
                        <div class="card-body lead-status">
                            <form action="{{ route('banners.update', $banner->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <div class="row">
                                    <div class="col-lg-6 mb-4">
                                        <label class="form-label">Store</label>
                                        <select name="store_id" class="form-control">
                                            <option value="">All Stores (global)</option>
                                            @foreach($stores as $s)
                                                <option value="{{ $s->id }}" {{ old('store_id', $banner->store_id) == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                                            @endforeach
                                        </select>
                                        <small class="text-muted">Leave as "All Stores" to show it in every store.</small>
                                    </div>
                                    <div class="col-lg-6 mb-4">
                                        <label class="form-label">Title <span class="text-danger">*</span></label>
                                        <input type="text" name="title" value="{{ old('title', $banner->title) }}"
                                            placeholder="Enter banner title"
                                            class="form-control @error('title') is-invalid @enderror" />
                                        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>

                                    <div class="col-lg-6 mb-4">
                                        <label class="form-label">Heading</label>
                                        <input type="text" name="heading" value="{{ old('heading', $banner->heading) }}"
                                            placeholder="Enter banner heading"
                                            class="form-control @error('heading') is-invalid @enderror" />
                                        @error('heading')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>

                                    <div class="col-lg-6 mb-4">
                                        <label class="form-label">Image</label>
                                        <input type="file" name="image" accept="image/*" id="imageInput"
                                            class="form-control @error('image') is-invalid @enderror" />
                                        @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        <div class="mt-2">
                                            <img id="imagePreview"
                                                src="{{ config('custom.public_path') . $banner->image_url }}"
                                                alt="Current Image"
                                                style="max-width:200px;border-radius:6px;"
                                                onerror="this.style.display='none'">
                                        </div>
                                    </div>

                                    <div class="col-lg-3 mb-4">
                                        <label class="form-label">Order</label>
                                        <input type="number" name="order" value="{{ old('order', $banner->order) }}" min="0"
                                            class="form-control @error('order') is-invalid @enderror" />
                                        @error('order')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>

                                    <div class="col-lg-3 mb-4">
                                        <label class="form-label">Status <span class="text-danger">*</span></label>
                                        <select name="status" class="form-select form-control @error('status') is-invalid @enderror">
                                            <option value="1" {{ old('status', $banner->status) == '1' ? 'selected' : '' }}>Active</option>
                                            <option value="0" {{ old('status', $banner->status) == '0' ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>

                                <div class="row mt-4">
                                    <div class="col-lg-12 d-flex justify-content-end gap-2">
                                        <a href="{{ route('banners.index') }}" class="btn btn-secondary">
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

    @push('script')
    <script>
        document.getElementById('imageInput').addEventListener('change', function (e) {
            var preview = document.getElementById('imagePreview');
            var file = e.target.files[0];
            if (file) {
                preview.src = URL.createObjectURL(file);
                preview.style.display = 'block';
            }
        });
    </script>
    @endpush

    @include('admin.layouts.footer')
</main>
@endsection

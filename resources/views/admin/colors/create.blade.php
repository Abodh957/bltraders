@extends('admin.layouts.app')

@section('content')
<main class="nxl-container">
    <div class="nxl-content">
        <div class="page-header">
            <div class="page-header-left d-flex align-items-center">
                <div class="page-header-title">
                    <h5 class="m-b-10">Add Color</h5>
                </div>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('colors.index') }}">Colors</a></li>
                    <li class="breadcrumb-item">Add New</li>
                </ul>
            </div>
            <div class="page-header-right ms-auto">
                <a href="{{ route('colors.index') }}" class="btn btn-secondary">
                    <i class="feather-arrow-left me-2"></i>Back
                </a>
            </div>
        </div>

        <div class="main-content">
            <div class="row">
                <div class="col-lg-6">
                    <div class="card stretch stretch-full">
                        <div class="card-body lead-status">
                            <form action="{{ route('colors.store') }}" method="POST">
                                @csrf

                                <div class="mb-4">
                                    <label class="form-label">Color Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" value="{{ old('name') }}"
                                        placeholder="e.g. Red, Navy Blue, Olive Green"
                                        class="form-control @error('name') is-invalid @enderror" />
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-4">
                                    <label class="form-label">Hex Color Code <span class="text-danger">*</span></label>
                                    <div class="d-flex align-items-center gap-3">
                                        <input type="color" id="colorPicker" value="{{ old('hex_code', '#000000') }}"
                                            style="width:48px;height:38px;padding:2px;border-radius:6px;cursor:pointer;border:1px solid #dee2e6;">
                                        <input type="text" name="hex_code" id="hexInput" value="{{ old('hex_code', '#000000') }}"
                                            placeholder="#000000" maxlength="7"
                                            class="form-control @error('hex_code') is-invalid @enderror"
                                            style="max-width:140px;" />
                                        <span id="colorPreviewBadge"
                                            style="display:inline-block;width:38px;height:38px;border-radius:50%;background:{{ old('hex_code','#000000') }};border:1px solid #dee2e6;flex-shrink:0;"></span>
                                    </div>
                                    @error('hex_code')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-4">
                                    <label class="form-label">Status <span class="text-danger">*</span></label>
                                    <select name="status" class="form-control @error('status') is-invalid @enderror">
                                        <option value="1" {{ old('status','1')=='1' ? 'selected' : '' }}>Active</option>
                                        <option value="0" {{ old('status')=='0' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="d-flex justify-content-end gap-2 mt-4">
                                    <a href="{{ route('colors.index') }}" class="btn btn-secondary">
                                        <i class="feather-x me-2"></i>Cancel
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="feather-save me-2"></i>Save Color
                                    </button>
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
    document.addEventListener('DOMContentLoaded', function () {
        var picker  = document.getElementById('colorPicker');
        var hexInput = document.getElementById('hexInput');
        var preview = document.getElementById('colorPreviewBadge');

        picker.addEventListener('input', function () {
            hexInput.value = this.value.toUpperCase();
            preview.style.background = this.value;
        });

        hexInput.addEventListener('input', function () {
            var val = this.value.trim();
            if (/^#[0-9A-Fa-f]{6}$/.test(val)) {
                picker.value = val;
                preview.style.background = val;
            }
        });
    });
    </script>
    @endpush

    @include('admin.layouts.footer')
</main>
@endsection

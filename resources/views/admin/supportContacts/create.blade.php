@extends('admin.layouts.app')
@section('content')
@php $c = $contact ?? null; @endphp
<main class="nxl-container">
    <div class="nxl-content">
        <div class="page-header">
            <div class="page-header-left d-flex align-items-center">
                <div class="page-header-title"><h5 class="m-b-10">{{ $c ? 'Edit' : 'Add' }} Support Contact</h5></div>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('support-contacts.index') }}">Support Contacts</a></li>
                    <li class="breadcrumb-item">{{ $c ? 'Edit' : 'Add' }}</li>
                </ul>
            </div>
        </div>

        <div class="main-content">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card stretch stretch-full">
                        <div class="card-body">
                            <form action="{{ $c ? route('support-contacts.update', $c->id) : route('support-contacts.store') }}" method="POST">
                                @csrf
                                @if($c) @method('PUT') @endif
                                <div class="row">
                                    <div class="col-lg-4 mb-4">
                                        <label class="form-label">Name <span class="text-danger">*</span></label>
                                        <input type="text" name="name" value="{{ old('name', $c->name ?? '') }}"
                                            class="form-control @error('name') is-invalid @enderror" />
                                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>

                                    <div class="col-lg-4 mb-4">
                                        <label class="form-label">Phone No. <span class="text-danger">*</span></label>
                                        <input type="text" name="phone" value="{{ old('phone', $c->phone ?? '') }}"
                                            class="form-control @error('phone') is-invalid @enderror" />
                                        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>

                                    <div class="col-lg-4 mb-4">
                                        <label class="form-label">Designation</label>
                                        <input type="text" name="designation" value="{{ old('designation', $c->designation ?? '') }}"
                                            class="form-control @error('designation') is-invalid @enderror"
                                            placeholder="e.g. Sales, Complaints" />
                                        @error('designation')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>

                                    <div class="col-lg-4 mb-4">
                                        <label class="form-label">Order</label>
                                        <input type="number" name="sort_order" min="0"
                                            value="{{ old('sort_order', $c->sort_order ?? 0) }}"
                                            class="form-control @error('sort_order') is-invalid @enderror" />
                                        <small class="text-muted">Lower numbers appear first.</small>
                                        @error('sort_order')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>

                                    <div class="col-lg-4 mb-4">
                                        <label class="form-label">Status <span class="text-danger">*</span></label>
                                        <select name="status" class="form-control @error('status') is-invalid @enderror">
                                            <option value="1" {{ old('status', $c->status ?? 1) == 1 ? 'selected' : '' }}>Active</option>
                                            <option value="0" {{ old('status', $c->status ?? 1) == 0 ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>

                                <div class="row mt-2">
                                    <div class="col-lg-6 d-flex justify-content-start">
                                        <a href="{{ route('support-contacts.index') }}" class="btn btn-secondary">
                                            <i class="feather-arrow-left me-2"></i><span>Back</span>
                                        </a>
                                    </div>
                                    <div class="col-lg-6 d-flex justify-content-end">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="feather-save me-2"></i><span>Save</span>
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

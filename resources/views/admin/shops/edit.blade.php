@extends('admin.layouts.app')

@section('content')
<main class="nxl-container">
    <div class="nxl-content">
        <div class="page-header">
            <div class="page-header-left d-flex align-items-center">
                <div class="page-header-title">
                    <h5 class="m-b-10">User Shops</h5>
                </div>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item">Edit</li>
                </ul>
            </div>
            <div class="page-header-right ms-auto">
                <div class="page-header-right-items">
                    <div class="d-flex d-md-none">
                        <a href="{{ route('user-shops.index') }}" class="page-header-right-close-toggle">
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
                            <form action="{{ route('user-shops.update', $shop->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <div class="row">
                                    <div class="col-lg-4 mb-4">
                                        <label class="form-label">User</label>
                                        <select name="user_id" class="form-select form-control @error('user_id') is-invalid @enderror">
                                            <option value="">Please select user</option>
                                            @foreach ($users as $user)
                                                <option value="{{ $user->id }}" {{ old('user_id', $shop->user_id) == $user->id ? 'selected' : '' }}>
                                                    {{ trim($user->first_name.' '.$user->last_name) }} @if($user->phone_no) ({{ $user->phone_no }}) @endif
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('user_id') <span class="text-danger small">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="col-lg-4 mb-4">
                                        <label class="form-label">Shop Name</label>
                                        <input type="text" name="shop_name" value="{{ old('shop_name', $shop->shop_name) }}" class="form-control @error('shop_name') is-invalid @enderror" />
                                        @error('shop_name') <span class="text-danger small">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="col-lg-4 mb-4">
                                        <label class="form-label">Status</label>
                                        <select name="status" class="form-select form-control @error('status') is-invalid @enderror">
                                            @foreach (['pending','approved','rejected'] as $st)
                                                <option value="{{ $st }}" {{ old('status', $shop->status) == $st ? 'selected' : '' }}>{{ ucfirst($st) }}</option>
                                            @endforeach
                                        </select>
                                        @error('status') <span class="text-danger small">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="col-lg-4 mb-4">
                                        <label class="form-label">City</label>
                                        <input type="text" name="city" value="{{ old('city', $shop->city) }}" class="form-control @error('city') is-invalid @enderror" />
                                        @error('city') <span class="text-danger small">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="col-lg-4 mb-4">
                                        <label class="form-label">State</label>
                                        <input type="text" name="state" value="{{ old('state', $shop->state) }}" class="form-control @error('state') is-invalid @enderror" />
                                        @error('state') <span class="text-danger small">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="col-lg-4 mb-4">
                                        <label class="form-label">Country</label>
                                        <input type="text" name="country" value="{{ old('country', $shop->country) }}" class="form-control @error('country') is-invalid @enderror" />
                                        @error('country') <span class="text-danger small">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="col-lg-4 mb-4">
                                        <label class="form-label">Pincode</label>
                                        <input type="text" name="pincode" value="{{ old('pincode', $shop->pincode) }}" class="form-control @error('pincode') is-invalid @enderror" />
                                        @error('pincode') <span class="text-danger small">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="col-lg-4 mb-4">
                                        <label class="form-label">Shop Document</label>
                                        <input type="file" name="shop_document" class="form-control @error('shop_document') is-invalid @enderror" />
                                        @if($shop->shop_document)
                                            <small class="text-muted">Current: {{ $shop->shop_document }}</small>
                                        @endif
                                        @error('shop_document') <span class="text-danger small">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="col-lg-12 mb-4">
                                        <label class="form-label">Shop Address</label>
                                        <textarea name="shop_address" class="form-control @error('shop_address') is-invalid @enderror">{{ old('shop_address', $shop->shop_address) }}</textarea>
                                        @error('shop_address') <span class="text-danger small">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <div class="row mt-4">
                                    <div class="col-lg-4 mb-4 d-flex justify-content-start">
                                        <a href="{{ route('user-shops.index') }}" class="btn btn-secondary">
                                            <i class="feather-arrow-left me-2"></i>
                                            <span>Back</span>
                                        </a>
                                    </div>
                                    <div class="col-lg-4 mb-4 offset-lg-4 d-flex justify-content-end">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="feather-save me-2"></i>
                                            <span>Update</span>
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

@extends('admin.layouts.app')

@section('content')
<main class="nxl-container">
    <div class="nxl-content">
        <div class="page-header">
            <div class="page-header-left d-flex align-items-center">
                <div class="page-header-title">
                    <h5 class="m-b-10">Customers</h5>
                </div>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item">Add New</li>
                </ul>
            </div>
            <div class="page-header-right ms-auto">
                <div class="page-header-right-items">
                    <div class="d-flex d-md-none">
                        <a href="{{ route('customers.index') }}" class="page-header-right-close-toggle">
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
                            <form action="{{ route('customers.store') }}" id="permissionForm" method="POST">
                                @csrf
                                <div class="row">
                                    <div class="col-lg-4 mb-4">
                                        <label class="form-label">Username</label>
                                        <input type="text" required name="name" value="{{ old('name') }}"  placeholder="Please enter a username" class="form-control" />
                                    </div>
                                    <div class="col-lg-4 mb-4">
                                        <label class="form-label">FIrst Name</label>
                                        <input type="text" required name="first_name" value="{{ old('first_name') }}" placeholder="Please enter a first name" class="form-control" />
                                    </div>
                                    <div class="col-lg-4 mb-4">
                                        <label class="form-label">Last Name</label>
                                        <input type="text" required name="last_name" value="{{ old('last_name') }}" placeholder="Please enter a last name" class="form-control" />
                                    </div>
                                    <div class="col-lg-4 mb-4">
                                        <label class="form-label">Email</label>
                                        <input type="email" required name="email" value="{{ old('email') }}" placeholder="Please enter a Email" class="form-control" />
                                    </div>
                                    <div class="col-lg-4 mb-4">
                                        <label class="form-label">Phone No</label>
                                        <input type="text" name="phone_no" value="{{ old('phone_no') }}" placeholder="Please enter a phone number" class="form-control" required pattern="\d{10}" title="Please enter a 10-digit phone number" />

                                    </div>
                                    <div class="col-lg-4 mb-4">
                                        <label class="form-label">Password</label>
                                        <input type="Password" required name="password" value="{{ old('password') }}" placeholder="Please enter a Password" class="form-control" />
                                    </div>
                                    <div class="col-lg-4 mb-4">
                                        <label class="form-label">Confirm Password</label>
                                        <input type="password" name="password_confirmation" value="{{ old('password_confirmation') }}" placeholder="Please confirm your password" class="form-control" required />
                                    </div>

                                </div>


                                <div class="row row mt-4">
                                    <div class="col-lg-4 mb-4 d-flex justify-content-start">
                                        <a href="{{ route('roles.index') }}" class="btn btn-secondary">
                                            <i class="feather-arrow-left me-2"></i>
                                            <span>Back</span>
                                        </a>
                                    </div>
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
@push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('customerForm');
        form.addEventListener('submit', function (event) {
            const phoneInput = document.querySelector('input[name="phone_no"]');
            const phoneValue = phoneInput.value;

            if (!/^\d{10}$/.test(phoneValue)) {
                event.preventDefault(); // Prevent form submission
                alert('Please enter a valid 10-digit phone number.');
                phoneInput.focus();
            }
        });
    });
</script>

@endpush
@endsection

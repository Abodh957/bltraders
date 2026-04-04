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
                    <li class="breadcrumb-item">Edit</li>
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
                            <form action="{{ route('customers.update', $user->id) }}" id="permissionForm" method="POST">
                                @csrf
                                @method('PUT') <!-- Add this if you are using PUT/PATCH for update -->

                                <div class="row">
                                    <div class="col-lg-4 mb-4">
                                        <label class="form-label">Username</label>
                                        <input type="text" name="name" required value="{{ old('name', $user->name) }}" placeholder="Please enter a username" class="form-control" />
                                    </div>
                                    <div class="col-lg-4 mb-4">
                                        <label class="form-label">First Name</label>
                                        <input type="text" name="first_name" required value="{{ old('first_name', $user->first_name) }}" placeholder="Please enter a first name" class="form-control" />
                                    </div>
                                    <div class="col-lg-4 mb-4">
                                        <label class="form-label">Last Name</label>
                                        <input type="text" name="last_name" required value="{{ old('last_name', $user->last_name) }}" placeholder="Please enter a last name" class="form-control" />
                                    </div>
                                    <div class="col-lg-4 mb-4">
                                        <label class="form-label">Email</label>
                                        <input type="email" name="email" required value="{{ old('email', $user->email) }}" placeholder="Please enter an email" class="form-control" />
                                    </div>
                                    <div class="col-lg-4 mb-4">
                                        <label class="form-label">Phone No</label>
                                        <input type="text" name="phone_no" required value="{{ old('phone_no', $user->phone_no) }}" placeholder="Please enter a phone number" class="form-control" pattern="\d{10}" title="Please enter a 10-digit phone number" />
                                    </div>
                                    <div class="col-lg-4 mb-4">
                                        <label class="form-label">Password</label>
                                        <input type="password" name="password" placeholder="Please enter a password" class="form-control" />
                                    </div>
                                    <div class="col-lg-4 mb-4">
                                        <label class="form-label">Confirm Password</label>
                                        <input type="password" name="password_confirmation" placeholder="Please confirm your password" class="form-control" />
                                    </div>
                                    <div class="col-lg-4" data-select2-id="select2-data-91-82j8">
                                        <label class="form-label">Select Role</label>

                                        <select class="form-control select2-hidden-accessible" required data-select2-selector="city" data-select2-id="select2-data-70-8hue" tabindex="-1" name="role_id" aria-hidden="true">
                                            <option  value="" >Select Role</option>
                                            @if(isset($roles) && count($roles) > 0)
                                            @foreach ($roles as $value => $label)
                                            @php
                                            @endphp
                                             <option value="{{ $value }}"  {{ isset($userRole[$value]) ? 'selected' : ''}}>
                                                                                        {{ $label }}
                                                                                    </option>

                                            @endforeach
                                            @endif
                                        </select>
                                    </div>
                                </div>



                                <div class="row mt-4">
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

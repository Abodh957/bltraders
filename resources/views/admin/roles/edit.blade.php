@extends('admin.layouts.app')

@section('content')
<main class="nxl-container">
    <div class="nxl-content">
        <div class="page-header">
            <div class="page-header-left d-flex align-items-center">
                <div class="page-header-title">
                    <h5 class="m-b-10">Role</h5>
                </div>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item">Edit Role</li>
                </ul>
            </div>
            <div class="page-header-right ms-auto">
                <div class="page-header-right-items">
                    <div class="d-flex d-md-none">
                        <a href="{{ route('roles.index') }}" class="page-header-right-close-toggle">
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
                            <form action="{{ route('roles.update', $role->id) }}" id="permissionForm" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="row">
                                    <div class="col-lg-6 mb-4">
                                        <label class="form-label">Name</label>
                                        <input type="text" required name="name" placeholder="Please enter a name" class="form-control" value="{{ $role->name }}" />
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="form-label">Permission</label>
                                    @if(isset($permission))
                                        @foreach ($permission as $row)
                                            <div class="col-lg-4 mb-4">
                                                <div class="custom-control custom-checkbox me-2">
                                                    @php
                                                        $checkboxId = 'checkbox-' . $row->id;
                                                        $formattedName = ucwords(str_replace('-', ' ', $row->name));
                                                    @endphp
                                                    <input type="checkbox"
                                                    {{ in_array($row->id, $rolePermissions) ? 'checked' : ''}}
                                                           value="{{ $row->id }}"
                                                           class="custom-control-input"
                                                           id="{{ $checkboxId }}"
                                                           name="permission[]"
                                                           data-checked-action="task-action">
                                                    <label class="custom-control-label c-pointer" for="{{ $checkboxId }}">
                                                        {{ $formattedName }}
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>

                                <div class="row">
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
@endsection

@extends('admin.layouts.app')

@section('content')
<main class="nxl-container">
    <div class="nxl-content">
        <!-- [ page-header ] start -->
        <div class="page-header">
            <div class="page-header-left d-flex align-items-center">
                <div class="page-header-title">
                    <h5 class="m-b-10">Permissions</h5>
                </div>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active">Permissions</li>
                </ul>
            </div>
            <div class="page-header-right ms-auto">
                <div class="page-header-right-items">
                    <div class="d-flex d-md-none">
                        <a href="{{ route('permissions.index') }}" class="page-header-right-close-toggle">
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
        <!-- [ page-header ] end -->
        <!-- [ Main Content ] start -->
        <div class="main-content">
            <div class="row">
                <div class="col-12">
                    <div class="card stretch stretch-full">
                        <div class="card-body">
                            <form action="{{ route('permissions.store') }}" method="post" >
                                @csrf
                            <div class="row">
                                <div class="col-lg-12">
                                    {{-- <div class="mb-4">
                                        <h5 class="fw-bold">Add Items:</h5>
                                        <span class="fs-12 text-muted">Add items to proposal</span>
                                    </div> --}}
                                    <div class="table-responsive">
                                        <table class="table table-bordered overflow-hidden" id="tab_logic">
                                            <thead>
                                                <tr>
                                                    <th class="text-center">#</th>
                                                    <th class="text-center">Name </th>
                                                 </tr>
                                            </thead>
                                            <tbody>
                                                @if(@isset($permissions))
                                                    @foreach ($permissions as $k =>$value )

                                                <tr id="addr{{ $k }}" class="permissionRow">
                                                    <td>{{ $k+1 }}</td>
                                                    <input type="hidden" name="id[]" value="{{ $value->id }}"/>
                                                    <td><input type="text" name="name[]" placeholder="Product Name" class="form-control" value="{{ $value->name }}"></td>
                                                 </tr>

                                                 @endforeach
                                                 @endif
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="d-flex justify-content-end gap-2 mt-3">
                                        <button type="button" id="delete_row" class="btn btn-md bg-soft-danger text-danger">Delete</button>
                                        <button id="add_row" type="button" class="btn btn-md btn-primary">Add Items</button>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-lg-4 mb-4 d-flex justify-content-start">
                                    <a href="{{ route('permissions.index') }}" class="btn btn-secondary">
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
        <!-- [ Main Content ] end -->
    </div>

    <!-- [ Footer ] start -->
    @include('admin.layouts.footer')
    <!-- [ Footer ] end -->
</main>


@endsection

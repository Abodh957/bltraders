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
                    <li class="breadcrumb-item">User Shops</li>
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
                    <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                        <a href="{{ route('user-shops.create') }}" class="btn btn-primary">
                            <i class="feather-plus me-2"></i>
                            <span>Create Shop</span>
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
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover" id="shopsTable" data-url="{{ route('user-shops.data') }}">
                                    <thead>
                                        <tr>
                                            <th>Srno</th>
                                            <th>Shop Name</th>
                                            <th>User Number</th>
                                            <th>City</th>
                                            <th>State</th>
                                            <th>Country</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                            <th class="text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('script')
    <script>
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $('#shopsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: $('#shopsTable').data('url'),
                    type: 'POST',
                    data: function(d) {
                        d.from_date = $('input[name=from_date]').val();
                        d.end_date = $('input[name=end_date]').val();
                    },
                    dataSrc: 'data'
                },
                paging: true,
                pageLength: 5,
                lengthChange: false,
                searching: true,
                columns: [
                    { data: 'srno', name: 'id', orderable: false, searchable: false },
                    { data: 'shop_name', name: 'shop_name' },
                    { data: 'user_number', name: 'user_number', orderable: false },
                    { data: 'city', name: 'city' },
                    { data: 'state', name: 'state' },
                    { data: 'country', name: 'country' },
                    { data: 'status', name: 'status' },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false }
                ]
            });

            $(document).on("change", "#shopsTable .statusChange", function() {
                var dataurl = $(this).data("url");
                var id = $(this).data('id');
                var newStatus = this.checked ? 1 : 0;
                $.ajax({
                    url: dataurl,
                    type: 'POST',
                    data: {
                        id: id,
                        status: newStatus
                    },
                    success: function(response) {
                        $('#shopsTable').DataTable().ajax.reload(null, false);
                    },
                    error: function(xhr, status, error) {
                        alert("Failed to update status. Please try again.");
                    }
                });
            });
        });
    </script>
    @endpush

    @include('admin.layouts.footer')
</main>
@endsection

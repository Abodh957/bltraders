@extends('admin.layouts.app')

@section('content')
<main class="nxl-container">
    <div class="nxl-content">
        <!-- [ page-header ] start -->
        <div class="page-header">
            <div class="page-header-left d-flex align-items-center">
                <div class="page-header-title">
                    <h5 class="m-b-10">Categories</h5>
                </div>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item">Categories</li>
                </ul>
            </div>
            <div class="page-header-right ms-auto">
                <div class="page-header-right-items">
                    <div class="d-flex d-md-none">
                        <a href="{{ route('categories.index') }}" class="page-header-right-close-toggle">
                            <i class="feather-arrow-left me-2"></i>
                            <span>Back</span>
                        </a>
                    </div>
                    <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                        <a href="{{ route('categories.create') }}" class="btn btn-primary">
                            <i class="feather-plus me-2"></i>
                            <span>Create Categories</span>
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
                <div class="col-lg-12">
                    <div class="card stretch stretch-full">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover" id="CategoriesTable" data-url="{{ route('categories.data') }}">
                                    <thead>
                                        <tr>
                                            <th>Srno</th>
                                            <th>Name</th>
                                            <Th>Image</Th>
                                            <Th>Description</Th>
                                            <th>Status</th>
                                            <th>Date</th>
                                            <th class="text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Dynamic rows will be loaded here by DataTable -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- [ Main Content ] end -->
    </div>

    @push('script')
    <script>
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $('#CategoriesTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: $('#CategoriesTable').data('url'),
                    type: 'POST',
                    data: function(d) {
                        // Add CSRF token and additional data
                        d.from_date = $('input[name=from_date]').val();
                        d.end_date = $('input[name=end_date]').val();
                    },
                    dataSrc: 'data' // Adjust based on your server response structure
                },
                paging: true,
                pageLength: 5,
                lengthChange: false,
                searching: true,
                columns: [
                    { data: 'srno', name: 'id', orderable: false, searchable: false },
                    { data: 'name', name: 'name' },
                    { data: 'image', name: 'image' },
                    { data: 'description', name: 'description' },
                    { data: 'status', name: 'status' },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false }
                ]
            });

            // Status change event
            $(document).on("change", ".statusChange", function() {
                var dataurl = $(this).data("url");
                var id = $(this).data('id');
              //  var newStatus = $(this).val();
                var newStatus = this.checked ? 1 : 0;
                $.ajax({
                    url: dataurl,
                    type: 'POST',
                    data: {
                        id:id,
                        status: newStatus
                    },
                    success: function(response) {
                        // Handle success response
                        $('#CategoriesTable').DataTable().ajax.reload(null, false); // Reload the table without resetting the pagination
                    },
                    error: function(xhr, status, error) {
                        // Handle error response
                        alert("Failed to update status. Please try again.");
                    }
                });
            });
        });
    </script>

    @endpush

    <!-- [ Footer ] start -->
    <!-- [ Footer ] end -->
    @include('admin.layouts.footer')
</main>
@endsection

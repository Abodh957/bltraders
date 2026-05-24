@extends('admin.layouts.app')

@section('content')
<main class="nxl-container">
    <div class="nxl-content">
        <div class="page-header">
            <div class="page-header-left d-flex align-items-center">
                <div class="page-header-title">
                    <h5 class="m-b-10">Products</h5>
                </div>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item">Products</li>
                </ul>
            </div>
            <div class="page-header-right ms-auto">
                <div class="page-header-right-items">
                    <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                        <button type="button" id="bulkDeleteBtn" class="btn btn-danger d-none">
                            <i class="feather-trash-2 me-2"></i>Delete Selected
                        </button>
                        <a href="{{ route('products.create') }}" class="btn btn-primary">
                            <i class="feather-plus me-2"></i>Add Product
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="main-content">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card stretch stretch-full">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover" id="ProductsTable" data-url="{{ route('products.data') }}">
                                    <thead>
                                        <tr>
                                            <th><input type="checkbox" id="selectAll"></th>
                                            <th>Sr.</th>
                                            <th>Image</th>
                                            <th>Name</th>
                                            <th>Category</th>
                                            <th>Price</th>
                                            <th>Stock</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                            <th class="text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
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
        $(document).ready(function () {
            $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

            var table = $('#ProductsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: { url: $('#ProductsTable').data('url'), type: 'POST' },
                paging: true,
                pageLength: 10,
                lengthChange: false,
                searching: true,
                columns: [
                    { data: 'checkbox',   orderable: false, searchable: false },
                    { data: 'srno',       orderable: false, searchable: false },
                    { data: 'image',      orderable: false, searchable: false },
                    { data: 'name',       name: 'name' },
                    { data: 'category',   orderable: false, searchable: false },
                    { data: 'price',      name: 'price' },
                    { data: 'stock',      name: 'stock' },
                    { data: 'status',     name: 'status' },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'actions',    orderable: false, searchable: false }
                ]
            });

            $('#selectAll').on('change', function () {
                $('.row-checkbox').prop('checked', this.checked);
                toggleBulkDelete();
            });
            $(document).on('change', '.row-checkbox', toggleBulkDelete);

            function toggleBulkDelete() {
                $('#bulkDeleteBtn').toggleClass('d-none', $('.row-checkbox:checked').length === 0);
            }

            function showToast(icon, title) {
                Swal.fire({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, timerProgressBar: true, icon: icon, title: title });
            }

            $('#bulkDeleteBtn').on('click', function () {
                var ids = $('.row-checkbox:checked').map(function () { return $(this).val(); }).get();
                if (!ids.length) return;
                if (!confirm('Delete ' + ids.length + ' selected product(s)?')) return;
                $.ajax({
                    url: '{{ route("products.bulkDelete") }}', type: 'POST', data: { ids: ids },
                    success: function (res) {
                        if (res.success) {
                            table.ajax.reload(null, false);
                            $('#selectAll').prop('checked', false);
                            $('#bulkDeleteBtn').addClass('d-none');
                            showToast('success', res.message);
                        }
                    },
                    error: function () { showToast('error', 'Failed to delete.'); }
                });
            });

            $(document).on('click', '.delete-btn', function () {
                var id = $(this).data('id');
                if (!confirm('Are you sure you want to delete this product?')) return;
                $.ajax({
                    url: '{{ url("admin/products") }}/' + id, type: 'POST', data: { _method: 'DELETE' },
                    success: function (res) {
                        if (res.success) { table.ajax.reload(null, false); showToast('success', res.message); }
                    },
                    error: function () { showToast('error', 'Failed to delete.'); }
                });
            });

            $(document).on('change', '.statusChange', function () {
                $.ajax({
                    url: '{{ route("products.statusChange") }}', type: 'POST',
                    data: { id: $(this).data('id'), status: this.checked ? 1 : 0 },
                    success: function () { table.ajax.reload(null, false); },
                    error: function () { alert('Failed to update status.'); }
                });
            });
        });
    </script>
    @endpush

    @include('admin.layouts.footer')
</main>
@endsection

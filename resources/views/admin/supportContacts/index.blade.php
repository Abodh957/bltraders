@extends('admin.layouts.app')
@section('content')
<main class="nxl-container">
    <div class="nxl-content">
        <div class="page-header">
            <div class="page-header-left d-flex align-items-center">
                <div class="page-header-title"><h5 class="m-b-10">Support Contacts</h5></div>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item">Support Contacts</li>
                </ul>
            </div>
            <div class="page-header-right ms-auto">
                <div class="page-header-right-items">
                    <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                        <a href="{{ route('support-contacts.create') }}" class="btn btn-primary">
                            <i class="feather-plus me-2"></i><span>Add Contact</span>
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
                                <table class="table table-hover" id="ContactsTable" data-url="{{ route('support-contacts.data') }}">
                                    <thead>
                                        <tr>
                                            <th>Sr.</th>
                                            <th>Name</th>
                                            <th>Phone No.</th>
                                            <th>Designation</th>
                                            <th>Order</th>
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

            var table = $('#ContactsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: { url: $('#ContactsTable').data('url'), type: 'POST' },
                paging: true,
                pageLength: 10,
                lengthChange: false,
                searching: true,
                columns: [
                    { data: 'srno',        name: 'id',   orderable: false, searchable: false },
                    { data: 'name',        name: 'name' },
                    { data: 'phone',       name: 'phone' },
                    { data: 'designation', name: 'designation' },
                    { data: 'sort_order',  name: 'sort_order' },
                    { data: 'status',      name: 'status' },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'actions',    name: 'actions', orderable: false, searchable: false }
                ]
            });

            function showToast(icon, title) {
                Swal.fire({ toast: true, position: 'top-end', showConfirmButton: false,
                            timer: 3000, timerProgressBar: true, icon: icon, title: title });
            }

            $(document).on('click', '.delete-btn', function () {
                var id = $(this).data('id');
                if (!confirm('Are you sure you want to delete this contact?')) return;

                $.ajax({
                    url: '{{ url("admin/support-contacts") }}/' + id,
                    type: 'POST',
                    data: { _method: 'DELETE' },
                    success: function (res) {
                        table.ajax.reload(null, false);
                        showToast('success', res.message || 'Contact deleted successfully.');
                    },
                    error: function (xhr) {
                        showToast('error', (xhr.responseJSON && xhr.responseJSON.message) || 'Failed to delete.');
                    }
                });
            });

            $(document).on('change', '.statusChange', function () {
                var id = $(this).data('id');
                var status = this.checked ? 1 : 0;
                $.ajax({
                    url: '{{ route("pages.statusChange") }}',
                    type: 'POST',
                    data: { id: id, status: status },
                    success: function () { table.ajax.reload(null, false); },
                    error: function () { showToast('error', 'Failed to update status.'); }
                });
            });
        });
    </script>
    @endpush

    @include('admin.layouts.footer')
</main>
@endsection

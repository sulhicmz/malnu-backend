@extends('admin.layouts.app')
@section('title', 'Permissions')

@section('content')
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">Permissions</h4>
            <a href="{{ route('permissions.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i> Create Permission
            </a>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card shadow-sm border-0">
            <div class="card-body table-responsive-sm">
                <table id="permissions-table" class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Assigned Users</th>
                            <th>Roles</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                </table>

            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    $(function () {
        $('#permissions-table').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: '{{ route("permissions.datatable") }}',
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                { data: 'name', name: 'name' },
                { data: 'assigned_users', name: 'assigned_users', orderable: false, searchable: false },
                { data: 'roles', name: 'roles', orderable: false, searchable: false },
                { data: 'actions', name: 'actions', orderable: false, searchable: false },
            ]
        });
    });
</script>
@endpush
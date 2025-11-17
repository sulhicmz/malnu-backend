<!-- resources/views/admin/pages/users/index.blade.php -->
@extends('admin.layouts.app')
@section('title', 'Users')
@section('content')
    <div class="container mx-auto mt-6 bg-white p-6 rounded-lg shadow-sm border border-gray-100">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-semibold text-gray-800">Users Management</h2>
            <a href="{{ route('users.create') }}"
                class="flex items-center bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">
                <i class="fas fa-plus mr-2"></i> Add User
            </a>
        </div>
        <div class="overflow-x-auto">
            <table id="usersTable" class="w-full text-left text-gray-700">
                <thead>
                    <tr class="bg-gray-50 text-xs uppercase text-gray-500">
                        <th class="px-6 py-3">#</th>
                        <th class="px-6 py-3">Name</th>
                        <th class="px-6 py-3">Email</th>
                        <th class="px-6 py-3">Roles</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200"></tbody>
            </table>
        </div>
    </div>

    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $(document).ready(function() {
            $('#usersTable').DataTable({
                ajax: {
                    url: '{{ route('users.data') }}',
                    dataSrc: ''
                },
                columns: [{
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: (data, type, row, meta) => meta.row + 1
                    }, {
                        data: 'name'
                    },
                    {
                        data: 'email',
                        render: data =>
                            `<a href="mailto:${data}" class="text-blue-600 hover:underline">${data}</a>`
                    },
                    {
                        data: 'roles',
                        render: data => data?.length ? data.map(r =>
                            `<span class="bg-gray-100 rounded-full px-2 py-1 text-xs text-gray-700 mr-1">${r.name}</span>`
                        ).join('') : '<span class="text-gray-400 text-xs">No roles</span>'
                    },
                    {
                        data: 'is_active',
                        render: data =>
                            `<span class="px-2 py-1 text-xs rounded-full ${data ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">${data ? 'Active' : 'Inactive'}</span>`
                    },
                    {
                        data: null,
                        orderable: false,
                        render: data => `
                            <div class="relative text-right">
                                <button onclick="toggleMenu(this)" class="p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-full">
                                    <i class="fas fa-ellipsis-vertical fa-lg"></i>
                                </button>
                                <div class="menu hidden absolute right-0 mt-2 w-36 bg-white rounded-md shadow-lg border z-10">
                                    <a href="/home/users/${data.id}/edit" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                        <i class="fas fa-edit mr-2 text-blue-500"></i> Edit
                                    </a>
                                    <form action="/home/users/${data.id}" method="POST" onsubmit="return confirm('Delete this user?')">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <input type="hidden" name="_method" value="DELETE">
                                        <button class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-50">
                                            <i class="fas fa-trash mr-2"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        `
                    }
                ],
                responsive: true,
                pageLength: 10
            });
        });

        function toggleMenu(button) {
            const menu = button.nextElementSibling;
            menu.classList.toggle('hidden');
            document.querySelectorAll('.menu').forEach(m => m !== menu && m.classList.add('hidden'));
        }

        document.addEventListener('click', e => {
            if (!e.target.closest('.relative')) {
                document.querySelectorAll('.menu').forEach(m => m.classList.add('hidden'));
            }
        });
    </script>
@endsection

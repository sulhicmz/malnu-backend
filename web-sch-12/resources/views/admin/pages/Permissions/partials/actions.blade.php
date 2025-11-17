<div class="d-flex flex-wrap gap-2">
    <a href="{{ route('permissions.edit', $permission->id) }}" class="btn btn-sm btn-warning">
        <i class="bi bi-pencil-square"></i> Edit
    </a>
    <form action="{{ route('permissions.destroy', $permission->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?');">
        @csrf @method('DELETE')
        <button class="btn btn-sm btn-danger">
            <i class="bi bi-trash"></i> Delete
        </button>
    </form>
    <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#assignUserModal{{ $permission->id }}">
        <i class="bi bi-person-plus"></i> Assign User
    </button>
    <button class="btn btn-sm btn-secondary" data-bs-toggle="modal" data-bs-target="#assignRoleModal{{ $permission->id }}">
        <i class="bi bi-shield-lock"></i> Assign Role
    </button>
</div>

@include('admin.pages.Permissions.partials.modals', ['permission' => $permission])

@extends('admin.layouts.app')
@section('title', 'Edit Permission')

@section('content')
    <div class="container py-4">
        <div class="card card-body">
            <h4>Edit Permission</h4>

            <form action="{{ route('permissions.update', $permission) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="name" class="form-label">Permission Name</label>
                    <input type="text" class="form-control" id="name" name="name" value="{{ $permission->name }}"
                        required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Assign to Roles</label>
                    <div class="row">
                        @foreach ($roles as $role)
                            <div class="col-md-3 col-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="roles[]"
                                        value="{{ $role->id }}" id="role_{{ $role->id }}"
                                        {{ $permission->roles->contains($role->id) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="role_{{ $role->id }}">
                                        {{ $role->name }}
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <button class="btn btn-primary">Update</button>
                <a href="{{ route('permissions.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
@endsection

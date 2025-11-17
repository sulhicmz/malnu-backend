<?php
namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;

class PermissionController extends Controller
{
    public function index()
    {
        $permissions = Permission::with(['users', 'roles'])->get();
        $users       = User::all();
        $roles       = Role::all();
        return view('admin.pages.Permissions.index', compact('permissions', 'users', 'roles'));
    }

    public function create()
    {
        $roles = Role::all();
        return view('admin.pages.Permissions.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'    => 'required|string|unique:permissions,name',
            'roles'   => 'nullable|array',
            'roles.*' => 'exists:roles,id',
        ]);

        $permission = Permission::create(['name' => $request->name]);

        if ($request->filled('roles')) {
            $roles = Role::whereIn('id', $request->roles)->get();
            foreach ($roles as $role) {
                $role->givePermissionTo($permission);
            }
        }

        return redirect()->route('permissions.index')->with('success', 'Permission created.');
    }

    public function show(Permission $permission)
    {
        $users = User::all();
        $roles = $permission->roles;
        return view('admin.pages.Permissions.show', compact('permission', 'users', 'roles'));
    }

    public function edit(Permission $permission)
    {
        $roles         = Role::all();
        $assignedRoles = $permission->roles->pluck('id')->toArray();
        return view('admin.pages.Permissions.edit', compact('permission', 'roles', 'assignedRoles'));
    }

    public function update(Request $request, Permission $permission)
    {
        $request->validate([
            'name'    => 'required|string|unique:permissions,name,' . $permission->id,
            'roles'   => 'nullable|array',
            'roles.*' => 'exists:roles,id',
        ]);

        $permission->update(['name' => $request->name]);

        // Sinkronisasi ulang permission ke role
        if ($request->filled('roles')) {
            // Ambil semua role yang sebelumnya punya permission ini
            $oldRoles = $permission->roles;
            foreach ($oldRoles as $oldRole) {
                $oldRole->revokePermissionTo($permission);
            }

            // Assign ke role baru
            $newRoles = Role::whereIn('id', $request->roles)->get();
            foreach ($newRoles as $role) {
                $role->givePermissionTo($permission);
            }
        } else {
            // Jika tidak ada role, revoke semuanya
            foreach ($permission->roles as $role) {
                $role->revokePermissionTo($permission);
            }
        }

        return redirect()->route('permissions.index')->with('success', 'Permission updated.');
    }

    public function destroy(Permission $permission)
    {
        $permission->delete();

        return redirect()->route('permissions.index')->with('success', 'Permission deleted.');
    }

    public function assignToUser(Request $request, Permission $permission)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::findOrFail($request->user_id);
        $user->givePermissionTo($permission);

        return back()->with('success', "Permission '{$permission->name}' assigned to user '{$user->name}'.");
    }

    public function revokeFromUser(Request $request, Permission $permission)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::findOrFail($request->user_id);
        $user->revokePermissionTo($permission);

        return back()->with('success', "Permission '{$permission->name}' revoked from user '{$user->name}'.");
    }

    public function assignToRole(Request $request, Permission $permission)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id',
        ]);

        $role = Role::findOrFail($request->role_id);
        $role->givePermissionTo($permission);

        return back()->with('success', "Permission '{$permission->name}' assigned to role '{$role->name}'.");
    }

    public function revokeFromRole(Request $request, Permission $permission)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id',
        ]);

        $role = Role::findOrFail($request->role_id);
        $role->revokePermissionTo($permission);

        return back()->with('success', "Permission '{$permission->name}' revoked from role '{$role->name}'.");
    }
    public function datatable(Request $request)
    {
        $permissions = Permission::with(['users', 'roles'])->select('permissions.*');

        return DataTables::of($permissions)
            ->addIndexColumn()
            ->addColumn('assigned_users', fn($permission) => $permission->users->count() . ' user(s)')
            ->addColumn('roles', function ($permission) {
                // return $permission->roles->map(fn($r) => '<span class="badge bg-secondary">' . $r->name . '</span>')->implode(' ');
                return $permission->roles->map(fn($r) => '<span class="badge bg-secondary me-1 mb-1 d-inline-block">' . $r->name . '</span>')->implode('');

            })
            ->addColumn('actions', function ($permission) {
                return view('admin.pages.Permissions.partials.actions', compact('permission'))->render();
            })
            ->rawColumns(['roles', 'actions']) // roles pakai HTML
            ->make(true);
    }
}

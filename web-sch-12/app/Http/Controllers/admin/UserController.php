<?php
namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

// Ensure Role model is imported

class UserController extends Controller
{
    /**
     * Display the users index page.
     * @return \Illuminate\View\View
     */
    public function indexView()
    {
        return view('admin.pages.users.index');
    }

    /**
     * Fetch all users for DataTables.
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $users = User::with('roles')->get([
                'id', 'name', 'username', 'email', 'phone', 'is_active',
            ]);

            // \Log::info('Users data:', $users->toArray());
            return response()->json($users);
        } catch (\Exception $e) {
            \Log::error('Error in users index: ' . $e->getMessage());
            return response()->json(['error' => 'Server error'], 500);
        }
    }

    /**
     * Show the create user form.
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $roles = Role::select('id', 'name')->get();
        return view('admin.pages.users.create', compact('roles'));
    }

    /**
     * Store a new user.
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'username'  => 'required|string|max:255|unique:users,username',
            'email'     => 'required|email|max:255|unique:users,email',
            'password'  => 'required|string|min:8|confirmed',
            'phone'     => 'nullable|string|max:20',
            'roles'     => 'required|array|min:1',
            'roles.*'   => 'exists:roles,id',
            'is_active' => 'nullable|in:on',
        ]);

        try {
            $user = User::create([
                'name'      => $validated['name'],
                'username'  => $validated['username'],
                'email'     => $validated['email'],
                'password'  => Hash::make($validated['password']),
                'phone'     => $validated['phone'],
                'slug'      => Str::slug($validated['username']),
                'is_active' => $request->has('is_active'),
            ]);

            $roles = Role::whereIn('id', $validated['roles'])->get();
            $user->syncRoles($roles);

            return redirect()->route('users.index')
                ->with('success', 'User created successfully.');
        } catch (\Exception $e) {
            \Log::error('Error creating user: ' . $e->getMessage());
            return redirect()->back()
                ->withErrors(['error' => 'Failed to create user: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Show the edit user form.
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $user      = User::findOrFail($id);
        $roles     = Role::select('id', 'name')->get();
        $userRoles = $user->roles->pluck('id')->toArray();
        return view('admin.pages.users.edit', compact('user', 'roles', 'userRoles'));
    }

    /**
     * Update an existing user.
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'username'  => 'required|string|max:255|unique:users,username,' . $id,
            'email'     => 'required|email|max:255|unique:users,email,' . $id,
            'password'  => 'nullable|string|min:8|confirmed',
            'phone'     => 'nullable|string|max:20',
            'roles'     => 'required|array|min:1',
            'roles.*'   => 'exists:roles,id',
            'is_active' => 'nullable|in:on',
        ]);

        try {
            $data = [
                'name'      => $validated['name'],
                'username'  => $validated['username'],
                'email'     => $validated['email'],
                'phone'     => $validated['phone'],
                'slug'      => Str::slug($validated['username']),
                'is_active' => $request->has('is_active'),
            ];

            if (! empty($validated['password'])) {
                $data['password'] = Hash::make($validated['password']);
            }

            $user->update($data);

            $roles = Role::whereIn('id', $validated['roles'])->get();
            $user->syncRoles($roles);

            return redirect()->route('users.index')
                ->with('success', 'User updated successfully.');
        } catch (\Exception $e) {
            \Log::error('Error updating user: ' . $e->getMessage());
            return redirect()->back()
                ->withErrors(['error' => 'Failed to update user.'])
                ->withInput();
        }
    }

    /**
     * Delete a user.
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        try {
            $user = User::find($id);

            if (! $user) {
                return redirect()->route('users.index')
                    ->withErrors(['error' => 'User not found.']);
            }

            $user->delete();

            return redirect()->route('users.index')
                ->with('success', 'User deleted successfully.');
        } catch (\Exception $e) {
            \Log::error('Error deleting user: ' . $e->getMessage());
            return redirect()->route('users.index')
                ->withErrors(['error' => 'Failed to delete user.']);
        }
    }
    public function list()
    {
        return User::select(['id', 'name', 'email'])->get();
    }
    
}

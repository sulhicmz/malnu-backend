                        <!-- Assign to User Modal -->
                        <div class="modal fade" id="assignUserModal{{ $permission->id }}" tabindex="-1" aria-labelledby="assignUserLabel{{ $permission->id }}" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form method="POST" action="{{ route('permissions.assign', $permission->id) }}">
                                        @csrf
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="assignUserLabel{{ $permission->id }}">
                                                Assign "{{ $permission->name }}" to User
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label for="user_id" class="form-label">Select User</label>
                                                <select name="user_id" class="form-select" required>
                                                    <option value="" disabled selected>Choose user</option>
                                                    @foreach(\App\Models\User::all() as $user)
                                                        <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="submit" class="btn btn-success">
                                                Assign Permission
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Assign to Role Modal -->
                        <div class="modal fade" id="assignRoleModal{{ $permission->id }}" tabindex="-1" aria-labelledby="assignRoleLabel{{ $permission->id }}" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form method="POST" action="{{ route('permissions.assignRole', $permission->id) }}">
                                        @csrf
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="assignRoleLabel{{ $permission->id }}">
                                                Assign "{{ $permission->name }}" to Role
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label for="role_id" class="form-label">Select Role</label>
                                                <select name="role_id" class="form-select" required>
                                                    <option value="" disabled selected>Choose role</option>
                                                    @foreach(\Spatie\Permission\Models\Role::all() as $role)
                                                        <option value="{{ $role->id }}">{{ $role->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="submit" class="btn btn-primary">
                                                Assign to Role
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
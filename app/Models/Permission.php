<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Model;
use App\Models\Role;
use App\Models\RoleHasPermission;

class Permission extends Model
{
    protected string $primaryKey = 'id'; // ✅ ubah dari ?string ke string
    protected string $keyType = 'string';
    public bool $incrementing = false;

    protected array $fillable = [
        'name',
        'guard_name',
    ];

    protected array $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the roles associated with the permission.
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_has_permissions', 'permission_id', 'role_id');
    }

    /**
     * Assign this permission to a role.
     */
    public function assignToRole(Role $role): void
    {
        RoleHasPermission::create([
            'permission_id' => $this->id,
            'role_id'       => $role->id,
        ]);
    }

    /**
     * Get permission from cache by ID
     */
    public static function getCached(string $id): ?self
    {
        return \Hyperf\Cache\Cache::instance()->get('permission:' . $id, function () use ($id) {
            return self::find($id);
        }, 3600);
    }

    /**
     * Get permission from cache by name
     */
    public static function getCachedByName(string $name): ?self
    {
        return \Hyperf\Cache\Cache::instance()->get('permission:name:' . $name, function () use ($name) {
            return self::where('name', $name)->first();
        }, 3600);
    }

    /**
     * Clear permission cache
     */
    public function clearCache(): void
    {
        \Hyperf\Cache\Cache::instance()->delete('permission:' . $this->id);
        \Hyperf\Cache\Cache::instance()->delete('permission:name:' . $this->name);
    }
}

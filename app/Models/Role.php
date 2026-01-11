<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Model;
use App\Models\Permission;
use App\Models\ModelHasRole;
use App\Models\User;

class Role extends Model
{
    protected string $primaryKey = 'id';
    protected string $keyType = 'string';
    public bool $incrementing = false;
    protected array $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    protected array $fillable = [
        'name',
        'guard_name',
    ];

    /**
     * Get the permissions associated with the role.
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_has_permissions', 'role_id', 'permission_id');
    }

    /**
     * Assign this role to a model (e.g., User).
     */
    public function assignTo($user): void
    {
        if ($user instanceof User) {
            ModelHasRole::create([
                'role_id' => $this->id,
                'model_type' => get_class($user),
                'model_id' => $user->id,
            ]);
        } else {
            throw new \InvalidArgumentException('Expected instance of User');
        }
    }

    /**
     * Get role from cache by ID
     */
    public static function getCached(string $id): ?self
    {
        return \Hyperf\Cache\Cache::instance()->get('role:' . $id, function () use ($id) {
            return self::find($id);
        }, 3600);
    }

    /**
     * Get role from cache by name
     */
    public static function getCachedByName(string $name): ?self
    {
        return \Hyperf\Cache\Cache::instance()->get('role:name:' . $name, function () use ($name) {
            return self::where('name', $name)->first();
        }, 3600);
    }

    /**
     * Get role permissions from cache
     */
    public function getCachedPermissions(): array
    {
        return \Hyperf\Cache\Cache::instance()->get('role:' . $this->id . ':permissions', function () {
            return $this->permissions()->pluck('name')->toArray();
        }, 3600);
    }

    /**
     * Clear role cache
     */
    public function clearCache(): void
    {
        \Hyperf\Cache\Cache::instance()->delete('role:' . $this->id);
        \Hyperf\Cache\Cache::instance()->delete('role:name:' . $this->name);
        \Hyperf\Cache\Cache::instance()->delete('role:' . $this->id . ':permissions');
    }
}

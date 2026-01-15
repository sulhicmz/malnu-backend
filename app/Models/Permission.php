<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Model;
use App\Models\Role;
use App\Models\RoleHasPermission;
use Hyperf\Redis\Redis;
use Psr\Container\ContainerInterface;

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

    private ?Redis $redis = null;
    private int $cacheTtl = 3600;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $container = \Hyperf\Context\ApplicationContext::getContainer();
        $this->redis = $container->get(Redis::class);
    }

    /**
     * Get roles associated with permission.
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
        $this->clearCache();
        $role->clearCache();
    }

    public function getCached(string $id): ?self
    {
        $key = $this->getCacheKey($id);
        $data = $this->redis->get($key);

        if ($data) {
            $permission = new self();
            $permission->fill(json_decode($data, true));
            $permission->exists = true;
            return $permission;
        }

        return null;
    }

    public function getCachedByName(string $name): ?self
    {
        $key = 'permission:name:' . $name;
        $data = $this->redis->get($key);

        if ($data) {
            $permission = new self();
            $permission->fill(json_decode($data, true));
            $permission->exists = true;
            return $permission;
        }

        return null;
    }

    public function setCached(): void
    {
        $key = $this->getCacheKey($this->id);
        $nameKey = 'permission:name:' . $this->name;

        $data = json_encode($this->toArray());
        $this->redis->setex($key, $this->cacheTtl, $data);
        $this->redis->setex($nameKey, $this->cacheTtl, $data);
    }

    public function clearCache(): void
    {
        $key = $this->getCacheKey($this->id);
        $nameKey = 'permission:name:' . $this->name;

        $this->redis->del($key);
        $this->redis->del($nameKey);
    }

    protected function getCacheKey(string $id): string
    {
        return 'permission:' . $id;
    }
}

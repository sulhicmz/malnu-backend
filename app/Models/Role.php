<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Model;
use App\Models\Permission;
use App\Models\ModelHasRole;
use App\Models\User;
use Hyperf\Redis\Redis;
use Psr\Container\ContainerInterface;

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

    private ?Redis $redis = null;
    private int $cacheTtl = 3600;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $container = \Hyperf\Context\ApplicationContext::getContainer();
        $this->redis = $container->get(Redis::class);
    }

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
            $this->clearCache();
        } else {
            throw new \InvalidArgumentException('Expected instance of User');
        }
    }

    public function getCached(string $id): ?self
    {
        $key = $this->getCacheKey($id);
        $data = $this->redis->get($key);

        if ($data) {
            $role = new self();
            $role->fill(json_decode($data, true));
            $role->exists = true;
            return $role;
        }

        return null;
    }

    public function getCachedByName(string $name): ?self
    {
        $key = 'role:name:' . $name;
        $data = $this->redis->get($key);

        if ($data) {
            $role = new self();
            $role->fill(json_decode($data, true));
            $role->exists = true;
            return $role;
        }

        return null;
    }

    public function setCached(): void
    {
        $key = $this->getCacheKey($this->id);
        $nameKey = 'role:name:' . $this->name;

        $data = json_encode($this->toArray());
        $this->redis->setex($key, $this->cacheTtl, $data);
        $this->redis->setex($nameKey, $this->cacheTtl, $data);
    }

    public function clearCache(): void
    {
        $key = $this->getCacheKey($this->id);
        $nameKey = 'role:name:' . $this->name;

        $this->redis->del($key);
        $this->redis->del($nameKey);
    }

    protected function getCacheKey(string $id): string
    {
        return 'role:' . $id;
    }
}

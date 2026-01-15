<?php

declare(strict_types=1);

namespace App\Models;

use InvalidArgumentException;

class Role extends Model
{
    public bool $incrementing = false;

    protected string $primaryKey = 'id';

    protected string $keyType = 'string';

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
     * @param mixed $user
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
            throw new InvalidArgumentException('Expected instance of User');
        }
    }
}

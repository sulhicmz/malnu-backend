<?php

declare(strict_types=1);

namespace App\Models;

class RoleHasPermission extends Model
{
    protected string $primaryKey = 'id';
    protected string $keyType = 'string';
    public bool $incrementing = false;
    public bool $timestamps = false;
    protected array $fillable = [
        'permission_id',
        'role_id',
    ];

    protected array $casts = [];
}

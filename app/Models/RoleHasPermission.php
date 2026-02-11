<?php

declare(strict_types=1);

namespace App\Models;

class RoleHasPermission extends Model
{
    public bool $timestamps = false; // ✅

    protected array $fillable = [
        'permission_id',
        'role_id',
    ];

    protected array $casts = [];
}

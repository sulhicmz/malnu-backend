<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Model;

class RoleHasPermission extends Model
{
    protected string $primaryKey = 'id';
    protected string $keyType = 'string';
    public bool $incrementing = false;
    protected array $fillable = [
        'permission_id',
        'role_id',
    ];
    public bool $timestamps = false; // ✅
    protected array $casts = [];
}
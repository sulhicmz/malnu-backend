<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Model;

class ModelHasPermission extends Model
{
    protected string $primaryKey = 'id'; // ✅ ubah dari ?string ke string
    protected string $keyType = 'string';
    public bool $incrementing = false;
    protected array $fillable = [
        'permission_id',
        'model_type',
        'model_id',
    ];

    protected array $casts = [];
    public bool $timestamps = true;
}
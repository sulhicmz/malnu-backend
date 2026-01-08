<?php

declare(strict_types=1);

namespace App\Models;

class ModelHasPermission extends Model
{
    public bool $timestamps = true;

    protected array $fillable = [
        'permission_id',
        'model_type',
        'model_id',
    ];

    protected array $casts = [];
}

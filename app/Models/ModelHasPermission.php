<?php

declare(strict_types=1);

namespace App\Models;

use Hypervel\Database\Model\Model;

class ModelHasPermission extends Model
{
    public bool $incrementing = false;

    public bool $timestamps = true;

    protected string $primaryKey = 'id'; // ✅ ubah dari ?string ke string

    protected string $keyType = 'string';

    protected array $fillable = [
        'permission_id',
        'model_type',
        'model_id',
    ];

    protected array $casts = [];
}

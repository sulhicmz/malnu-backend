<?php

declare (strict_types = 1);

namespace App\Models;

use App\Models\Model;

class ModelHasRole extends Model
{
    protected string $primaryKey = 'id';
    protected string $keyType = 'string';
    public bool $incrementing = false;
    protected array $fillable = [
        'role_id',
        'model_type',
        'model_id',
    ];

    protected array $casts = [];
    // public $timestamps = false;
}

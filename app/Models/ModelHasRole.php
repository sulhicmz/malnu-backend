<?php

declare(strict_types=1);

namespace App\Models;

class ModelHasRole extends Model
{
    protected array $fillable = [
        'role_id',
        'model_type',
        'model_id',
    ];

    protected array $casts = [];
    // public $timestamps = false;
}

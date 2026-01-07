<?php

declare(strict_types=1);

namespace App\Models;

use Hyperf\Database\Eloquent\Model as BaseModel;

abstract class Model extends BaseModel
{
    public bool $incrementing = false;

    protected ?string $connection = null;

    protected string $primaryKey = 'id';

    protected string $keyType = 'string';
}

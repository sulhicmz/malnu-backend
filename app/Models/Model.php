<?php

declare(strict_types=1);

namespace App\Models;

use Hyperf\Database\Model as BaseModel;

abstract class Model extends BaseModel
{
    protected ?string $connection = null;

    public int $id;
    public string $created_at;
    public string $updated_at;
    public ?string $deleted_at;
}

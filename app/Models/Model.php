<?php

declare(strict_types=1);

namespace App\Models;

use Hyperf\Database\Eloquent\Model as BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

abstract class Model extends BaseModel
{
    use SoftDeletes;

    protected ?string $connection = null;
}

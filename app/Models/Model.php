<?php

declare(strict_types=1);

namespace App\Models;

use Hyperf\Database\Model as BaseModel;
use Hyperf\Database\Model\SoftDeletes;

abstract class Model extends BaseModel
{
    use SoftDeletes;

    protected ?string $connection = null;
}

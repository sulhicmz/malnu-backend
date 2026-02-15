<?php

declare(strict_types=1);

namespace App\Models;

use Hypervel\Database\Model\Model as BaseModel;
use Hypervel\Database\Model\SoftDeletes;

abstract class Model extends BaseModel
{
    use SoftDeletes;

    protected ?string $connection = null;
}

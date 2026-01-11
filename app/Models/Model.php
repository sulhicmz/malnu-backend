<?php

declare(strict_types=1);

namespace App\Models;

use Hyperf\Database\Model\Model as EloquentModel;

abstract class Model extends EloquentModel
{
    protected ?string $connection = null;
}

<?php

declare(strict_types=1);

namespace App\Models;

use Hyperf\Database\Model as BaseModel;

abstract class Model extends BaseModel
{
    /**
     * Common Eloquent properties (documented for PHPStan).
     *
     * @var null|string
     */
    public $id;

    /**
     * @var null|\Carbon\Carbon
     */
    public $created_at;

    /**
     * @var null|\Carbon\Carbon
     */
    public $updated_at;

    /**
     * @var null|string
     */
    public $deleted_at;

    protected ?string $connection = null;
}

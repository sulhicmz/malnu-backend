<?php

declare(strict_types=1);

namespace App\Traits;

use Hyperf\Database\Model\Builder;

trait ModelHelpers
{
    public static function find(int|string $id): mixed
    {
        return static::query()->where('id', $id)->first();
    }

    public static function findOrFail(int|string $id): mixed
    {
        return static::query()->where('id', $id)->firstOrFail();
    }

    public static function where(string $column, mixed $operator = null, mixed $value = null): Builder
    {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }

        return static::query()->where($column, $operator, $value);
    }

    public static function create(array $attributes): mixed
    {
        $model = new static();
        $model->fill($attributes);
        $model->save();
        return $model;
    }
}

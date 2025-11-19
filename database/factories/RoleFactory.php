<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Role;
use Hypervel\Database\Eloquent\Factories\Factory;

class RoleFactory extends Factory
{
    protected $model = Role::class;

    public function definition(): array
    {
        return [
            'id' => \Ramsey\Uuid\Uuid::uuid4()->toString(),
            'name' => $this->faker->unique()->word(),
            'guard_name' => 'web',
        ];
    }
}
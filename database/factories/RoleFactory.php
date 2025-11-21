<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Role;
use Hypervel\Database\Schema\Factories\Factory;
use Illuminate\Support\Str;

class RoleFactory extends Factory
{
    protected $model = Role::class;

    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'name' => $this->faker->unique()->word(),
            'guard_name' => 'web',
        ];
    }
}
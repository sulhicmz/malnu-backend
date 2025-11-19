<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Permission;
use Hypervel\Database\Schema\Factories\Factory;
use Illuminate\Support\Str;

class PermissionFactory extends Factory
{
    protected $model = Permission::class;

    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'name' => $this->faker->unique()->word(),
            'guard_name' => 'web',
        ];
    }
}
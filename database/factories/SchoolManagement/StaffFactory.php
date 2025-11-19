<?php

declare(strict_types=1);

namespace Database\Factories\SchoolManagement;

use App\Models\SchoolManagement\Staff;
use Hypervel\Database\Eloquent\Factories\Factory;

class StaffFactory extends Factory
{
    protected $model = Staff::class;

    public function definition(): array
    {
        return [
            'id' => \Ramsey\Uuid\Uuid::uuid4()->toString(),
            'user_id' => null,
            'position' => $this->faker->jobTitle(),
            'department' => $this->faker->word(),
            'join_date' => $this->faker->date(),
            'status' => 'active',
        ];
    }
}
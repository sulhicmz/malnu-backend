<?php

declare(strict_types=1);

namespace Database\Factories\SchoolManagement;

use App\Models\SchoolManagement\Teacher;
use Hypervel\Database\Eloquent\Factories\Factory;

class TeacherFactory extends Factory
{
    protected $model = Teacher::class;

    public function definition(): array
    {
        return [
            'id' => \Ramsey\Uuid\Uuid::uuid4()->toString(),
            'user_id' => null,
            'nip' => $this->faker->unique()->numerify('TEACHER-#####'),
            'expertise' => $this->faker->jobTitle(),
            'join_date' => $this->faker->date(),
            'status' => 'active',
        ];
    }
}
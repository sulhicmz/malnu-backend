<?php

declare(strict_types=1);

namespace Database\Factories\SchoolManagement;

use App\Models\SchoolManagement\Student;
use Hypervel\Database\Eloquent\Factories\Factory;

class StudentFactory extends Factory
{
    protected $model = Student::class;

    public function definition(): array
    {
        return [
            'id' => \Ramsey\Uuid\Uuid::uuid4()->toString(),
            'user_id' => null,
            'nisn' => $this->faker->unique()->numerify('##########'),
            'class_id' => null,
            'birth_date' => $this->faker->date(),
            'birth_place' => $this->faker->city(),
            'address' => $this->faker->address(),
            'parent_id' => null,
            'enrollment_date' => $this->faker->date(),
            'status' => 'active',
        ];
    }
}
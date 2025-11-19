<?php

namespace Database\Factories\SchoolManagement;

use App\Models\SchoolManagement\ClassModel;
use App\Models\SchoolManagement\Teacher;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ClassModel>
 */
class ClassModelFactory extends Factory
{
    protected $model = ClassModel::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->word() . ' ' . $this->faker->numberBetween(1, 12),
            'teacher_id' => Teacher::factory(),
            'description' => $this->faker->sentence(),
            'max_students' => $this->faker->numberBetween(20, 40),
        ];
    }
}
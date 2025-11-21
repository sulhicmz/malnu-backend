<?php

namespace Database\Factories\SchoolManagement;

use App\Models\SchoolManagement\Student;
use App\Models\User;
use App\Models\SchoolManagement\ClassModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Student>
 */
class StudentFactory extends Factory
{
    protected $model = Student::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'class_id' => ClassModel::factory(),
            'nis' => $this->faker->unique()->numerify('############'),
            'nisn' => $this->faker->unique()->numerify('##############'),
            'birth_date' => $this->faker->date(),
            'birth_place' => $this->faker->city(),
            'gender' => $this->faker->randomElement(['male', 'female']),
            'religion' => $this->faker->randomElement(['Islam', 'Protestan', 'Katolik', 'Hindu', 'Buddha', 'Konghucu']),
            'address' => $this->faker->address(),
            'phone' => $this->faker->phoneNumber(),
        ];
    }
}
<?php

namespace Database\Factories\SchoolManagement;

use App\Models\SchoolManagement\Teacher;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Teacher>
 */
class TeacherFactory extends Factory
{
    protected $model = Teacher::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'nip' => $this->faker->unique()->numerify('19##############'),
            'birth_date' => $this->faker->date(),
            'birth_place' => $this->faker->city(),
            'gender' => $this->faker->randomElement(['male', 'female']),
            'religion' => $this->faker->randomElement(['Islam', 'Protestan', 'Katolik', 'Hindu', 'Buddha', 'Konghucu']),
            'address' => $this->faker->address(),
            'phone' => $this->faker->phoneNumber(),
            'subject_expertise' => $this->faker->word(),
        ];
    }
}
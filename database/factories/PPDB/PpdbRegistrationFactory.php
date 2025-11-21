<?php

namespace Database\Factories\PPDB;

use App\Models\PPDB\PpdbRegistration;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PpdbRegistration>
 */
class PpdbRegistrationFactory extends Factory
{
    protected $model = PpdbRegistration::class;

    public function definition(): array
    {
        return [
            'student_name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'parent_name' => $this->faker->name(),
            'parent_phone' => $this->faker->phoneNumber(),
            'address' => $this->faker->address(),
            'status' => $this->faker->randomElement(['pending', 'approved', 'rejected']),
            'registration_number' => $this->faker->unique()->numerify('REG#####'),
        ];
    }
}
<?php

namespace Database\Factories\ParentPortal;

use App\Models\ParentPortal\ParentOrtu;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ParentOrtu>
 */
class ParentOrtuFactory extends Factory
{
    protected $model = ParentOrtu::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'full_name' => $this->faker->name(),
            'phone' => $this->faker->phoneNumber(),
            'occupation' => $this->faker->jobTitle(),
            'address' => $this->faker->address(),
            'relationship' => $this->faker->randomElement(['father', 'mother', 'guardian']),
        ];
    }
}
<?php

declare(strict_types=1);

namespace Database\Factories\SchoolManagement;

use App\Models\SchoolManagement\Staff;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SchoolManagement\Staff>
 */
class StaffFactory extends Factory
{
    protected $model = Staff::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'user_id' => null, // Will be set when creating with user
            'nip' => fake()->unique()->numerify('STAFF#####'),
            'position' => fake()->jobTitle(),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'date_of_birth' => fake()->date(),
            'gender' => fake()->randomElement(['male', 'female']),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
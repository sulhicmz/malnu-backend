<?php

declare(strict_types=1);

namespace Database\Factories\SchoolManagement;

use App\Models\SchoolManagement\Teacher;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SchoolManagement\Teacher>
 */
class TeacherFactory extends Factory
{
    protected $model = Teacher::class;

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
            'nip' => fake()->unique()->numerify('NIP#####'),
            'nuptk' => fake()->unique()->numerify('NUPTK#####'),
            'subject_id' => null,
            'class_id' => null,
            'phone' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'date_of_birth' => fake()->date(),
            'gender' => fake()->randomElement(['male', 'female']),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
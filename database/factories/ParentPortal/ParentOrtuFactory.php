<?php

declare(strict_types=1);

namespace Database\Factories\ParentPortal;

use App\Models\ParentPortal\ParentOrtu;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ParentPortal\ParentOrtu>
 */
class ParentOrtuFactory extends Factory
{
    protected $model = ParentOrtu::class;

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
            'full_name' => fake()->name(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->safeEmail(),
            'address' => fake()->address(),
            'occupation' => fake()->jobTitle(),
            'relationship' => fake()->randomElement(['father', 'mother', 'guardian']),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
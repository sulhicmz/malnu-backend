<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use Carbon\Carbon;
use Hypervel\Database\Schema\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'name' => $this->faker->name(),
            'username' => $this->faker->unique()->userName(),
            'email' => $this->faker->unique()->safeEmail(),
            'full_name' => $this->faker->name(),
            'phone' => $this->faker->phoneNumber(),
            'avatar_url' => $this->faker->imageUrl(),
            'is_active' => true,
            'last_login_time' => Carbon::now(),
            'last_login_ip' => $this->faker->ipv4(),
            'email_verified_at' => Carbon::now(),
            'slug' => $this->faker->slug(),
            'key_status' => 'active',
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'email_verified_at' => null,
            ];
        });
    }
    
    public function inactive(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'is_active' => false,
            ];
        });
    }
}

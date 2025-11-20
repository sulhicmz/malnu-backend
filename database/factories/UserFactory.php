<?php

declare(strict_types=1);

use App\Models\User;
use Carbon\Carbon;
use Faker\Generator as Faker;

$factory->define(User::class, function (Faker $faker) {
    return [
        'name' => $faker->unique()->name(),
        'username' => $faker->unique()->userName(),
        'email' => $faker->unique()->safeEmail(),
        'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
        'full_name' => $faker->name(),
        'email_verified_at' => Carbon::now(),
        'remember_token' => \Illuminate\Support\Str::random(10),
    ];
});

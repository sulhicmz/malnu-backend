<?php

declare(strict_types=1);

use App\Models\Attendance\LeaveType;
use Faker\Generator as Faker;

$factory->define(LeaveType::class, function (Faker $faker) {
    return [
        'name' => $faker->randomElement(['Annual Leave', 'Sick Leave', 'Personal Leave', 'Maternity Leave', 'Paternity Leave']),
        'description' => $faker->sentence(),
        'requires_approval' => $faker->boolean(80),
        'days_per_year' => $faker->numberBetween(10, 30),
        'is_paid' => $faker->boolean(90),
        'carry_forward_allowed' => $faker->boolean(50),
    ];
});

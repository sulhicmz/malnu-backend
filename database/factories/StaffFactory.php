<?php

declare(strict_types=1);

use App\Models\SchoolManagement\Staff;
use App\Models\User;
use Faker\Generator as Faker;

$factory->define(Staff::class, function (Faker $faker) {
    return [
        'user_id' => function () {
            return User::factory()->create()->id;
        },
        'employee_id' => $faker->unique()->numerify('EMP#####'),
        'department' => $faker->randomElement(['Teaching', 'Administration', 'Finance', 'HR', 'IT']),
        'position' => $faker->jobTitle(),
        'hire_date' => $faker->dateTimeBetween('-5 years', '-1 month')->format('Y-m-d'),
        'salary' => $faker->numberBetween(3000000, 15000000),
        'status' => $faker->randomElement(['active', 'inactive', 'on_leave']),
    ];
});

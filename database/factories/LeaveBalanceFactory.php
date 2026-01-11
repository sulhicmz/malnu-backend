<?php

declare(strict_types=1);

use App\Models\Attendance\LeaveBalance;
use App\Models\Attendance\LeaveType;
use App\Models\SchoolManagement\Staff;
use Faker\Generator as Faker;

$factory->define(LeaveBalance::class, function (Faker $faker) {
    return [
        'staff_id' => function () {
            return Staff::inRandomOrder()->first()->id ?? Staff::factory()->create()->id;
        },
        'leave_type_id' => function () {
            return LeaveType::inRandomOrder()->first()->id ?? LeaveType::factory()->create()->id;
        },
        'year' => (int) date('Y'),
        'allocated_days' => $faker->numberBetween(10, 30),
        'used_days' => $faker->numberBetween(0, 10),
        'carry_forward_days' => $faker->numberBetween(0, 5),
    ];
});

<?php

declare(strict_types=1);

use App\Models\Attendance\LeaveRequest;
use App\Models\Attendance\LeaveType;
use App\Models\SchoolManagement\Staff;
use Carbon\Carbon;
use Faker\Generator as Faker;

$factory->define(LeaveRequest::class, function (Faker $faker) {
    $startDate = Carbon::now()->addDays($faker->numberBetween(1, 30));
    $endDate = $startDate->copy()->addDays($faker->numberBetween(1, 10));
    $totalDays = $startDate->diffInDays($endDate) + 1;

    return [
        'staff_id' => function () {
            return Staff::inRandomOrder()->first()->id ?? Staff::factory()->create()->id;
        },
        'leave_type_id' => function () {
            return LeaveType::inRandomOrder()->first()->id ?? LeaveType::factory()->create()->id;
        },
        'start_date' => $startDate->toDateString(),
        'end_date' => $endDate->toDateString(),
        'total_days' => $totalDays,
        'reason' => $faker->sentence(),
        'comments' => $faker->optional(0.5)->sentence(),
        'status' => $faker->randomElement(['pending', 'approved', 'rejected', 'cancelled']),
        'approved_by' => null,
        'approved_at' => null,
        'approval_comments' => null,
        'substitute_assigned_id' => null,
    ];
});

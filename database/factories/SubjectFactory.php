<?php

declare(strict_types=1);

use App\Models\SchoolManagement\Subject;
use Faker\Generator as Faker;

$factory->define(Subject::class, function (Faker $faker) {
    return [
        'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
        'code' => strtoupper($faker->unique()->lexify('SUB???')),
        'name' => $faker->randomElement(['Mathematics', 'Science', 'English', 'History', 'Art', 'Music', 'Physical Education']),
        'description' => $faker->sentence(),
        'credit_hours' => $faker->numberBetween(1, 5),
    ];
});

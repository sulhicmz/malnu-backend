<?php

declare(strict_types=1);

use App\Models\SchoolManagement\ClassModel;
use Faker\Generator as Faker;

$factory->define(ClassModel::class, function (Faker $faker) {
    return [
        'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
        'name' => 'Class ' . $faker->randomNumber(2),
        'level' => (string) $faker->numberBetween(1, 12),
        'academic_year' => '2024-2025',
        'capacity' => $faker->numberBetween(20, 40),
    ];
});

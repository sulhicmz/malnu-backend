<?php

declare(strict_types=1);

use App\Models\SchoolManagement\Student;
use App\Models\User;
use Faker\Generator as Faker;

$factory->define(Student::class, function (Faker $faker) {
    $user = factory(User::class)->create();

    return [
        'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
        'user_id' => $user->id,
        'nisn' => (string) $faker->unique()->randomNumber(9, true),
        'birth_date' => $faker->date(),
        'birth_place' => $faker->city(),
        'address' => $faker->address(),
        'enrollment_date' => $faker->date(),
        'status' => 'active',
    ];
});

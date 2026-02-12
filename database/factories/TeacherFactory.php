<?php

declare(strict_types=1);

use App\Models\SchoolManagement\Teacher;
use App\Models\User;
use Faker\Generator as Faker;

$factory->define(Teacher::class, function (Faker $faker) {
    $user = factory(User::class)->create();

    return [
        'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
        'user_id' => $user->id,
        'nip' => (string) $faker->unique()->randomNumber(9, true),
        'expertise' => $faker->randomElement(['Mathematics', 'Science', 'English', 'History', 'Art']),
        'join_date' => $faker->date(),
        'status' => 'active',
    ];
});

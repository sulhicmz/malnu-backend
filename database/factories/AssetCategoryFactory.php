<?php

declare(strict_types=1);

use App\Models\SchoolManagement\AssetCategory;
use Faker\Generator as Faker;

$factory->define(AssetCategory::class, function (Faker $faker) {
    return [
        'name' => $faker->words(2, true),
        'code' => strtoupper($faker->lexify('???')),
        'description' => $faker->sentence(),
        'is_active' => $faker->boolean(80),
    ];
});

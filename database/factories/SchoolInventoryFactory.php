<?php

declare(strict_types=1);

use App\Models\SchoolManagement\SchoolInventory;
use Faker\Generator as Faker;

$factory->define(SchoolInventory::class, function (Faker $faker) {
    return [
        'name' => $faker->words(3, true),
        'category' => $faker->word(),
        'quantity' => $faker->numberBetween(1, 100),
        'location' => $faker->word(),
        'condition' => $faker->randomElement(['new', 'good', 'fair', 'poor']),
        'purchase_date' => $faker->date(),
        'last_maintenance' => $faker->date(),
        'serial_number' => $faker->uuid(),
        'asset_code' => $faker->numerify('INV-###'),
        'status' => $faker->randomElement(['available', 'assigned', 'maintenance', 'retired']),
        'purchase_cost' => $faker->randomFloat(2, 100, 10000),
        'notes' => $faker->sentence(),
    ];
});

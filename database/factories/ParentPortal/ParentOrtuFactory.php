<?php

declare(strict_types=1);

namespace Database\Factories\ParentPortal;

use App\Models\ParentPortal\ParentOrtu;
use Hypervel\Database\Eloquent\Factories\Factory;

class ParentOrtuFactory extends Factory
{
    protected $model = ParentOrtu::class;

    public function definition(): array
    {
        return [
            'id' => \Ramsey\Uuid\Uuid::uuid4()->toString(),
            'user_id' => null,
            'occupation' => $this->faker->jobTitle(),
            'address' => $this->faker->address(),
        ];
    }
}
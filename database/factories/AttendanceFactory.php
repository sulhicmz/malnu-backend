<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Attendance\Attendance;
use App\Models\SchoolManagement\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition(): array
    {
        return [
            'id' => $this->faker->uuid(),
            'student_id' => Student::factory(),
            'date' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'status' => $this->faker->randomElement(['present', 'absent', 'late', 'excused']),
            'remarks' => $this->faker->optional()->sentence(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function present(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'present',
        ]);
    }

    public function absent(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'absent',
        ]);
    }

    public function late(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'late',
        ]);
    }
}

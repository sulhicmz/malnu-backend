<?php

namespace Database\Factories\ELearning;

use App\Models\ELearning\LearningMaterial;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LearningMaterial>
 */
class LearningMaterialFactory extends Factory
{
    protected $model = LearningMaterial::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'content' => '<p>' . $this->faker->paragraph() . '</p>',
            'created_by' => User::factory(),
            'subject_id' => $this->faker->numberBetween(1, 10),
            'grade_level' => $this->faker->numberBetween(1, 12),
            'status' => $this->faker->randomElement(['draft', 'published', 'archived']),
            'file_path' => $this->faker->filePath(),
        ];
    }
}
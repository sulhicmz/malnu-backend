<?php

declare(strict_types=1);

namespace App\Models\Cafeteria;

use App\Models\Model;

class MealPlan extends Model
{
    protected $table = 'meal_plans';
    protected $fillable = [
        'name',
        'description',
        'start_date',
        'end_date',
        'status',
    ];

    public function studentPreferences()
    {
        return $this->hasMany(StudentMealPreference::class, 'meal_plan_id');
    }
}

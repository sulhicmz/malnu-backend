<?php

declare(strict_types=1);

namespace App\Models\Cafeteria;

use App\Models\Model;
use App\Models\SchoolManagement\Student;

class StudentMealPreference extends Model
{
    protected $table = 'student_meal_preferences';
    protected $fillable = [
        'student_id',
        'meal_plan_id',
        'requires_special_diet',
        'dietary_restrictions',
        'allergies',
        'subsidy_eligible',
        'subsidy_amount',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function mealPlan()
    {
        return $this->belongsTo(MealPlan::class, 'meal_plan_id');
    }
}

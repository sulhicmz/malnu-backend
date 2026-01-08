<?php

declare(strict_types=1);

namespace App\Models\Hostel;

use App\Models\Model;
use App\Models\User;

class MealPlan extends Model
{
    protected $table = 'meal_plans';

    protected $fillable = [
        'hostel_id',
        'student_id',
        'plan_type',
        'dietary_requirements',
        'allergies',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function hostel()
    {
        return $this->belongsTo(Hostel::class, 'hostel_id');
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByHostel($query, $hostelId)
    {
        return $query->where('hostel_id', $hostelId);
    }

    public function scopeByStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('plan_type', $type);
    }

    public function scopeCurrent($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', now());
            });
    }

    public function getIsActiveForDateAttribute($date)
    {
        if (!$this->is_active) {
            return false;
        }
        if ($date < $this->start_date) {
            return false;
        }
        if ($this->end_date && $date > $this->end_date) {
            return false;
        }
        return true;
    }
}

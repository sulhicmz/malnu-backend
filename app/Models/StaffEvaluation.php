<?php

namespace App\Models;

use Hyperf\DbConnection\Model\Model;

class StaffEvaluation extends Model
{
    protected $table = 'staff_evaluations';

    protected $fillable = [
        'staff_id',
        'evaluator_id',
        'evaluation_date',
        'evaluation_type',
        'academic_year',
        'overall_score',
        'rating',
        'strengths',
        'areas_for_improvement',
        'goals',
        'status',
        'reviewer_id',
        'review_date',
        'feedback',
    ];

    protected $casts = [
        'evaluation_date' => 'date',
        'review_date' => 'date',
        'overall_score' => 'decimal:2',
    ];

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    public function evaluator()
    {
        return $this->belongsTo(Staff::class, 'evaluator_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(Staff::class, 'reviewer_id');
    }
}

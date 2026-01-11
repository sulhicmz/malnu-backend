<?php

declare (strict_types = 1);

namespace App\Models\SchoolManagement;

use App\Models\CareerDevelopment\CareerAssessment;
use App\Models\CareerDevelopment\CounselingSession;
use App\Models\Grading\Competency;
use App\Models\Grading\Grade;
use App\Models\Grading\Report;
use App\Models\Grading\StudentPortfolio;
use App\Models\Model;
use App\Models\OnlineExam\ExamResult;
use App\Models\ParentPortal\ParentOrtu;
use App\Models\User;
use App\Models\SchoolManagement\ClassModel;

class Student extends Model
{

    protected $fillable = [
        'user_id',
        'nisn',
        'class_id',
        'birth_date',
        'birth_place',
        'address',
        'parent_id',
        'enrollment_date',
        'status',
    ];

    protected $casts = [
        'birth_date'      => 'date',
        'enrollment_date' => 'date',
        'created_at'      => 'datetime',
        'updated_at'      => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function class ()
    {
        return $this->belongsTo(ClassModel::class);
    }

    public function parent()
    {
        return $this->belongsTo(ParentOrtu::class);
    }

    public function grades()
    {
        return $this->hasMany(Grade::class);
    }

    public function competencies()
    {
        return $this->hasMany(Competency::class);
    }

    public function reports()
    {
        return $this->hasMany(Report::class);
    }

    public function portfolios()
    {
        return $this->hasMany(StudentPortfolio::class);
    }

    public function examResults()
    {
        return $this->hasMany(ExamResult::class);
    }

    public function careerAssessments()
    {
        return $this->hasMany(CareerAssessment::class);
    }

    public function counselingSessions()
    {
        return $this->hasMany(CounselingSession::class);
    }
}

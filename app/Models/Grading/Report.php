<?php

declare(strict_types=1);

namespace App\Models\Grading;

use App\Models\Model;
use App\Models\SchoolManagement\ClassModel;
use App\Models\SchoolManagement\Student;
use App\Models\User;

class Report extends Model
{
    public $incrementing = false;

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    protected $fillable = [
        'student_id',
        'class_id',
        'semester',
        'academic_year',
        'average_grade',
        'rank_in_class',
        'homeroom_notes',
        'principal_notes',
        'file_path',
        'file_type',
        'template_id',
        'is_published',
        'published_at',
        'created_by',
    ];

    protected $casts = [
        'average_grade' => 'decimal:2',
        'semester' => 'integer',
        'is_published' => 'boolean',
        'published_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function class()
    {
        return $this->belongsTo(ClassModel::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function template()
    {
        return $this->belongsTo(ReportTemplate::class);
    }

    public function signatures()
    {
        return $this->hasMany(ReportSignature::class);
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeForStudent($query, string $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeForSemester($query, int $semester, string $academicYear)
    {
        return $query->where('semester', $semester)
                     ->where('academic_year', $academicYear);
    }
}

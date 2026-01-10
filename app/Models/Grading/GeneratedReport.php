<?php

declare (strict_types = 1);

namespace App\Models\Grading;

use App\Models\Model;
use App\Models\SchoolManagement\Student;
use App\Models\User;

class GeneratedReport extends Model
{
    protected $primaryKey = 'id';
    protected $keyType    = 'string';
    public $incrementing  = false;

    protected $fillable = [
        'student_id',
        'report_type',
        'semester',
        'academic_year',
        'template_id',
        'file_path',
        'file_format',
        'file_size',
        'status',
        'generation_data',
        'is_published',
        'published_at',
        'created_by',
    ];

    protected $casts = [
        'generation_data' => 'array',
        'is_published'    => 'boolean',
        'published_at'     => 'datetime',
        'created_at'       => 'datetime',
        'updated_at'       => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function template()
    {
        return $this->belongsTo(ReportTemplate::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('report_type', $type);
    }

    public function scopeByStudent($query, string $studentId)
    {
        return $query->where('student_id', $studentId);
    }
}

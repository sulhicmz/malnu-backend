<?php

declare(strict_types=1);

namespace App\Models\LMS;

use App\Models\Model;
use App\Models\LMS\Course;
use App\Models\SchoolManagement\Student;

class Certificate extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'course_id',
        'student_id',
        'certificate_number',
        'issued_at',
        'notes',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}

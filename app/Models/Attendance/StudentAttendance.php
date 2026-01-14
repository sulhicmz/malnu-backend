<?php

declare(strict_types=1);

namespace App\Models\Attendance;

use App\Models\Model;
use App\Models\SchoolManagement\Student;
use App\Models\SchoolManagement\ClassModel;
use App\Models\User;
use App\Traits\UsesUuid;

class StudentAttendance extends Model
{
    protected string $primaryKey = 'id';
    protected string $keyType = 'string';
    public bool $incrementing = false;

    use UsesUuid;

    protected $table = 'student_attendances';

    protected $fillable = [
        'student_id',
        'class_id',
        'teacher_id',
        'attendance_date',
        'status',
        'notes',
        'check_in_time',
        'check_out_time',
        'marked_by',
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'check_in_time' => 'time',
        'check_out_time' => 'time',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function class ()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function markedBy()
    {
        return $this->belongsTo(User::class, 'marked_by');
    }

    public function scopeByStudent($query, string $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeByClass($query, string $classId)
    {
        return $query->where('class_id', $classId);
    }

    public function scopeByDate($query, string $date)
    {
        return $query->whereDate('attendance_date', $date);
    }

    public function scopeByDateRange($query, string $startDate, string $endDate)
    {
        return $query->whereBetween('attendance_date', [$startDate, $endDate]);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopePresent($query)
    {
        return $query->where('status', 'present');
    }

    public function scopeAbsent($query)
    {
        return $query->where('status', 'absent');
    }

    public function scopeLate($query)
    {
        return $query->where('status', 'late');
    }

    public function scopeExcused($query)
    {
        return $query->where('status', 'excused');
    }

    public function scopeWithRelationships($query)
    {
        return $query->with(['student', 'class', 'teacher', 'markedBy']);
    }
}

<?php

declare(strict_types=1);

namespace App\Models\Attendance;

use App\Models\Model;
use App\Models\SchoolManagement\Student;
use App\Models\SchoolManagement\Schedule;
use App\Models\User;
use App\Traits\UsesUuid;

class Attendance extends Model
{
    use UsesUuid;

    protected $table = 'attendance';

    protected string $primaryKey = 'id';
    protected string $keyType = 'string';
    public bool $incrementing = false;

    protected array $fillable = [
        'student_id',
        'schedule_id',
        'date',
        'status',
        'notes',
        'created_by'
    ];

    protected array $casts = [
        'date' => 'date',
    ];

    // Relationships
    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function schedule()
    {
        return $this->belongsTo(Schedule::class, 'schedule_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
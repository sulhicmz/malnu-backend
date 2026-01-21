<?php

declare(strict_types=1);

namespace App\Models\Behavior;

use App\Models\Model;
use App\Models\SchoolManagement\Student;
use App\Models\User;
use App\Traits\UsesUuid;

class BehaviorNote extends Model
{
    use UsesUuid;

    protected string $table = 'behavior_notes';

    protected array $fillable = [
        'student_id',
        'recorded_by',
        'note_date',
        'note',
        'type',
        'is_private',
        'created_by',
        'updated_by',
    ];

    protected array $casts = [
        'note_date' => 'date',
        'is_private' => 'boolean',
    ];

    public function scopePublic($query)
    {
        return $query->where('is_private', false);
    }

    public function scopePrivate($query)
    {
        return $query->where('is_private', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByDateRange($query, string $startDate, string $endDate)
    {
        return $query->whereBetween('note_date', [$startDate, $endDate]);
    }

    public function scopeForStudent($query, string $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}

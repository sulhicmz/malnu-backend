<?php

namespace App\Models\Attendance;

use App\Models\Model;
use App\Models\SchoolManagement\Teacher;
use App\Traits\UsesUuid;
use App\Models\Attendance\SubstituteAssignment;

/**
 * @property string $id
 * @property string $teacher_id
 * @property bool $is_active
 * @property array|null $available_subjects
 * @property array|null $available_classes
 * @property string|null $special_notes
 * @property float|null $hourly_rate
 */
class SubstituteTeacher extends Model
{
    protected string $primaryKey = 'id';
    protected string $keyType = 'string';
    public bool $incrementing = false;

    use UsesUuid;

    protected $table = 'substitute_teachers';

    protected $fillable = [
        'teacher_id',
        'is_active',
        'available_subjects',
        'available_classes',
        'special_notes',
        'hourly_rate',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'available_subjects' => 'array',
        'available_classes' => 'array',
        'hourly_rate' => 'decimal:2',
    ];

    /**
     * Get the teacher record associated with this substitute teacher.
     */
    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }

    /**
     * Get the substitute assignments for this substitute teacher.
     */
    public function substituteAssignments()
    {
        return $this->hasMany(SubstituteAssignment::class, 'substitute_teacher_id');
    }
}
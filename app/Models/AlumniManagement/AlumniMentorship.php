<?php

declare(strict_types=1);

namespace App\Models\AlumniManagement;

use App\Models\Model;
use App\Models\SchoolManagement\Student;

class AlumniMentorship extends Model
{
    protected $primaryKey = 'id';
    protected $keyType    = 'string';
    public $incrementing  = false;

    protected $fillable = [
        'alumni_id',
        'student_id',
        'status',
        'focus_area',
        'start_date',
        'end_date',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
        'deleted_at'  => 'datetime',
    ];

    public function alumni()
    {
        return $this->belongsTo(AlumniProfile::class, 'alumni_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['active', 'completed']);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}

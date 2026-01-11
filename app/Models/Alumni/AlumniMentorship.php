<?php

declare(strict_types=1);

namespace App\Models\Alumni;

use App\Models\Model;

class AlumniMentorship extends Model
{
    protected $table = 'alumni_mentorships';

    protected $fillable = [
        'mentor_id',
        'student_id',
        'mentee_name',
        'mentee_email',
        'status',
        'focus_area',
        'goals',
        'start_date',
        'end_date',
        'sessions_count',
        'notes',
        'feedback',
        'match_criteria',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'sessions_count' => 'integer',
        'notes' => 'array',
        'feedback' => 'array',
        'match_criteria' => 'array',
        'created_by' => 'string',
        'updated_by' => 'string',
    ];

    public function mentor()
    {
        return $this->belongsTo(Alumni::class, 'mentor_id');
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeByMentor($query, $mentorId)
    {
        return $query->where('mentor_id', $mentorId);
    }

    public function scopeByStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function activate()
    {
        $this->update([
            'status' => 'active',
            'start_date' => now(),
        ]);
    }

    public function complete()
    {
        $this->update([
            'status' => 'completed',
            'end_date' => now(),
        ]);
    }

    public function incrementSessions()
    {
        $this->increment('sessions_count');
    }

    public function isActive()
    {
        return $this->status === 'active';
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }
}

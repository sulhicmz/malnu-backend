<?php

declare(strict_types=1);

namespace App\Models;

class Immunization extends Model
{
    protected $fillable = [
        'student_id',
        'health_record_id',
        'vaccine_name',
        'administration_date',
        'due_date',
        'batch_number',
        'administered_by',
        'administering_facility',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'administration_date' => 'date',
        'due_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'id');
    }

    public function healthRecord()
    {
        return $this->belongsTo(HealthRecord::class, 'health_record_id', 'id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }

    public function scopeOverdue($query)
    {
        return $query->whereNotNull('due_date')
            ->where('due_date', '<', now()->format('Y-m-d'));
    }

    public function scopeDueSoon($query, int $days = 30)
    {
        return $query->whereNotNull('due_date')
            ->whereBetween('due_date', [
                now()->format('Y-m-d'),
                now()->addDays($days)->format('Y-m-d'),
            ]);
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->due_date !== null && $this->due_date < now()->format('Y-m-d');
    }

    public function getIsDueSoonAttribute(): bool
    {
        return $this->due_date !== null
               && $this->due_date >= now()->format('Y-m-d')
               && $this->due_date <= now()->addDays(30)->format('Y-m-d');
    }
}

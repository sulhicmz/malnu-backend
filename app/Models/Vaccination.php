<?php

declare(strict_types=1);

namespace App\Models;

use Hyperf\Database\Model as BaseModel;
use Hyperf\Database\Model\Relations\BelongsTo;

class Vaccination extends BaseModel
{
    protected $table = 'immunizations';

    protected $fillable = [
        'student_id',
        'health_record_id',
        'vaccine_name',
        'vaccine_code',
        'manufacturer',
        'batch_number',
        'administration_date',
        'next_due_date',
        'completed_date',
        'dose',
        'route_of_administration',
        'site_of_administration',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'administration_date' => 'date',
        'next_due_date' => 'date',
        'completed_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id', 'id');
    }

    public function healthRecord(): BelongsTo
    {
        return $this->belongsTo(HealthRecord::class, 'health_record_id', 'id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }

    public function scopeForStudent($query, string $studentId)
    {
        return $query->where('student_id', $studentId);
    }
}

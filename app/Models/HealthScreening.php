<?php

declare(strict_types=1);

namespace App\Models;

class HealthScreening extends Model
{
    const STATUS_NORMAL = 'normal';
    const STATUS_ABNORMAL = 'abnormal';
    const STATUS_NEEDS_FOLLOW_UP = 'needs_follow_up';

    protected $fillable = [
        'student_id',
        'health_record_id',
        'screening_type',
        'screening_date',
        'results',
        'status',
        'follow_up_date',
        'notes',
        'performed_by',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'screening_date' => 'date',
        'follow_up_date' => 'date',
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

    public function performedBy()
    {
        return $this->belongsTo(User::class, 'performed_by', 'id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('screening_type', $type);
    }

    public function scopeVision($query)
    {
        return $this->where('screening_type', 'vision');
    }

    public function scopeHearing($query)
    {
        return $this->where('screening_type', 'hearing');
    }

    public function scopeScoliosis($query)
    {
        return $this->where('screening_type', 'scoliosis');
    }

    public function scopeAbnormal($query)
    {
        return $query->whereIn('status', [self::STATUS_ABNORMAL, self::STATUS_NEEDS_FOLLOW_UP]);
    }

    public function scopeNeedsFollowUp($query)
    {
        return $query->where('status', self::STATUS_NEEDS_FOLLOW_UP)
                      ->where('follow_up_date', '<=', now()->format('Y-m-d'));
    }

    public function getIsOverdueForFollowUpAttribute(): bool
    {
        return $this->status === self::STATUS_NEEDS_FOLLOW_UP &&
               $this->follow_up_date !== null &&
               $this->follow_up_date < now()->format('Y-m-d');
    }
}

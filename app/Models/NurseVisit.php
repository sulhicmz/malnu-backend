<?php

declare(strict_types=1);

namespace App\Models;

class NurseVisit extends Model
{
    protected $fillable = [
        'student_id',
        'health_record_id',
        'visit_reason',
        'symptoms',
        'visit_time',
        'examination',
        'treatment',
        'disposition',
        'return_time',
        'referral_details',
        'attended_by',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'visit_time' => 'datetime',
        'return_time' => 'datetime',
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

    public function attendedBy()
    {
        return $this->belongsTo(User::class, 'attended_by', 'id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }

    public function scopeByDate($query, string $date)
    {
        return $query->whereDate('visit_time', $date);
    }

    public function scopeByDateRange($query, string $startDate, string $endDate)
    {
        return $query->whereBetween('visit_time', [$startDate, $endDate]);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('visit_time', now()->format('Y-m-d'));
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('visit_time', [
            now()->startOfWeek()->format('Y-m-d'),
            now()->endOfWeek()->format('Y-m-d'),
        ]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereYear('visit_time', now()->year)
            ->whereMonth('visit_time', now()->month);
    }

    public function getDurationAttribute(): ?string
    {
        if (! $this->return_time || ! $this->visit_time) {
            return null;
        }

        $visitTime = strtotime($this->visit_time);
        $returnTime = strtotime($this->return_time);

        if ($returnTime > $visitTime) {
            $minutes = round(($returnTime - $visitTime) / 60);
            return $minutes . ' minute' . ($minutes !== 1 ? 's' : '');
        }

        return null;
    }
}

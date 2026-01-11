<?php

namespace App\Models;

use Hyperf\DbConnection\Model\Model;

class AccreditationStandard extends Model
{
    protected $table = 'accreditation_standards';

    protected $fillable = [
        'name',
        'accreditation_body',
        'standard_code',
        'description',
        'status',
        'assessment_date',
        'expiry_date',
        'coordinator_id',
        'evidence_notes',
        'report_path',
    ];

    protected $casts = [
        'assessment_date' => 'date',
        'expiry_date' => 'date',
    ];

    public function coordinator()
    {
        return $this->belongsTo(Staff::class, 'coordinator_id');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeExpiringSoon($query)
    {
        return $query->where('status', '!=', 'completed')
            ->where('expiry_date', '>', now())
            ->where('expiry_date', '<=', now()->addDays(90));
    }

    public function scopeExpired($query)
    {
        return $query->where('expiry_date', '<', now());
    }
}

<?php

declare(strict_types=1);

namespace App\Models\Compliance;

use App\Models\Model;
use App\Models\User;

/**
 * ComplianceReport Model.
 *
 * Stores generated regulatory compliance reports.
 */
class ComplianceReport extends Model
{
    public $primaryKey = 'id';

    public $incrementing = false;

    protected $table = 'compliance_reports';

    protected $fillable = [
        'report_type',
        'title',
        'description',
        'report_period_start',
        'report_period_end',
        'generated_by',
        'report_data',
        'status',
        'submitted_at',
        'submitted_to',
        'external_reference',
    ];

    protected $casts = [
        'report_data' => 'array',
        'report_period_start' => 'date',
        'report_period_end' => 'date',
        'submitted_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $keyType = 'string';

    public function generatedBy()
    {
        return $this->belongsTo(User::class, 'generated_by', 'id');
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('report_type', $type);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByPeriod($query, string $startDate, string $endDate)
    {
        return $query->where('report_period_start', '>=', $startDate)
            ->where('report_period_end', '<=', $endDate);
    }

    public function scopeSubmitted($query)
    {
        return $query->where('status', 'submitted');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }
}

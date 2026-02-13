<?php

declare(strict_types=1);

namespace App\Models\Grading;

use App\Models\Model;
use App\Models\User;

class ReportSignature extends Model
{
    public $incrementing = false;

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    protected $fillable = [
        'report_id',
        'signer_name',
        'signer_title',
        'signature_image_url',
        'signed_at',
        'notes',
        'signed_by',
    ];

    protected $casts = [
        'signed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function report()
    {
        return $this->belongsTo(Report::class);
    }

    public function signer()
    {
        return $this->belongsTo(User::class, 'signed_by');
    }

    public function scopeForReport($query, string $reportId)
    {
        return $query->where('report_id', $reportId);
    }

    public function isSigned(): bool
    {
        return $this->signed_at !== null;
    }
}

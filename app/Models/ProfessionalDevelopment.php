<?php

namespace App\Models;

use Hyperf\DbConnection\Model\Model;

class ProfessionalDevelopment extends Model
{
    protected $table = 'professional_development';

    protected $fillable = [
        'staff_id',
        'title',
        'training_type',
        'provider',
        'start_date',
        'end_date',
        'duration_hours',
        'location',
        'description',
        'status',
        'certificate_path',
        'cost',
        'internal',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'cost' => 'decimal:2',
        'internal' => 'boolean',
    ];

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('status', 'planned')
            ->where('start_date', '>', now());
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeInternal($query)
    {
        return $query->where('internal', true);
    }

    public function scopeExternal($query)
    {
        return $query->where('internal', false);
    }
}

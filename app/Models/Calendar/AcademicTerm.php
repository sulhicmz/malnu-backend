<?php

declare(strict_types=1);

namespace App\Models\Calendar;

use App\Models\User;
use App\Models\Model;

class AcademicTerm extends Model
{
    protected $table = 'academic_terms';

    protected $fillable = [
        'name',
        'academic_year',
        'term_number',
        'start_date',
        'end_date',
        'is_current',
        'is_enrollment_open',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_current' => 'boolean',
        'is_enrollment_open' => 'boolean',
        'created_by' => 'string',
        'updated_by' => 'string',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function holidays()
    {
        return $this->hasMany(Holiday::class, 'academic_term_id');
    }

    public function scopeCurrent($query)
    {
        return $query->where('is_current', true);
    }

    public function scopeActive($query)
    {
        $today = \Carbon\Carbon::now()->toDateString();
        return $query->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today);
    }
}

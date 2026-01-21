<?php

declare(strict_types=1);

namespace App\Models\Calendar;

use App\Models\User;
use App\Models\Model;

class Holiday extends Model
{
    protected $table = 'holidays';

    protected $fillable = [
        'academic_term_id',
        'name',
        'start_date',
        'end_date',
        'type',
        'is_school_wide',
        'description',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_school_wide' => 'boolean',
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

    public function academicTerm()
    {
        return $this->belongsTo(AcademicTerm::class, 'academic_term_id');
    }

    public function scopeCurrentYear($query, $year = null)
    {
        if ($year === null) {
            $year = date('Y');
        }
        return $query->whereYear('start_date', $year);
    }

    public function scopeSchoolWide($query)
    {
        return $query->where('is_school_wide', true);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('start_date', '>=', \Carbon\Carbon::today()->toDateString());
    }
}

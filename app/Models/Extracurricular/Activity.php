<?php

declare(strict_types=1);

namespace App\Models\Extracurricular;

use App\Models\Model;
use App\Models\SchoolManagement\Student;
use App\Models\Extracurricular\Club;

class Activity extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'club_id',
        'name',
        'description',
        'start_date',
        'end_date',
        'location',
        'max_attendees',
        'status',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function club()
    {
        return $this->belongsTo(Club::class);
    }

    public function attendances()
    {
        return $this->hasMany(ActivityAttendance::class);
    }

    public function scopeByStatus($query, ?string $status)
    {
        if ($status) {
            return $query->where('status', $status);
        }
        return $query;
    }

    public function scopeByClub($query, string $clubId)
    {
        return $query->where('club_id', $clubId);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('start_date', '>=', date('Y-m-d H:i:s'))
            ->orderBy('start_date', 'asc');
    }

    public function scopePast($query)
    {
        return $query->where('start_date', '<', date('Y-m-d H:i:s'))
            ->orderBy('start_date', 'desc');
    }
}

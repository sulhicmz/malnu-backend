<?php

declare(strict_types=1);

namespace App\Models\Extracurricular;

use App\Models\Model;
use App\Models\SchoolManagement\Teacher;
use App\Models\Extracurricular\Club;

class ClubAdvisor extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'club_id',
        'teacher_id',
        'assigned_date',
        'notes',
    ];

    protected $casts = [
        'assigned_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function club()
    {
        return $this->belongsTo(Club::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function scopeByClub($query, string $clubId)
    {
        return $query->where('club_id', $clubId);
    }

    public function scopeByTeacher($query, string $teacherId)
    {
        return $query->where('teacher_id', $teacherId);
    }
}

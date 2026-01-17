<?php

declare(strict_types=1);

namespace App\Models\Extracurricular;

use App\Models\Model;
use App\Models\SchoolManagement\Student;
use App\Models\Extracurricular\Club;

class ClubMembership extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'club_id',
        'student_id',
        'role',
        'joined_date',
    ];

    protected $casts = [
        'joined_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function club()
    {
        return $this->belongsTo(Club::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function scopeByRole($query, ?string $role)
    {
        if ($role) {
            return $query->where('role', $role);
        }
        return $query;
    }

    public function scopeByClub($query, string $clubId)
    {
        return $query->where('club_id', $clubId);
    }
}

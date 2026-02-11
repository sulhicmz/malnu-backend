<?php

declare (strict_types = 1);

namespace App\Models\ClubManagement;

use App\Models\ClubManagement\Club;
use App\Models\SchoolManagement\Student;

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
}

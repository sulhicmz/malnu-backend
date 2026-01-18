<?php

declare (strict_types = 1);

namespace App\Models\ClubManagement;

use App\Models\ClubManagement\Club;
use App\Models\SchoolManagement\Teacher;

class ClubAdvisor extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'club_id',
        'teacher_id',
        'assigned_date',
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
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }
}

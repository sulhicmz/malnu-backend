<?php

declare (strict_types = 1);

namespace App\Models\ClubManagement;

use App\Models\ClubManagement\Club;

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
}

<?php

declare(strict_types=1);

namespace App\Models\AlumniManagement;

use App\Models\Model;

class AlumniEventRegistration extends Model
{
    protected $primaryKey = 'id';
    protected $keyType    = 'string';
    public $incrementing  = false;

    protected $fillable = [
        'event_id',
        'alumni_id',
        'attendance_status',
        'registration_time',
        'notes',
    ];

    protected $casts = [
        'registration_time' => 'datetime',
        'created_at'       => 'datetime',
        'updated_at'       => 'datetime',
        'deleted_at'       => 'datetime',
    ];

    public function event()
    {
        return $this->belongsTo(AlumniEvent::class, 'event_id');
    }

    public function alumni()
    {
        return $this->belongsTo(AlumniProfile::class, 'alumni_id');
    }

    public function scopeAttended($query)
    {
        return $query->where('attendance_status', 'attended');
    }

    public function scopeRegistered($query)
    {
        return $query->where('attendance_status', 'registered');
    }
}

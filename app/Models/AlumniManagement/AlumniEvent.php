<?php

declare(strict_types=1);

namespace App\Models\AlumniManagement;

use App\Models\Model;
use App\Models\User;

class AlumniEvent extends Model
{
    protected $primaryKey = 'id';
    protected $keyType    = 'string';
    public $incrementing  = false;

    protected $fillable = [
        'created_by',
        'title',
        'description',
        'event_type',
        'location',
        'event_date',
        'max_attendees',
        'current_attendees',
        'status',
    ];

    protected $casts = [
        'event_date'         => 'datetime',
        'max_attendees'      => 'integer',
        'current_attendees'  => 'integer',
        'created_at'          => 'datetime',
        'updated_at'          => 'datetime',
        'deleted_at'          => 'datetime',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function registrations()
    {
        return $this->hasMany(AlumniEventRegistration::class, 'event_id');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('status', 'upcoming')->where('event_date', '>=', date('Y-m-d H:i:s'));
    }

    public function scopePast($query)
    {
        return $query->where('event_date', '<', date('Y-m-d H:i:s'));
    }

    public function scopeByType($query, $type)
    {
        return $query->where('event_type', $type);
    }
}

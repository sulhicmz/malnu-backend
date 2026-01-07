<?php

declare(strict_types=1);

namespace App\Models\Transportation;

use App\Models\User;
use App\Models\Model;

class TransportDriver extends Model
{
    protected $table = 'transport_drivers';

    protected $fillable = [
        'user_id',
        'name',
        'phone',
        'license_number',
        'license_expiry',
        'license_type',
        'status',
        'address',
        'emergency_contact',
        'hire_date',
        'notes',
        'certifications',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'license_expiry' => 'date',
        'hire_date' => 'date',
        'certifications' => 'array',
        'created_by' => 'string',
        'updated_by' => 'string',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function schedules()
    {
        return $this->hasMany(TransportSchedule::class, 'driver_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}

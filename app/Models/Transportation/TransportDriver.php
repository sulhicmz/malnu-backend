<?php

declare(strict_types=1);

namespace App\Models\Transportation;

use App\Models\User;
use App\Models\Model;

class TransportDriver extends Model
{
    protected $table = 'transport_drivers';

    protected $fillable = [
        'employee_id',
        'name',
        'phone',
        'email',
        'license_number',
        'license_type',
        'license_expiry',
        'address',
        'hire_date',
        'status',
        'certifications',
        'emergency_contacts',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'hire_date' => 'date',
        'license_expiry' => 'date',
        'certifications' => 'array',
        'emergency_contacts' => 'array',
        'created_by' => 'string',
        'updated_by' => 'string',
    ];

    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_ON_LEAVE = 'on_leave';

    public const LICENSE_COMMERCIAL = 'commercial';
    public const LICENSE_BUS = 'bus';
    public const LICENSE_HEAVY = 'heavy_vehicle';

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function schedules()
    {
        return $this->hasMany(TransportSchedule::class, 'driver_id');
    }

    public function assignments()
    {
        return $this->hasMany(TransportAssignment::class, 'driver_id');
    }

    public function incidents()
    {
        return $this->hasMany(TransportIncident::class, 'driver_id');
    }

    public function tracking()
    {
        return $this->hasMany(TransportTracking::class, 'driver_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeExpiringLicenses($query, $days = 60)
    {
        return $query->whereDate('license_expiry', '<=', now()->addDays($days));
    }

    public function isLicenseExpiring($days = 60)
    {
        return $this->license_expiry && $this->license_expiry->lte(now()->addDays($days));
    }

    public function isLicenseExpired()
    {
        return $this->license_expiry && $this->license_expiry->isPast();
    }
}

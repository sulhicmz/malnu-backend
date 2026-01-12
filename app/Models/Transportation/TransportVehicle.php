<?php

declare(strict_types=1);

namespace App\Models\Transportation;

use App\Models\User;
use App\Models\Model;

class TransportVehicle extends Model
{
    protected $table = 'transport_vehicles';

    protected $fillable = [
        'plate_number',
        'vehicle_type',
        'make',
        'model',
        'year',
        'capacity',
        'color',
        'vin',
        'registration_number',
        'registration_expiry',
        'insurance_expiry',
        'inspection_expiry',
        'fuel_type',
        'status',
        'current_location',
        'latitude',
        'longitude',
        'last_odometer',
        'maintenance_history',
        'gps_device_info',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'year' => 'integer',
        'capacity' => 'integer',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'last_odometer' => 'decimal:2',
        'maintenance_history' => 'array',
        'gps_device_info' => 'array',
        'created_by' => 'string',
        'updated_by' => 'string',
    ];

    public const STATUS_ACTIVE = 'active';
    public const STATUS_MAINTENANCE = 'maintenance';
    public const STATUS_RETIRED = 'retired';

    public const TYPE_BUS = 'bus';
    public const TYPE_VAN = 'van';
    public const TYPE_MINIBUS = 'minibus';

    public const FUEL_DIESEL = 'diesel';
    public const FUEL_PETROL = 'petrol';
    public const FUEL_ELECTRIC = 'electric';
    public const FUEL_HYBRID = 'hybrid';

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
        return $this->hasMany(TransportSchedule::class, 'vehicle_id');
    }

    public function assignments()
    {
        return $this->hasMany(TransportAssignment::class, 'vehicle_id');
    }

    public function incidents()
    {
        return $this->hasMany(TransportIncident::class, 'vehicle_id');
    }

    public function tracking()
    {
        return $this->hasMany(TransportTracking::class, 'vehicle_id')->latest('recorded_at');
    }

    public function latestTracking()
    {
        return $this->hasOne(TransportTracking::class, 'vehicle_id')->latest('recorded_at');
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('vehicle_type', $type);
    }

    public function scopeExpiringDocuments($query, $days = 30)
    {
        return $query->where(function ($q) use ($days) {
            $q->whereDate('registration_expiry', '<=', now()->addDays($days))
                ->orWhereDate('insurance_expiry', '<=', now()->addDays($days))
                ->orWhereDate('inspection_expiry', '<=', now()->addDays($days));
        });
    }

    public function needsRegistration()
    {
        return $this->registration_expiry && $this->registration_expiry->lte(now()->addDays(30));
    }

    public function needsInsurance()
    {
        return $this->insurance_expiry && $this->insurance_expiry->lte(now()->addDays(30));
    }

    public function needsInspection()
    {
        return $this->inspection_expiry && $this->inspection_expiry->lte(now()->addDays(30));
    }

    public function hasAnyExpiringDocuments()
    {
        return $this->needsRegistration() || $this->needsInsurance() || $this->needsInspection();
    }
}

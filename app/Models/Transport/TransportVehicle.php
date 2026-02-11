<?php

declare (strict_types = 1);

namespace App\Models\Transport;

use App\Models\User;
use App\Traits\UsesUuid;
use Hyperf\DbConnection\Model\Model;

class TransportVehicle extends Model
{
    use UsesUuid;

    protected string $table = 'transport_vehicles';
    protected string $primaryKey = 'id';
    protected string $keyType = 'string';
    public bool $incrementing = false;

    protected array $fillable = [
        'plate_number',
        'vehicle_type',
        'capacity',
        'make',
        'model',
        'manufacture_year',
        'registration_number',
        'registration_expiry',
        'insurance_number',
        'insurance_expiry',
        'inspection_number',
        'inspection_expiry',
        'is_active',
        'status',
    ];

    protected array $casts = [
        'is_active' => 'boolean',
        'registration_expiry' => 'date',
        'insurance_expiry' => 'date',
        'inspection_expiry' => 'date',
    ];

    public function schedules()
    {
        return $this->hasMany(TransportSchedule::class, 'vehicle_id');
    }

    public function trackingRecords()
    {
        return $this->hasMany(TransportTracking::class, 'vehicle_id');
    }

    public function incidents()
    {
        return $this->hasMany(TransportIncident::class, 'vehicle_id');
    }

    public function isActive(): bool
    {
        return $this->is_active && $this->status === 'available';
    }

    public function hasExpiringDocuments(): bool
    {
        $today = date('Y-m-d');
        $warningDate = date('Y-m-d', strtotime('+30 days'));

        return ($this->registration_expiry && $this->registration_expiry <= $warningDate)
            || ($this->insurance_expiry && $this->insurance_expiry <= $warningDate)
            || ($this->inspection_expiry && $this->inspection_expiry <= $warningDate);
    }
}
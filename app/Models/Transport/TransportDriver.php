<?php

declare (strict_types = 1);

namespace App\Models\Transport;

use App\Models\User;
use App\Traits\UsesUuid;
use Hyperf\DbConnection\Model\Model;

class TransportDriver extends Model
{
    use UsesUuid;

    protected string $table = 'transport_drivers';
    protected string $primaryKey = 'id';
    protected string $keyType = 'string';
    public bool $incrementing = false;

    protected array $fillable = [
        'user_id',
        'name',
        'license_number',
        'license_expiry',
        'phone',
        'address',
        'certification_type',
        'certification_expiry',
        'is_active',
        'status',
    ];

    protected array $casts = [
        'is_active' => 'boolean',
        'license_expiry' => 'date',
        'certification_expiry' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function schedules()
    {
        return $this->hasMany(TransportSchedule::class, 'driver_id');
    }

    public function incidents()
    {
        return $this->hasMany(TransportIncident::class, 'driver_id');
    }

    public function isActive(): bool
    {
        return $this->is_active && $this->status === 'available';
    }

    public function hasExpiringDocuments(): bool
    {
        $today = date('Y-m-d');
        $warningDate = date('Y-m-d', strtotime('+30 days'));

        return ($this->license_expiry && $this->license_expiry <= $warningDate)
            || ($this->certification_expiry && $this->certification_expiry <= $warningDate);
    }
}
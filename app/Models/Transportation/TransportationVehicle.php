<?php

declare(strict_types=1);

namespace App\Models\Transportation;

use App\Models\Model;

class TransportationVehicle extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'vehicle_number',
        'vehicle_type',
        'license_plate',
        'capacity',
        'make',
        'model',
        'year',
        'status',
        'insurance_number',
        'insurance_expiry',
        'notes',
    ];

    protected $casts = [
        'year' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function assignments()
    {
        return $this->hasMany(TransportationAssignment::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'active');
    }
}

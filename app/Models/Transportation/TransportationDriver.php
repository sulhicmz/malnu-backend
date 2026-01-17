<?php

declare(strict_types=1);

namespace App\Models\Transportation;

use App\Models\Model;
use App\Models\Transportation\TransportationRegistration;

class TransportationDriver extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'driver_name',
        'phone_number',
        'email',
        'license_number',
        'license_expiry',
        'user_id',
        'status',
        'certifications',
    ];

    protected $casts = [
        'license_expiry' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function assignments()
    {
        return $this->hasMany(TransportationAssignment::class);
    }

    public function incidents()
    {
        return $this->hasMany(TransportationIncident::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}

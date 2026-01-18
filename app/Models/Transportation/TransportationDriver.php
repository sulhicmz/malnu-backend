<?php

declare(strict_types=1);

namespace App\Models\Transportation;

use App\Models\User;
use App\Models\Model;

class TransportationDriver extends Model
{
    protected $table = 'transportation_drivers';

    protected $fillable = [
        'user_id',
        'driver_license_number',
        'license_expiry_date',
        'status',
        'background_check_date',
        'emergency_contact_phone',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'created_by' => 'string',
        'updated_by' => 'string',
        'user_id' => 'string',
        'license_expiry_date' => 'date',
        'background_check_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function routes()
    {
        return $this->hasMany(TransportationRoute::class, 'driver_id');
    }

    public function incidents()
    {
        return $this->hasMany(TransportationIncident::class, 'driver_id');
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

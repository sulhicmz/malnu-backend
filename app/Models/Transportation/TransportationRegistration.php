<?php

declare(strict_types=1);

namespace App\Models\Transportation;

use App\Models\Model;
use App\Models\Transportation\TransportationRoute;

class TransportationRegistration extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'student_id',
        'route_id',
        'bus_stop_id',
        'registration_date',
        'expiry_date',
        'status',
        'fee_amount',
        'fee_paid',
        'payment_status',
        'special_requirements',
        'parent_notes',
    ];

    protected $casts = [
        'registration_date' => 'date',
        'expiry_date' => 'date',
        'fee_amount' => 'decimal:2',
        'fee_paid' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function route()
    {
        return $this->belongsTo(TransportationRoute::class);
    }

    public function assignments()
    {
        return $this->hasMany(TransportationAssignment::class);
    }

    public function fees()
    {
        return $this->hasMany(TransportationFee::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeExpiring($query, $days = 30)
    {
        return $query->where('status', 'active')
            ->where('expiry_date', '<=', now()->addDays($days));
    }
}

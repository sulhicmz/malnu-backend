<?php

declare(strict_types=1);

namespace App\Models\Transportation;

use App\Models\User;
use App\Models\Model;

class TransportStop extends Model
{
    protected $table = 'transport_stops';

    protected $fillable = [
        'route_id',
        'stop_name',
        'description',
        'address',
        'latitude',
        'longitude',
        'stop_order',
        'arrival_time',
        'departure_time',
        'pickup_point',
        'is_morning',
        'is_afternoon',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'stop_order' => 'integer',
        'arrival_time' => 'datetime:H:i:s',
        'departure_time' => 'datetime:H:i:s',
        'pickup_point' => 'array',
        'is_morning' => 'boolean',
        'is_afternoon' => 'boolean',
        'created_by' => 'string',
        'updated_by' => 'string',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function route()
    {
        return $this->belongsTo(TransportRoute::class, 'route_id');
    }

    public function assignments()
    {
        return $this->hasMany(TransportAssignment::class, 'stop_id');
    }

    public function scopeMorning($query)
    {
        return $query->where('is_morning', true);
    }

    public function scopeAfternoon($query)
    {
        return $query->where('is_afternoon', true);
    }

    public function scopeByOrder($query, $order)
    {
        return $query->where('stop_order', $order);
    }
}

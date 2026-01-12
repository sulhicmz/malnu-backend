<?php

declare(strict_types=1);

namespace App\Models\Transportation;

use App\Models\User;
use App\Models\Model;

class TransportRoute extends Model
{
    protected $table = 'transport_routes';

    protected $fillable = [
        'route_name',
        'route_number',
        'description',
        'route_type',
        'status',
        'start_time',
        'end_time',
        'total_stops',
        'total_distance',
        'estimated_duration',
        'route_coordinates',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'start_time' => 'datetime:H:i:s',
        'end_time' => 'datetime:H:i:s',
        'total_stops' => 'integer',
        'total_distance' => 'decimal:2',
        'estimated_duration' => 'integer',
        'route_coordinates' => 'array',
        'created_by' => 'string',
        'updated_by' => 'string',
    ];

    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

    public const TYPE_REGULAR = 'regular';
    public const TYPE_EXPRESS = 'express';
    public const TYPE_SPECIAL = 'special';

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function stops()
    {
        return $this->hasMany(TransportStop::class, 'route_id')->orderBy('stop_order');
    }

    public function schedules()
    {
        return $this->hasMany(TransportSchedule::class, 'route_id');
    }

    public function assignments()
    {
        return $this->hasMany(TransportAssignment::class, 'route_id');
    }

    public function incidents()
    {
        return $this->hasMany(TransportIncident::class, 'route_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('route_type', $type);
    }
}

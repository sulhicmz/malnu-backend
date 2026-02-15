<?php

declare (strict_types = 1);

namespace App\Models\Transport;

use App\Models\User;
use App\Traits\UsesUuid;
use Hypervel\Database\Model\Model;

class TransportAssignment extends Model
{
    use UsesUuid;

    protected string $table = 'transport_assignments';
    protected string $primaryKey = 'id';
    protected string $keyType = 'string';
    public bool $incrementing = false;

    protected array $fillable = [
        'student_id',
        'route_id',
        'pickup_stop_id',
        'dropoff_stop_id',
        'session_type',
        'start_date',
        'end_date',
        'fee',
        'is_active',
    ];

    protected array $casts = [
        'fee' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function route()
    {
        return $this->belongsTo(TransportRoute::class, 'route_id');
    }

    public function pickupStop()
    {
        return $this->belongsTo(TransportStop::class, 'pickup_stop_id');
    }

    public function dropoffStop()
    {
        return $this->belongsTo(TransportStop::class, 'dropoff_stop_id');
    }

    public function attendance()
    {
        return $this->hasMany(TransportAttendance::class, 'assignment_id');
    }

    public function isActive(): bool
    {
        return $this->is_active
            && (!$this->end_date || $this->end_date >= date('Y-m-d'));
    }
}
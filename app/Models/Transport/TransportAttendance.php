<?php

declare (strict_types = 1);

namespace App\Models\Transport;

use App\Models\User;
use App\Traits\UsesUuid;
use Hypervel\Database\Model\Model;

class TransportAttendance extends Model
{
    use UsesUuid;

    protected string $table = 'transport_attendance';
    protected string $primaryKey = 'id';
    protected string $keyType = 'string';
    public bool $incrementing = false;

    protected array $fillable = [
        'assignment_id',
        'student_id',
        'route_id',
        'attendance_date',
        'boarding_time',
        'alighting_time',
        'status',
        'remarks',
    ];

    protected array $casts = [
        'attendance_date' => 'date',
        'boarding_time' => 'datetime:H:i',
        'alighting_time' => 'datetime:H:i',
    ];

    public function assignment()
    {
        return $this->belongsTo(TransportAssignment::class, 'assignment_id');
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function route()
    {
        return $this->belongsTo(TransportRoute::class, 'route_id');
    }

    public function isPresent(): bool
    {
        return $this->status === 'present';
    }

    public function hasBoarded(): bool
    {
        return $this->boarding_time !== null;
    }

    public function hasAlighted(): bool
    {
        return $this->alighting_time !== null;
    }
}
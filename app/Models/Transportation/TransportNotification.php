<?php

declare(strict_types=1);

namespace App\Models\Transportation;

use App\Models\User;
use App\Models\Model;

class TransportNotification extends Model
{
    protected $table = 'transport_notifications';

    protected $fillable = [
        'route_id',
        'vehicle_id',
        'student_id',
        'notification_type',
        'title',
        'message',
        'priority',
        'is_sent',
        'sent_at',
        'recipient_ids',
        'metadata',
        'created_by',
    ];

    protected $casts = [
        'is_sent' => 'boolean',
        'sent_at' => 'datetime',
        'recipient_ids' => 'array',
        'metadata' => 'array',
        'created_by' => 'string',
    ];

    public function route()
    {
        return $this->belongsTo(TransportRoute::class, 'route_id');
    }

    public function vehicle()
    {
        return $this->belongsTo(TransportVehicle::class, 'vehicle_id');
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

<?php

declare(strict_types=1);

namespace App\Models\Notification;

use App\Models\Model;
use App\Models\User;

class NotificationRecipient extends Model
{
    protected $table = 'notification_recipients';

    protected $fillable = [
        'notification_id',
        'user_id',
        'read',
        'read_at',
        'delivery_channels',
        'delivery_status',
    ];

    protected $casts = [
        'read' => 'boolean',
        'read_at' => 'datetime',
        'delivery_channels' => 'array',
        'delivery_status' => 'array',
        'notification_id' => 'uuid',
        'user_id' => 'uuid',
    ];

    public function notification()
    {
        return $this->belongsTo(Notification::class, 'notification_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function deliveryLogs()
    {
        return $this->hasMany(NotificationDeliveryLog::class, 'recipient_id');
    }
}

<?php

declare(strict_types=1);

namespace App\Models\Notification;

use App\Models\Model;
use App\Models\User;

class NotificationDeliveryLog extends Model
{
    protected $table = 'notification_delivery_logs';

    protected $fillable = [
        'notification_id',
        'recipient_id',
        'channel',
        'status',
        'response',
        'delivered_at',
    ];

    protected $casts = [
        'delivered_at' => 'datetime',
    ];

    public function notification()
    {
        return $this->belongsTo(Notification::class, 'notification_id');
    }

    public function recipient()
    {
        return $this->belongsTo(NotificationRecipient::class, 'recipient_id');
    }
}
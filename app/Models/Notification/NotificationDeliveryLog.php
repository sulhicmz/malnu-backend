<?php

declare(strict_types=1);

namespace App\Models\Notification;

use App\Models\Model;

class NotificationDeliveryLog extends Model
{
    protected $table = 'notification_delivery_logs';

    protected $fillable = [
        'notification_id',
        'recipient_id',
        'channel',
        'status',
        'error_message',
        'retry_count',
        'delivered_at',
        'failed_at',
        'metadata',
    ];

    protected $casts = [
        'delivered_at' => 'datetime',
        'failed_at' => 'datetime',
        'metadata' => 'array',
        'notification_id' => 'uuid',
        'recipient_id' => 'uuid',
        'retry_count' => 'integer',
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

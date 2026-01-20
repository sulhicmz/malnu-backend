<?php

declare(strict_types=1);

namespace App\Models\Notification;

use App\Models\Model;
use App\Traits\UsesUuid;

class NotificationDeliveryLog extends Model
{
    use UsesUuid;

    protected string $primaryKey = 'id';
    protected string $keyType = 'string';
    public bool $incrementing = false;

    protected array $fillable = [
        'notification_id',
        'recipient_id',
        'channel',
        'status',
        'error_message',
        'sent_at',
    ];

    protected array $casts = [
        'sent_at' => 'datetime',
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

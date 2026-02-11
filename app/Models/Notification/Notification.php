<?php

declare(strict_types=1);

namespace App\Models\Notification;

use App\Models\Model;
use App\Traits\UsesUuid;

class Notification extends Model
{
    use UsesUuid;

    public bool $incrementing = false;

    protected string $primaryKey = 'id';

    protected string $keyType = 'string';

    protected array $fillable = [
        'template_id',
        'title',
        'message',
        'type',
        'priority',
        'data',
        'scheduled_at',
    ];

    protected array $casts = [
        'data' => 'array',
        'scheduled_at' => 'datetime',
    ];

    public function template()
    {
        return $this->belongsTo(NotificationTemplate::class, 'template_id');
    }

    public function recipients()
    {
        return $this->hasMany(NotificationRecipient::class, 'notification_id');
    }

    public function deliveryLogs()
    {
        return $this->hasMany(NotificationDeliveryLog::class, 'notification_id');
    }
}

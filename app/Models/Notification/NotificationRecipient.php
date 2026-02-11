<?php

declare(strict_types=1);

namespace App\Models\Notification;

use App\Models\Model;
use App\Traits\UsesUuid;

class NotificationRecipient extends Model
{
    use UsesUuid;

    public bool $incrementing = false;

    protected string $primaryKey = 'id';

    protected string $keyType = 'string';

    protected array $fillable = [
        'notification_id',
        'user_id',
        'read',
        'read_at',
    ];

    protected array $casts = [
        'read' => 'boolean',
        'read_at' => 'datetime',
    ];

    public function notification()
    {
        return $this->belongsTo(Notification::class, 'notification_id');
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function deliveryLogs()
    {
        return $this->hasMany(NotificationDeliveryLog::class, 'recipient_id');
    }
}

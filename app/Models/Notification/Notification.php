<?php

declare(strict_types=1);

namespace App\Models\Notification;

use App\Models\Model;
use App\Models\User;
use Hyperf\Database\Model\SoftDeletes;

class Notification extends Model
{
    use SoftDeletes;

    protected $table = 'notifications';

    protected $fillable = [
        'template_id',
        'title',
        'message',
        'type',
        'priority',
        'channels',
        'metadata',
        'sent_by',
        'scheduled_at',
        'sent_at',
    ];

    protected $casts = [
        'channels' => 'array',
        'metadata' => 'array',
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        'template_id' => 'uuid',
        'sent_by' => 'uuid',
    ];

    public function template()
    {
        return $this->belongsTo(NotificationTemplate::class, 'template_id');
    }

    public function sentBy()
    {
        return $this->belongsTo(User::class, 'sent_by');
    }

    public function recipients()
    {
        return $this->hasMany(NotificationRecipient::class, 'notification_id');
    }

    public function deliveryLogs()
    {
        return $this->hasManyThrough(NotificationDeliveryLog::class, NotificationRecipient::class, 'notification_id', 'recipient_id');
    }
}

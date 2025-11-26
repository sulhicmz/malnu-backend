<?php

declare(strict_types=1);

namespace App\Models\Notification;

use App\Models\Model;
use App\Models\User;

class Notification extends Model
{
    protected $table = 'notifications';

    protected $fillable = [
        'template_id',
        'sender_id',
        'title',
        'content',
        'type',
        'priority',
        'data',
        'channels',
        'scheduled_at',
        'sent_at',
        'expires_at',
        'is_broadcast',
        'is_read',
    ];

    protected $casts = [
        'data' => 'array',
        'channels' => 'array',
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_broadcast' => 'boolean',
        'is_read' => 'boolean',
    ];

    public function template()
    {
        return $this->belongsTo(NotificationTemplate::class, 'template_id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function recipients()
    {
        return $this->hasMany(NotificationRecipient::class, 'notification_id');
    }
}
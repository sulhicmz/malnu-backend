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
        'delivery_status',
        'read_at',
    ];

    protected $casts = [
        'delivery_status' => 'array',
        'read_at' => 'datetime',
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
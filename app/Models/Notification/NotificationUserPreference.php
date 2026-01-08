<?php

declare(strict_types=1);

namespace App\Models\Notification;

use App\Models\Model;
use App\Models\User;

class NotificationUserPreference extends Model
{
    protected $table = 'notification_user_preferences';

    protected $fillable = [
        'user_id',
        'email_enabled',
        'sms_enabled',
        'push_enabled',
        'in_app_enabled',
        'type_preferences',
        'quiet_hours',
    ];

    protected $casts = [
        'email_enabled' => 'boolean',
        'sms_enabled' => 'boolean',
        'push_enabled' => 'boolean',
        'in_app_enabled' => 'boolean',
        'type_preferences' => 'array',
        'quiet_hours' => 'array',
        'user_id' => 'uuid',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

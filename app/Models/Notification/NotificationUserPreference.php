<?php

declare(strict_types=1);

namespace App\Models\Notification;

use App\Models\Model;
use App\Traits\UsesUuid;

class NotificationUserPreference extends Model
{
    use UsesUuid;

    protected string $primaryKey = 'id';
    protected string $keyType = 'string';
    public bool $incrementing = false;

    protected array $fillable = [
        'user_id',
        'type',
        'email_enabled',
        'sms_enabled',
        'push_enabled',
        'in_app_enabled',
        'quiet_hours_start',
        'quiet_hours_end',
    ];

    protected array $casts = [
        'email_enabled' => 'boolean',
        'sms_enabled' => 'boolean',
        'push_enabled' => 'boolean',
        'in_app_enabled' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
}

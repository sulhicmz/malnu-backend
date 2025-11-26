<?php

declare(strict_types=1);

namespace App\Models\Notification;

use App\Models\Model;
use App\Models\User;

class NotificationTemplate extends Model
{
    protected $table = 'notification_templates';

    protected $fillable = [
        'name',
        'slug',
        'subject',
        'body',
        'placeholders',
        'type',
        'is_active',
        'channels',
    ];

    protected $casts = [
        'placeholders' => 'array',
        'channels' => 'array',
        'is_active' => 'boolean',
    ];

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'template_id');
    }
}
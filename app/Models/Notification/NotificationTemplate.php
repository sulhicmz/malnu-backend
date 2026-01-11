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
        'type',
        'subject',
        'body',
        'variables',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'variables' => 'array',
        'is_active' => 'boolean',
        'created_by' => 'uuid',
        'updated_by' => 'uuid',
    ];

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'template_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}

<?php

declare(strict_types=1);

namespace App\Models\Notification;

use App\Models\Model;
use App\Traits\UsesUuid;

class NotificationTemplate extends Model
{
    use UsesUuid;

    protected string $primaryKey = 'id';
    protected string $keyType = 'string';
    public bool $incrementing = false;

    protected array $fillable = [
        'name',
        'type',
        'subject',
        'body',
        'variables',
        'is_active',
    ];

    protected array $casts = [
        'variables' => 'array',
        'is_active' => 'boolean',
    ];

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'template_id');
    }
}

<?php

declare (strict_types = 1);

namespace App\Models;

use Hyperf\Database\Model\Model;
use App\Traits\UsesUuid;

class SecurityEvent extends Model
{
    use UsesUuid;

    protected string $primaryKey = 'id';
    protected string $keyType = 'string';
    public bool $incrementing = false;

    protected array $fillable = [
        'user_id',
        'event_type',
        'description',
        'ip_address',
        'user_agent',
        'is_successful',
    ];

    protected array $casts = [
        'is_successful' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByType($query, $eventType)
    {
        return $query->where('event_type', $eventType);
    }

    public function scopeFailed($query)
    {
        return $query->where('is_successful', false);
    }
}

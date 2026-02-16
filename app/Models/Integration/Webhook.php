<?php

declare(strict_types=1);

namespace App\Models\Integration;

use App\Models\User;
use App\Traits\UsesUuid;
use Hypervel\Database\Eloquent\Factories\HasFactory;
use Hypervel\Database\Eloquent\Model;
use Hypervel\Database\Eloquent\SoftDeletes;

class Webhook extends Model
{
    use HasFactory;
    use SoftDeletes;
    use UsesUuid;

    public bool $incrementing = false;

    protected string $primaryKey = 'id';

    protected string $keyType = 'string';

    protected array $fillable = [
        'name',
        'description',
        'url',
        'secret',
        'events',
        'headers',
        'status',
        'retry_count',
        'timeout',
        'last_triggered_at',
        'last_success_at',
        'failure_count',
        'created_by',
    ];

    protected array $casts = [
        'events' => 'array',
        'headers' => 'array',
        'last_triggered_at' => 'datetime',
        'last_success_at' => 'datetime',
    ];

    public function deliveries()
    {
        return $this->hasMany(WebhookDelivery::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function markAsFailed(): void
    {
        $this->increment('failure_count');
        if ($this->failure_count >= 5) {
            $this->update(['status' => 'failed']);
        }
    }

    public function markAsSuccess(): void
    {
        $this->update([
            'last_success_at' => now(),
            'failure_count' => 0,
            'status' => 'active',
        ]);
    }

    public function shouldTrigger(string $event): bool
    {
        return $this->isActive() && in_array($event, $this->events ?? [], true);
    }
}

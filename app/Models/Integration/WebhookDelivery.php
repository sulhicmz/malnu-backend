<?php

declare(strict_types=1);

namespace App\Models\Integration;

use App\Traits\UsesUuid;
use Hypervel\Database\Eloquent\Factories\HasFactory;
use Hypervel\Database\Eloquent\Model;

class WebhookDelivery extends Model
{
    use HasFactory;
    use UsesUuid;

    public bool $incrementing = false;

    protected string $primaryKey = 'id';

    protected string $keyType = 'string';

    protected array $fillable = [
        'webhook_id',
        'event',
        'payload',
        'attempt',
        'status',
        'http_status_code',
        'response_body',
        'error_message',
        'sent_at',
        'delivered_at',
        'duration_ms',
    ];

    protected array $casts = [
        'payload' => 'array',
        'attempt' => 'integer',
        'http_status_code' => 'integer',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'duration_ms' => 'float',
    ];

    public function webhook()
    {
        return $this->belongsTo(Webhook::class);
    }

    public function isSuccessful(): bool
    {
        return $this->status === 'success';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function markAsSent(): void
    {
        $this->update(['sent_at' => now()]);
    }

    public function markAsDelivered(int $httpStatusCode, string $responseBody): void
    {
        $this->update([
            'status' => 'success',
            'http_status_code' => $httpStatusCode,
            'response_body' => $responseBody,
            'delivered_at' => now(),
        ]);
    }

    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
        ]);
    }

    public function incrementAttempt(): void
    {
        $this->increment('attempt');
    }

    public function setDuration(float $durationMs): void
    {
        $this->update(['duration_ms' => $durationMs]);
    }

    public static function getRecentByWebhook(string $webhookId, int $limit = 10)
    {
        return self::where('webhook_id', $webhookId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public static function getFailedDeliveries(int $hours = 24)
    {
        return self::where('status', 'failed')
            ->where('created_at', '>=', now()->subHours($hours))
            ->get();
    }
}

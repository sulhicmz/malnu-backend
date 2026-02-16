<?php

declare(strict_types=1);

namespace App\Models\Integration;

use App\Traits\UsesUuid;
use Hypervel\Database\Eloquent\Factories\HasFactory;
use Hypervel\Database\Eloquent\Model;

class IntegrationSyncLog extends Model
{
    use HasFactory;
    use UsesUuid;

    public bool $incrementing = false;

    protected string $primaryKey = 'id';

    protected string $keyType = 'string';

    protected array $fillable = [
        'integration_id',
        'operation',
        'status',
        'records_processed',
        'records_created',
        'records_updated',
        'records_failed',
        'error_message',
        'details',
        'started_at',
        'completed_at',
        'duration_ms',
    ];

    protected array $casts = [
        'records_processed' => 'integer',
        'records_created' => 'integer',
        'records_updated' => 'integer',
        'records_failed' => 'integer',
        'details' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'duration_ms' => 'float',
    ];

    public function integration()
    {
        return $this->belongsTo(Integration::class);
    }

    public function isSuccessful(): bool
    {
        return $this->status === 'success';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isPartial(): bool
    {
        return $this->status === 'partial';
    }

    public function start(): void
    {
        $this->update(['started_at' => now()]);
    }

    public function complete(): void
    {
        $completedAt = now();
        $duration = $this->started_at
            ? $completedAt->diffInMilliseconds($this->started_at)
            : 0;

        $this->update([
            'status' => $this->records_failed > 0 ? 'partial' : 'success',
            'completed_at' => $completedAt,
            'duration_ms' => $duration,
        ]);
    }

    public function fail(string $errorMessage): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
            'completed_at' => now(),
            'duration_ms' => $this->started_at
                ? now()->diffInMilliseconds($this->started_at)
                : 0,
        ]);
    }

    public function incrementProcessed(int $count = 1): void
    {
        $this->increment('records_processed', $count);
    }

    public function incrementCreated(int $count = 1): void
    {
        $this->increment('records_created', $count);
    }

    public function incrementUpdated(int $count = 1): void
    {
        $this->increment('records_updated', $count);
    }

    public function incrementFailed(int $count = 1): void
    {
        $this->increment('records_failed', $count);
    }

    public function getSummary(): array
    {
        return [
            'operation' => $this->operation,
            'status' => $this->status,
            'records_processed' => $this->records_processed,
            'records_created' => $this->records_created,
            'records_updated' => $this->records_updated,
            'records_failed' => $this->records_failed,
            'duration_ms' => $this->duration_ms,
            'started_at' => $this->started_at,
            'completed_at' => $this->completed_at,
        ];
    }

    public static function getRecentByIntegration(string $integrationId, int $limit = 10)
    {
        return self::where('integration_id', $integrationId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}

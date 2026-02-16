<?php

declare(strict_types=1);

namespace App\Models\Integration;

use App\Models\User;
use App\Traits\UsesUuid;
use Hypervel\Database\Eloquent\Factories\HasFactory;
use Hypervel\Database\Eloquent\Model;
use Hypervel\Database\Eloquent\SoftDeletes;

class Integration extends Model
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
        'provider',
        'type',
        'credentials',
        'settings',
        'sync_rules',
        'status',
        'error_message',
        'last_sync_at',
        'last_error_at',
        'sync_count',
        'error_count',
        'created_by',
    ];

    protected array $casts = [
        'credentials' => 'encrypted:array',
        'settings' => 'array',
        'sync_rules' => 'array',
        'last_sync_at' => 'datetime',
        'last_error_at' => 'datetime',
    ];

    protected array $hidden = [
        'credentials',
    ];

    public function syncLogs()
    {
        return $this->hasMany(IntegrationSyncLog::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function activate(): void
    {
        $this->update([
            'status' => 'active',
            'error_message' => null,
        ]);
    }

    public function deactivate(): void
    {
        $this->update(['status' => 'inactive']);
    }

    public function markError(string $message): void
    {
        $this->update([
            'status' => 'error',
            'error_message' => $message,
            'last_error_at' => now(),
        ]);
        $this->increment('error_count');
    }

    public function markSuccess(): void
    {
        $this->update([
            'last_sync_at' => now(),
            'status' => 'active',
            'error_message' => null,
        ]);
        $this->increment('sync_count');
    }

    public function getCredential(string $key): ?string
    {
        $credentials = $this->credentials ?? [];
        return $credentials[$key] ?? null;
    }

    public function setCredential(string $key, string $value): void
    {
        $credentials = $this->credentials ?? [];
        $credentials[$key] = $value;
        $this->update(['credentials' => $credentials]);
    }

    public static function getByProvider(string $provider): ?self
    {
        return self::where('provider', $provider)->first();
    }

    public static function getActiveByType(string $type)
    {
        return self::where('type', $type)->where('status', 'active')->get();
    }
}

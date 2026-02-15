<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\User;
use App\Traits\UsesUuid;
use Hypervel\Database\Model\Model;

class MfaVerificationAttempt extends Model
{
    use UsesUuid;

    protected ?string $table = 'mfa_verification_attempts';

    public bool $incrementing = false;

    protected string $keyType = 'string';

    public bool $timestamps = true;

    protected array $fillable = [
        'user_id',
        'ip_address',
        'user_agent',
        'success',
        'method',
        'attempted_at',
    ];

    protected array $casts = [
        'success' => 'boolean',
        'attempted_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function logAttempt(
        string $userId,
        bool $success,
        string $method,
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): self {
        return self::create([
            'user_id' => $userId,
            'success' => $success,
            'method' => $method,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'attempted_at' => now(),
        ]);
    }

    public static function getRecentFailedAttempts(string $userId, int $minutes = 30): int
    {
        return self::where('user_id', $userId)
            ->where('success', false)
            ->where('attempted_at', '>=', now()->subMinutes($minutes))
            ->count();
    }

    public static function getRecentFailedAttemptsFromIp(string $ipAddress, int $minutes = 30): int
    {
        return self::where('ip_address', $ipAddress)
            ->where('success', false)
            ->where('attempted_at', '>=', now()->subMinutes($minutes))
            ->count();
    }
}

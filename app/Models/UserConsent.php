<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Model;
use Hyperf\Database\Model\Relations\BelongsTo;

/**
 * UserConsent Model
 *
 * Tracks user consent for GDPR compliance including:
 * - Terms of Service acceptance
 * - Privacy Policy acknowledgment
 * - Marketing consent
 * - Data processing consent
 * - Cookie consent
 */
class UserConsent extends Model
{
    protected string $table = 'user_consents';

    protected array $fillable = [
        'user_id',
        'consent_type',
        'consent_given',
        'consent_version',
        'ip_address',
        'user_agent',
        'consent_metadata',
        'withdrawn_at',
        'withdrawn_reason',
    ];

    protected array $casts = [
        'consent_given' => 'boolean',
        'consent_metadata' => 'array',
        'withdrawn_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Consent type constants
     */
    public const CONSENT_TERMS_OF_SERVICE = 'terms_of_service';
    public const CONSENT_PRIVACY_POLICY = 'privacy_policy';
    public const CONSENT_MARKETING = 'marketing';
    public const CONSENT_DATA_PROCESSING = 'data_processing';
    public const CONSENT_COOKIES = 'cookies';
    public const CONSENT_ANALYTICS = 'analytics';
    public const CONSENT_THIRD_PARTY = 'third_party_sharing';

    /**
     * All available consent types
     */
    public const ALL_CONSENT_TYPES = [
        self::CONSENT_TERMS_OF_SERVICE,
        self::CONSENT_PRIVACY_POLICY,
        self::CONSENT_MARKETING,
        self::CONSENT_DATA_PROCESSING,
        self::CONSENT_COOKIES,
        self::CONSENT_ANALYTICS,
        self::CONSENT_THIRD_PARTY,
    ];

    /**
     * Relationship to user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if consent is currently active (given and not withdrawn)
     */
    public function isActive(): bool
    {
        return $this->consent_given && $this->withdrawn_at === null;
    }

    /**
     * Withdraw consent
     */
    public function withdraw(string $reason = ''): void
    {
        $this->withdrawn_at = now();
        $this->withdrawn_reason = $reason;
        $this->save();
    }

    /**
     * Scope to get active consents only
     */
    public function scopeActive($query)
    {
        return $query->where('consent_given', true)
            ->whereNull('withdrawn_at');
    }

    /**
     * Scope to get consents by type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('consent_type', $type);
    }

    /**
     * Scope to get latest consent for each type per user
     */
    public function scopeLatestPerType($query)
    {
        return $query->whereIn('id', function ($subquery) {
            $subquery->selectRaw('MAX(id)')
                ->from('user_consents')
                ->groupBy('user_id', 'consent_type');
        });
    }

    /**
     * Record new consent
     */
    public static function record(
        string $userId,
        string $consentType,
        bool $consentGiven,
        string $version,
        ?string $ipAddress = null,
        ?string $userAgent = null,
        array $metadata = []
    ): self {
        return self::create([
            'user_id' => $userId,
            'consent_type' => $consentType,
            'consent_given' => $consentGiven,
            'consent_version' => $version,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'consent_metadata' => $metadata,
        ]);
    }

    /**
     * Check if user has given active consent for a specific type
     */
    public static function hasActiveConsent(string $userId, string $consentType): bool
    {
        return self::where('user_id', $userId)
            ->where('consent_type', $consentType)
            ->active()
            ->exists();
    }

    /**
     * Get all active consents for a user
     */
    public static function getActiveConsentsForUser(string $userId): array
    {
        return self::where('user_id', $userId)
            ->active()
            ->get()
            ->pluck('consent_type')
            ->toArray();
    }

    /**
     * Withdraw all consents for a user
     */
    public static function withdrawAllForUser(string $userId, string $reason = 'User request'): int
    {
        return self::where('user_id', $userId)
            ->whereNull('withdrawn_at')
            ->update([
                'withdrawn_at' => now(),
                'withdrawn_reason' => $reason,
            ]);
    }
}

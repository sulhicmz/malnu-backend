<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\User;
use App\Traits\UsesUuid;
use Hyperf\Database\Model\Model;

class UserMfaSetting extends Model
{
    use UsesUuid;

    protected ?string $table = 'user_mfa_settings';

    public bool $incrementing = false;

    protected string $keyType = 'string';

    protected array $fillable = [
        'user_id',
        'mfa_enabled',
        'mfa_secret',
        'mfa_type',
        'mfa_enabled_at',
        'mfa_verified_at',
        'backup_codes_count',
    ];

    protected array $casts = [
        'mfa_enabled' => 'boolean',
        'mfa_enabled_at' => 'datetime',
        'mfa_verified_at' => 'datetime',
        'backup_codes_count' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isMfaEnabled(): bool
    {
        return $this->mfa_enabled && $this->mfa_secret !== null;
    }

    public function markAsEnabled(): void
    {
        $this->update([
            'mfa_enabled' => true,
            'mfa_enabled_at' => now(),
        ]);
    }

    public function markAsDisabled(): void
    {
        $this->update([
            'mfa_enabled' => false,
            'mfa_secret' => null,
            'mfa_enabled_at' => null,
            'mfa_verified_at' => null,
            'backup_codes_count' => 0,
        ]);
    }

    public function markAsVerified(): void
    {
        $this->update([
            'mfa_verified_at' => now(),
        ]);
    }
}

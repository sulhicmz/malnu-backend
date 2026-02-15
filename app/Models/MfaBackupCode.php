<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\User;
use App\Traits\UsesUuid;
use Hypervel\Database\Model\Model;

class MfaBackupCode extends Model
{
    use UsesUuid;

    protected ?string $table = 'mfa_backup_codes';

    public bool $incrementing = false;

    protected string $keyType = 'string';

    protected array $fillable = [
        'user_id',
        'code_hash',
        'used',
        'used_at',
    ];

    protected array $casts = [
        'used' => 'boolean',
        'used_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function markAsUsed(): void
    {
        $this->update([
            'used' => true,
            'used_at' => now(),
        ]);
    }

    public static function getUnusedCodesForUser(string $userId)
    {
        return self::where('user_id', $userId)
            ->where('used', false)
            ->get();
    }

    public static function countUnusedCodesForUser(string $userId): int
    {
        return self::where('user_id', $userId)
            ->where('used', false)
            ->count();
    }

    public static function deleteAllForUser(string $userId): void
    {
        self::where('user_id', $userId)->delete();
    }
}

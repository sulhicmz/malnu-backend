<?php

declare(strict_types=1);

namespace App\Models\Integration;

use App\Models\User;
use App\Traits\UsesUuid;
use Hypervel\Database\Eloquent\Factories\HasFactory;
use Hypervel\Database\Eloquent\Model;
use Hypervel\Database\Eloquent\SoftDeletes;

class ApiClient extends Model
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
        'client_id',
        'client_secret',
        'redirect_uri',
        'scopes',
        'allowed_ips',
        'last_used_at',
        'expires_at',
        'is_active',
        'is_revoked',
        'created_by',
    ];

    protected array $casts = [
        'scopes' => 'array',
        'allowed_ips' => 'array',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
        'is_revoked' => 'boolean',
    ];

    protected array $hidden = [
        'client_secret',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isValid(): bool
    {
        if (!$this->is_active || $this->is_revoked) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    public function markAsUsed(): void
    {
        $this->update(['last_used_at' => now()]);
    }

    public function revoke(): void
    {
        $this->update(['is_revoked' => true]);
    }

    public function hasScope(string $scope): bool
    {
        $scopes = $this->scopes ?? [];
        return in_array($scope, $scopes, true) || in_array('*', $scopes, true);
    }

    public function isIpAllowed(string $ip): bool
    {
        $allowedIps = $this->allowed_ips ?? [];
        if (empty($allowedIps)) {
            return true;
        }
        return in_array($ip, $allowedIps, true);
    }

    public static function findByClientId(string $clientId): ?self
    {
        return self::where('client_id', $clientId)->first();
    }

    public function verifySecret(string $secret): bool
    {
        return hash_equals($this->client_secret, $secret);
    }
}

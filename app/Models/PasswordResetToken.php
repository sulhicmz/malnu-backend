<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\UsesUuid;

class PasswordResetToken extends \Hyperf\Database\Model\Model
{
    use UsesUuid;

    public bool $incrementing = false;

    protected ?string $table = 'password_reset_tokens';

    protected string $primaryKey = 'id';

    protected string $keyType = 'string';

    protected array $fillable = [
        'user_id',
        'token',
        'expires_at',
    ];

    protected array $casts = [
        'expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}

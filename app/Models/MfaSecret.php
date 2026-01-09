<?php

declare (strict_types = 1);

namespace App\Models;

use Hyperf\Database\Model\Model;
use App\Traits\UsesUuid;

class MfaSecret extends Model
{
    use UsesUuid;

    protected string $primaryKey = 'id';
    protected string $keyType = 'string';
    public bool $incrementing = false;

    protected array $fillable = [
        'user_id',
        'secret',
        'is_enabled',
        'backup_codes',
        'backup_codes_count',
    ];

    protected array $casts = [
        'is_enabled' => 'boolean',
        'backup_codes' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

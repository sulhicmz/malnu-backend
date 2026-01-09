<?php

declare (strict_types = 1);

namespace App\Models;

use Hyperf\Database\Model\Model;
use App\Traits\UsesUuid;

class UserDevice extends Model
{
    use UsesUuid;

    protected string $primaryKey = 'id';
    protected string $keyType = 'string';
    public bool $incrementing = false;

    protected array $fillable = [
        'user_id',
        'device_name',
        'user_agent',
        'ip_address',
        'is_trusted',
        'last_used_at',
    ];

    protected array $casts = [
        'is_trusted' => 'boolean',
        'last_used_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

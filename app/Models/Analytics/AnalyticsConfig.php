<?php

declare(strict_types=1);

namespace App\Models\Analytics;

use App\Models\Model;
use App\Traits\UsesUuid;

class AnalyticsConfig extends Model
{
    use UsesUuid;

    protected string $primaryKey = 'id';
    protected string $keyType = 'string';
    public bool $incrementing = false;

    protected array $fillable = [
        'user_id',
        'dashboard_name',
        'config_data',
        'is_public',
        'is_default',
    ];

    protected array $casts = [
        'config_data' => 'array',
        'is_public' => 'boolean',
        'is_default' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }
}

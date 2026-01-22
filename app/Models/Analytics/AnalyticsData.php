<?php

declare(strict_types=1);

namespace App\Models\Analytics;

use App\Models\Model;
use App\Traits\UsesUuid;

class AnalyticsData extends Model
{
    use UsesUuid;

    protected string $primaryKey = 'id';
    protected string $keyType = 'string';
    public bool $incrementing = false;

    protected array $fillable = [
        'user_id',
        'data_type',
        'metric_name',
        'metric_value',
        'metadata',
        'recorded_at',
        'period',
    ];

    protected array $casts = [
        'metadata' => 'array',
        'metric_value' => 'float',
        'recorded_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }
}

<?php

declare(strict_types=1);

namespace App\Models\Behavior;

use App\Models\Model;
use App\Traits\UsesUuid;

class BehaviorCategory extends Model
{
    use UsesUuid;

    protected string $table = 'behavior_categories';

    protected array $fillable = [
        'name',
        'description',
        'type',
        'severity_level',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected array $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeBySeverity($query, int $minSeverity = 1, int $maxSeverity = 5)
    {
        return $query->whereBetween('severity_level', [$minSeverity, $maxSeverity]);
    }

    public function incidents()
    {
        return $this->hasMany(BehaviorIncident::class, 'behavior_category_id');
    }
}

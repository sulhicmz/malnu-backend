<?php

declare(strict_types=1);

namespace App\Models\Behavior;

use App\Models\Model;

class BehaviorCategory extends Model
{
    protected $table = 'behavior_categories';

    protected $fillable = [
        'name',
        'type',
        'description',
        'severity',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function incidents()
    {
        return $this->hasMany(Incident::class, 'category_id');
    }
}

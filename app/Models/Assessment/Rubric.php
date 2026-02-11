<?php

declare(strict_types=1);

namespace App\Models\Assessment;

use App\Models\Model;
use App\Models\User;

class Rubric extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'description',
        'max_score',
        'is_default',
        'created_by',
    ];

    protected $casts = [
        'max_score' => 'decimal:2',
        'is_default' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function criteria()
    {
        return $this->hasMany(RubricCriterion::class, 'rubric_id');
    }

    public function assessments()
    {
        return $this->hasMany(Assessment::class);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function calculateTotalScore(): float
    {
        return $this->criteria()->sum('max_score');
    }
}

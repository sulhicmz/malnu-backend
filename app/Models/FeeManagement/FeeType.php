<?php

declare(strict_types = 1);

namespace App\Models\FeeManagement;

use App\Models\Model;
use App\Models\SchoolManagement\Student;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeeType extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'code',
        'description',
        'category',
        'is_active',
        'is_mandatory',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_mandatory' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function feeStructures(): HasMany
    {
        return $this->hasMany(FeeStructure::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeMandatory($query)
    {
        return $query->where('is_mandatory', true);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }
}

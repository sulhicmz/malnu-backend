<?php

declare(strict_types=1);

namespace App\Models\Assessment;

use App\Models\Model;

class RubricCriterion extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'rubric_id',
        'name',
        'description',
        'max_score',
        'weight',
    ];

    protected $casts = [
        'max_score' => 'decimal:2',
        'weight' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function rubric()
    {
        return $this->belongsTo(Rubric::class);
    }
}

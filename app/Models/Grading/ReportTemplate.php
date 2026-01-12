<?php

declare (strict_types = 1);

namespace App\Models\Grading;

use App\Models\Model;
use App\Models\User;

class ReportTemplate extends Model
{
    protected $primaryKey = 'id';
    protected $keyType    = 'string';
    public $incrementing  = false;

    protected $fillable = [
        'name',
        'type',
        'grade_level',
        'content',
        'css_styles',
        'is_default',
        'is_active',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'is_default'  => 'boolean',
        'is_active'   => 'boolean',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

<?php

declare (strict_types = 1);

namespace App\Models\Grading;

use App\Models\Model;
use App\Models\User;

class ReportSignature extends Model
{
    protected $primaryKey = 'id';
    protected $keyType    = 'string';
    public $incrementing  = false;

    protected $fillable = [
        'name',
        'title',
        'signature_type',
        'signature_image',
        'signature_image_path',
        'is_default',
        'metadata',
        'created_by',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'metadata'   => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('signature_type', $type);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }
}

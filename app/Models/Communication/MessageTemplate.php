<?php

declare(strict_types=1);

namespace App\Models\Communication;

use App\Models\Model;
use App\Models\User;
use App\Traits\UsesUuid;

class MessageTemplate extends Model
{
    use UsesUuid;

    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'slug',
        'content',
        'subject',
        'type',
        'placeholders',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'placeholders' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
<?php

declare(strict_types=1);

namespace App\Models\ELearning;

use App\Models\Model;
use App\Models\User;
use App\Models\ELearning\VirtualClass;
use App\Models\ELearning\DiscussionReply;

class Discussion extends Model
{
    protected string $primaryKey = 'id';
    protected string $keyType = 'string';
    public bool $incrementing = false;

    protected $fillable = [
        'virtual_class_id',
        'title',
        'content',
        'is_pinned',
        'created_by',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function virtualClass()
    {
        return $this->belongsTo(VirtualClass::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function replies()
    {
        return $this->hasMany(DiscussionReply::class);
    }
}

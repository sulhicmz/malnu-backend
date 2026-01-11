<?php

declare(strict_types=1);

namespace App\Models\Communication;

use App\Models\Model;
use App\Models\User;

class Announcement extends Model
{
    protected $table = 'announcements';

    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'created_by',
        'title',
        'content',
        'type',
        'target_type',
        'target_roles',
        'target_classes',
        'target_users',
        'published_at',
        'expires_at',
        'is_active',
        'attachment_url',
    ];

    protected $casts = [
        'target_roles' => 'array',
        'target_classes' => 'array',
        'target_users' => 'array',
        'published_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function readStatuses()
    {
        return $this->hasMany(AnnouncementReadStatus::class, 'announcement_id');
    }

    public function isTargetedForUser(User $user): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->target_type === 'all') {
            return true;
        }

        if ($this->target_type === 'users' && in_array($user->id, $this->target_users ?? [])) {
            return true;
        }

        return false;
    }
}

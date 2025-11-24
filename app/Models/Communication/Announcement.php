<?php

declare(strict_types=1);

namespace App\Models\Communication;

use App\Models\Model;
use App\Models\User;
use App\Traits\UsesUuid;

class Announcement extends Model
{
    use UsesUuid;

    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'title',
        'content',
        'type',
        'created_by',
        'target_audience_id',
        'target_audience_type',
        'publish_date',
        'expiry_date',
        'is_published',
        'is_pinned',
        'attachments',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'is_pinned' => 'boolean',
        'publish_date' => 'datetime',
        'expiry_date' => 'datetime',
        'attachments' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function readStatus()
    {
        return $this->hasMany(AnnouncementReadStatus::class);
    }
}
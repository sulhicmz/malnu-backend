<?php

declare(strict_types=1);

namespace App\Models\Communication;

use App\Models\Model;
use App\Models\User;
use App\Traits\UsesUuid;

class Message extends Model
{
    use UsesUuid;

    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'thread_id',
        'sender_id',
        'recipient_id',
        'content',
        'message_type',
        'file_url',
        'metadata',
        'is_read',
        'is_delivered',
        'read_at',
        'reply_to_id',
        'message_category_id',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'is_delivered' => 'boolean',
        'read_at' => 'datetime',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function thread()
    {
        return $this->belongsTo(MessageThread::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    public function replyTo()
    {
        return $this->belongsTo(Message::class, 'reply_to_id');
    }

    public function replies()
    {
        return $this->hasMany(Message::class, 'reply_to_id');
    }

    public function category()
    {
        return $this->belongsTo(MessageCategory::class, 'message_category_id');
    }

    public function readStatus()
    {
        return $this->hasMany(MessageReadStatus::class);
    }
}
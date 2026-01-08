<?php

declare(strict_types=1);

namespace App\Models\ParentPortal;

use App\Models\Model;
use App\Models\User;

class ParentMessage extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'sender_id',
        'recipient_id',
        'subject',
        'message',
        'type',
        'thread_id',
        'is_read',
        'read_at',
        'attachment_url',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    public function thread()
    {
        return $this->hasMany(self::class, 'thread_id', 'thread_id');
    }
}

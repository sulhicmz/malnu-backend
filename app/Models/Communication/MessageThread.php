<?php

declare(strict_types=1);

namespace App\Models\Communication;

use App\Models\Model;
use App\Models\User;

class MessageThread extends Model
{
    protected $table = 'message_threads';

    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'subject',
        'type',
        'created_by',
        'is_archived',
        'is_pinned',
    ];

    protected $casts = [
        'is_archived' => 'boolean',
        'is_pinned' => 'boolean',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function messages()
    {
        return $this->hasMany(Message::class, 'thread_id');
    }

    public function participants()
    {
        return $this->hasMany(MessageParticipant::class, 'thread_id');
    }

    public function lastMessage()
    {
        return $this->hasOne(Message::class, 'thread_id')->latest();
    }
}

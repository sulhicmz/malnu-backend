<?php

declare(strict_types=1);

namespace App\Models\Communication;

use App\Models\Model;
use App\Models\User;

class MessageParticipant extends Model
{
    protected $table = 'message_participants';

    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'thread_id',
        'user_id',
        'is_admin',
        'has_left',
        'last_read_at',
    ];

    protected $casts = [
        'is_admin' => 'boolean',
        'has_left' => 'boolean',
        'last_read_at' => 'datetime',
    ];

    public function thread()
    {
        return $this->belongsTo(MessageThread::class, 'thread_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
